<?php
session_start();
require 'includes/db.php';
include 'includes/header.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_role = $_SESSION['role'] ?? 'user';
$user_name = $_SESSION['username'] ?? '';

// دالة مساعدة لحالات الطلب
function getStatusLabel($status) {
    $labels = [
        'pending' => '⏳ قيد الانتظار',
        'processing' => '🔄 قيد المعالجة',
        'shipped' => '🚚 تم الشحن',
        'delivered' => '✅ تم التوصيل',
        'cancelled' => '❌ ملغي'
    ];
    return $labels[$status] ?? $status;
}

// إذا كان أدمن: عرض جميع الطلبات
if ($user_role === 'admin') {
    // جلب جميع الطلبات مع تفاصيل المستخدمين
    $stmt = $pdo->query("
        SELECT o.*, u.email, u.username 
        FROM orders o 
        LEFT JOIN users u ON o.customer_name = u.username 
        ORDER BY o.created_at DESC
    ");
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // جلب تفاصيل المنتجات لكل طلب
    foreach ($orders as &$order) {
        $items_stmt = $pdo->prepare("
            SELECT oi.*, p.name as product_name, p.img as product_image 
            FROM order_items oi 
            JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id = ?
        ");
        $items_stmt->execute([$order['id']]);
        $order['items'] = $items_stmt->fetchAll(PDO::FETCH_ASSOC);
        $order['total_items'] = array_sum(array_column($order['items'], 'quantity'));
    }
    ?>

    <!-- عرض الأدمن: جميع الطلبات -->
    <div class="orders-container">
        <h2 class="section-title">
            <span class="title-icon">📋</span>
            جميع الطلبات
        </h2>

        <?php if (empty($orders)): ?>
        <div class="empty-orders">
            <div class="empty-icon">📭</div>
            <h3>لا توجد طلبات حالياً</h3>
        </div>
        <?php else: ?>
        
        <div class="orders-list">
            <?php foreach ($orders as $order): ?>
            <div class="order-card">
                <div class="order-header">
                    <div class="order-info">
                        <div class="order-number">
                            <span class="label">رقم الطلب:</span>
                            <span class="value">#<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></span>
                        </div>
                        <div class="order-date">
                            <i class="icon">📅</i>
                            <?= date('d/m/Y', strtotime($order['created_at'])); ?>
                            <span class="time"><?= date('H:i', strtotime($order['created_at'])); ?></span>
                        </div>
                    </div>
                    <div class="order-status-container">
                        <span class="status-badge status-<?= $order['status'] ?? 'pending'; ?>">
                            <?= getStatusLabel($order['status'] ?? 'pending'); ?>
                        </span>
                    </div>
                </div>

                <div class="customer-info-bar">
                    <div class="customer-detail">
                        <i>👤</i>
                        <span><?= htmlspecialchars($order['customer_name']); ?></span>
                    </div>
                    <?php if ($order['email']): ?>
                    <div class="customer-detail">
                        <i>📧</i>
                        <span><?= htmlspecialchars($order['email']); ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="customer-detail">
                        <i>📱</i>
                        <span><?= htmlspecialchars($order['customer_phone']); ?></span>
                    </div>
                </div>

                <div class="order-products">
                    <div class="products-summary" onclick="toggleOrderDetails(<?= $order['id']; ?>)">
                        <span class="toggle-btn">
                            <i class="arrow" id="arrow-<?= $order['id']; ?>">▼</i>
                            عرض <?= $order['total_items']; ?> منتجات
                        </span>
                        <span class="items-count"><?= count($order['items']); ?> أصناف</span>
                    </div>
                    
                    <div class="products-details" id="details-<?= $order['id']; ?>" style="display: none;">
                        <div class="products-grid-list">
                            <?php foreach ($order['items'] as $item): ?>
                            <div class="product-item">
                                <img src="<?= htmlspecialchars($item['product_image']); ?>" alt="<?= htmlspecialchars($item['product_name']); ?>">
                                <div class="product-details">
                                    <h4><?= htmlspecialchars($item['product_name']); ?></h4>
                                    <div class="product-meta">
                                        <span class="qty">الكمية: <?= $item['quantity']; ?></span>
                                        <span class="unit-price">$<?= number_format($item['price'], 2); ?> للوحدة</span>
                                    </div>
                                </div>
                                <div class="product-total">
                                    $<?= number_format($item['price'] * $item['quantity'], 2); ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="order-footer">
                    <div class="payment-method">
                        <i>💳</i>
                        <span><?= htmlspecialchars($order['payment_method']); ?></span>
                    </div>
                    <div class="order-total-section">
                        <span class="total-label">المجموع الكلي:</span>
                        <span class="total-amount">$<?= number_format($order['total'], 2); ?></span>
                    </div>
                </div>

                <div class="shipping-address">
                    <i>📍</i>
                    <span><?= htmlspecialchars($order['customer_address']); ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <?php
// إذا كان مستخدم عادي: عرض السلة الخاصة به
} else {
    $cart = $_SESSION['cart'] ?? [];
    $total = 0;
    $products = [];
    
    if (!empty($cart)) {
        $product_ids = array_keys($cart);
        $placeholders = str_repeat('?,', count($product_ids) - 1) . '?';
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
        $stmt->execute($product_ids);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($rows as $row) {
            $products[$row['id']] = $row;
        }
    }
    ?>

    <!-- عرض المستخدم: سلة المشتريات -->
    <div class="cart-container">
        <h2 class="section-title">
            <span class="title-icon">🛒</span>
            سلة المشتريات
        </h2>

        <?php if (empty($cart)): ?>
        <div class="empty-cart">
            <div class="empty-icon">🛒</div>
            <h3>سلة المشتريات فارغة</h3>
            <p>لم تقم بإضافة أي منتجات بعد</p>
            <a href="products.php" class="btn btn-primary">تصفح المنتجات</a>
        </div>
        <?php else: ?>
        
        <div class="cart-content">
            <div class="cart-items">
                <?php foreach ($cart as $id => $item): 
                    if (!isset($products[$id])) continue;
                    $p = $products[$id];
                    $subtotal = $p['price'] * $item['qty'];
                    $total += $subtotal;
                ?>
                <div class="cart-item">
                    <img src="<?= htmlspecialchars($p['img'] ?? ''); ?>" alt="<?= htmlspecialchars($p['name'] ?? ''); ?>">
                    <div class="item-details">
                        <h3><?= htmlspecialchars($p['name'] ?? ''); ?></h3>
                        <p class="item-price">$<?= number_format($p['price'], 2); ?></p>
                    </div>
                    <div class="item-quantity">
                        <span class="qty-badge"><?= $item['qty']; ?></span>
                    </div>
                    <div class="item-total">
                        $<?= number_format($subtotal, 2); ?>
                    </div>
                    <a href="remove_from_cart.php?id=<?= $id; ?>" class="btn-remove" onclick="return confirm('هل أنت متأكد من حذف هذا المنتج؟')">
                        🗑️
                    </a>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="cart-summary">
                <h3>ملخص الطلبية</h3>
                <div class="summary-row">
                    <span>عدد المنتجات:</span>
                    <span><?= array_sum(array_column($cart, 'qty')); ?></span>
                </div>
                <div class="summary-row total">
                    <span>المجموع الكلي:</span>
                    <span>$<?= number_format($total, 2); ?></span>
                </div>
                <a href="checkout.php" class="btn btn-checkout">إتمام الشراء</a>
                <a href="products.php" class="btn btn-continue">مواصلة التسوق</a>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <?php
}
?>

<script>
function toggleOrderDetails(orderId) {
    const details = document.getElementById('details-' + orderId);
    const arrow = document.getElementById('arrow-' + orderId);
    
    if (details.style.display === 'none') {
        details.style.display = 'block';
        details.style.animation = 'slideDown 0.3s ease-out';
        arrow.style.transform = 'rotate(180deg)';
    } else {
        details.style.display = 'none';
        arrow.style.transform = 'rotate(0deg)';
    }
}
</script>

<style>
/* تنسيقات مشتركة */
.section-title {
    text-align: center;
    font-size: 2em;
    margin-bottom: 30px;
    color: #1e293b;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.title-icon {
    font-size: 1.2em;
}

/* تنسيقات صفحة الطلبات (للأدمن) */
.orders-container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 20px;
}

.empty-orders {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 20px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.05);
}

.empty-icon {
    font-size: 4em;
    margin-bottom: 20px;
    opacity: 0.5;
}

.orders-list {
    display: flex;
    flex-direction: column;
    gap: 25px;
}

.order-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    overflow: hidden;
    border: 1px solid #e2e8f0;
}

.order-header {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    padding: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #e2e8f0;
}

.order-number .value {
    font-weight: bold;
    font-family: monospace;
    font-size: 1.1em;
}

.status-badge {
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 0.85em;
    font-weight: 600;
}

.status-pending { background: #fef3c7; color: #92400e; }
.status-processing { background: #dbeafe; color: #1e40af; }
.status-shipped { background: #c7d2fe; color: #3730a3; }
.status-delivered { background: #d1fae5; color: #065f46; }
.status-cancelled { background: #fee2e2; color: #991b1b; }

.customer-info-bar {
    background: #f8fafc;
    padding: 12px 20px;
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
    border-bottom: 1px solid #e2e8f0;
    font-size: 0.9em;
}

.order-products {
    padding: 20px;
}

.products-summary {
    display: flex;
    justify-content: space-between;
    align-items: center;
    cursor: pointer;
    padding: 10px;
    background: #f8fafc;
    border-radius: 8px;
    transition: background 0.2s;
}

.products-summary:hover {
    background: #f1f5f9;
}

.toggle-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #3b82f6;
    font-weight: 600;
    user-select: none;
}

.arrow {
    transition: transform 0.3s;
    display: inline-block;
}

.items-count {
    color: #64748b;
    font-size: 0.9em;
}

.products-details {
    margin-top: 15px;
    animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.products-grid-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.product-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 12px;
    background: #f8fafc;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
}

.product-item img {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 8px;
}

.product-details {
    flex: 1;
}

.product-details h4 {
    margin: 0 0 5px 0;
    color: #1e293b;
    font-size: 0.95em;
}

.product-meta {
    display: flex;
    gap: 15px;
    font-size: 0.85em;
    color: #64748b;
}

.qty {
    background: #e0e7ff;
    color: #3730a3;
    padding: 2px 8px;
    border-radius: 12px;
}

.product-total {
    font-weight: bold;
    color: #059669;
    font-size: 1.1em;
}

.order-footer {
    background: #f8fafc;
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-top: 1px solid #e2e8f0;
}

.payment-method {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #64748b;
    font-size: 0.9em;
}

.order-total-section {
    display: flex;
    align-items: center;
    gap: 10px;
}

.total-label {
    color: #64748b;
    font-size: 0.9em;
}

.total-amount {
    font-size: 1.3em;
    font-weight: bold;
    color: #059669;
}

.shipping-address {
    padding: 12px 20px;
    background: #fff7ed;
    border-top: 1px dashed #fed7aa;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.9em;
    color: #9a3412;
}

/* تنسيقات سلة المستخدم */
.cart-container {
    max-width: 900px;
    margin: 0 auto;
    padding: 20px;
}

.empty-cart {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 20px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.05);
}

.cart-content {
    display: grid;
    grid-template-columns: 1fr 350px;
    gap: 30px;
}

.cart-items {
    background: white;
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.05);
}

.cart-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    border-bottom: 1px solid #e2e8f0;
}

.cart-item img {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 10px;
}

.item-details {
    flex: 1;
}

.item-details h3 {
    margin: 0 0 5px 0;
    color: #1e293b;
}

.item-price {
    color: #059669;
    font-weight: bold;
}

.qty-badge {
    background: #e0e7ff;
    color: #3730a3;
    padding: 5px 15px;
    border-radius: 20px;
    font-weight: bold;
}

.item-total {
    font-weight: bold;
    font-size: 1.1em;
    color: #1e293b;
    min-width: 80px;
    text-align: left;
}

.btn-remove {
    color: #dc2626;
    text-decoration: none;
    font-size: 1.2em;
    padding: 5px;
}

.cart-summary {
    background: white;
    border-radius: 16px;
    padding: 25px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    height: fit-content;
    position: sticky;
    top: 20px;
}

.cart-summary h3 {
    margin-bottom: 20px;
    color: #1e293b;
    border-bottom: 2px solid #e2e8f0;
    padding-bottom: 10px;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 15px;
    color: #64748b;
}

.summary-row.total {
    font-size: 1.2em;
    font-weight: bold;
    color: #1e293b;
    border-top: 2px solid #e2e8f0;
    padding-top: 15px;
    margin-top: 15px;
}

.btn-checkout {
    display: block;
    width: 100%;
    padding: 15px;
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
    color: white;
    text-align: center;
    border-radius: 10px;
    text-decoration: none;
    font-weight: bold;
    margin-bottom: 10px;
    transition: transform 0.2s;
}

.btn-checkout:hover {
    transform: translateY(-2px);
}

.btn-continue {
    display: block;
    width: 100%;
    padding: 12px;
    background: #f1f5f9;
    color: #475569;
    text-align: center;
    border-radius: 10px;
    text-decoration: none;
    transition: background 0.2s;
}

.btn-continue:hover {
    background: #e2e8f0;
}

.btn-primary {
    display: inline-block;
    padding: 12px 30px;
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: white;
    text-decoration: none;
    border-radius: 25px;
    font-weight: 600;
    margin-top: 15px;
}

/* تجاوبية */
@media (max-width: 768px) {
    .cart-content {
        grid-template-columns: 1fr;
    }
    
    .cart-item {
        flex-wrap: wrap;
    }
    
    .order-header {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }
    
    .customer-info-bar {
        flex-direction: column;
        gap: 8px;
    }
}
</style>

<?php include 'includes/footer.php'; ?>