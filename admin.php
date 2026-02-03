<?php
session_start();
require 'includes/db.php';
include 'includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// معالجة تحديث حالة الطلب
if (isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $status = $_POST['status'];
    $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?")->execute([$status, $order_id]);
    header('Location: admin.php');
    exit;
}

// جلب الطلبات مع تفاصيل المستخدمين والمنتجات
$orders = $pdo->query("
    SELECT o.*, u.username, u.email 
    FROM orders o 
    LEFT JOIN users u ON o.customer_name = u.username 
    ORDER BY o.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

// جلب تفاصيل كل طلب (المنتجات)
foreach ($orders as &$order) {
    $stmt = $pdo->prepare("
        SELECT oi.*, p.name as product_name, p.img as product_image 
        FROM order_items oi 
        JOIN products p ON oi.product_id = p.id 
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$order['id']]);
    $order['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// إحصائيات
$total_orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$total_products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$total_users = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();
$total_revenue = $pdo->query("SELECT SUM(total) FROM orders")->fetchColumn() ?? 0;
?>

<h2 class="section-title">لوحة الإدارة</h2>

<!-- الإحصائيات -->
<div class="admin-stats">
    <div class="stat-card">
        <i class="stat-icon">📦</i>
        <h3>إجمالي الطلبات</h3>
        <p><?= $total_orders; ?></p>
    </div>
    <div class="stat-card">
        <i class="stat-icon">🛍️</i>
        <h3>إجمالي المنتجات</h3>
        <p><?= $total_products; ?></p>
    </div>
    <div class="stat-card">
        <i class="stat-icon">👥</i>
        <h3>إجمالي المستخدمين</h3>
        <p><?= $total_users; ?></p>
    </div>
    <div class="stat-card revenue">
        <i class="stat-icon">💰</i>
        <h3>إجمالي المبيعات</h3>
        <p>$<?= number_format($total_revenue, 2); ?></p>
    </div>
</div>

<!-- جدول الطلبات المُحسّن -->
<div class="admin-container">
    <h3>إدارة الطلبات</h3>
    
    <div class="table-responsive">
        <table class="admin-table modern-table">
            <thead>
                <tr>
                    <th>رقم الطلب</th>
                    <th>العميل</th>
                    <th>المنتجات</th>
                    <th>المبلغ</th>
                    <th>تاريخ الطلب</th>
                    <th>الحالة</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                <tr class="order-row" data-order-id="<?= $order['id']; ?>">
                    <td class="order-id">#<?= str_pad($order['id'], 5, '0', STR_PAD_LEFT); ?></td>
                    <td class="customer-info">
                        <strong><?= htmlspecialchars($order['customer_name']); ?></strong>
                        <?php if ($order['email']): ?>
                            <br><small><?= htmlspecialchars($order['email']); ?></small>
                        <?php endif; ?>
                        <br><small class="phone"><?= htmlspecialchars($order['customer_phone']); ?></small>
                    </td>
                    <td class="order-products">
                        <button class="btn btn-sm btn-outline toggle-details" onclick="toggleOrderDetails(<?= $order['id']; ?>)">
                            عرض <?= count($order['items']); ?> منتجات
                        </button>
                        <div class="order-details" id="details-<?= $order['id']; ?>" style="display: none;">
                            <ul class="products-list">
                                <?php foreach ($order['items'] as $item): ?>
                                <li>
                                    <img src="../<?= htmlspecialchars($item['product_image']) ?>" width="40" alt="">
                                    <span><?= htmlspecialchars($item['product_name']); ?></span>
                                    <span class="qty">×<?= $item['quantity']; ?></span>
                                    <span class="price">$<?= $item['price']; ?></span>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </td>
                    <td class="order-total">$<?= number_format($order['total'], 2); ?></td>
                    <td class="order-date"><?= date('Y-m-d H:i', strtotime($order['created_at'])); ?></td>
                    <td class="order-status">
                        <span class="status-badge status-<?= $order['status'] ?? 'pending'; ?>">
                            <?= getStatusText($order['status'] ?? 'pending'); ?>
                        </span>
                    </td>
                    <td class="order-actions">
                        <form method="post" class="status-form">
                            <input type="hidden" name="order_id" value="<?= $order['id']; ?>">
                            <select name="status" onchange="this.form.submit()">
                                <option value="pending" <?= ($order['status'] ?? '') == 'pending' ? 'selected' : ''; ?>>قيد الانتظار</option>
                                <option value="processing" <?= ($order['status'] ?? '') == 'processing' ? 'selected' : ''; ?>>قيد المعالجة</option>
                                <option value="shipped" <?= ($order['status'] ?? '') == 'shipped' ? 'selected' : ''; ?>>تم الشحن</option>
                                <option value="delivered" <?= ($order['status'] ?? '') == 'delivered' ? 'selected' : ''; ?>>تم التوصيل</option>
                                <option value="cancelled" <?= ($order['status'] ?? '') == 'cancelled' ? 'selected' : ''; ?>>ملغي</option>
                            </select>
                            <input type="hidden" name="update_status" value="1">
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
function getStatusText($status) {
    $statuses = [
        'pending' => '⏳ قيد الانتظار',
        'processing' => '🔄 قيد المعالجة',
        'shipped' => '🚚 تم الشحن',
        'delivered' => '✅ تم التوصيل',
        'cancelled' => '❌ ملغي'
    ];
    return $statuses[$status] ?? $status;
}
?>

<script>
function toggleOrderDetails(orderId) {
    const details = document.getElementById('details-' + orderId);
    const btn = document.querySelector(`tr[data-order-id="${orderId}"] .toggle-details`);
    
    if (details.style.display === 'none') {
        details.style.display = 'block';
        btn.textContent = 'إخفاء المنتجات';
    } else {
        details.style.display = 'none';
        btn.textContent = 'عرض المنتجات';
    }
}
</script>

<style>
/* تنسيقات لوحة الإدارة الاحترافية */
.admin-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

.stat-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 25px;
    border-radius: 15px;
    text-align: center;
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    transition: transform 0.3s;
}

.stat-card:hover {
    transform: translateY(-5px);
}

.stat-card.revenue {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
}

.stat-icon {
    font-size: 2em;
    display: block;
    margin-bottom: 10px;
}

.stat-card h3 {
    font-size: 0.9em;
    margin-bottom: 10px;
    opacity: 0.9;
}

.stat-card p {
    font-size: 1.8em;
    font-weight: bold;
    margin: 0;
}

.admin-container {
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    margin-bottom: 30px;
}

.modern-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    margin-top: 20px;
}

.modern-table th {
    background: #1e293b;
    color: white;
    padding: 15px;
    text-align: right;
    font-weight: 600;
}

.modern-table td {
    padding: 15px;
    border-bottom: 1px solid #e2e8f0;
}

.order-row:hover {
    background: #f8fafc;
}

.order-id {
    font-weight: bold;
    color: #3b82f6;
    font-family: monospace;
}

.customer-info small {
    color: #64748b;
    display: block;
    margin-top: 5px;
}

.customer-info .phone {
    color: #059669;
    font-weight: 600;
}

.products-list {
    list-style: none;
    padding: 10px;
    background: #f1f5f9;
    border-radius: 8px;
    margin-top: 10px;
}

.products-list li {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 0;
    border-bottom: 1px solid #e2e8f0;
}

.products-list li:last-child {
    border-bottom: none;
}

.products-list img {
    border-radius: 4px;
    object-fit: cover;
}

.products-list .qty {
    background: #3b82f6;
    color: white;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 0.85em;
}

.products-list .price {
    margin-right: auto;
    font-weight: bold;
    color: #059669;
}

.status-badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.85em;
    font-weight: 600;
}

.status-pending { background: #fef3c7; color: #92400e; }
.status-processing { background: #dbeafe; color: #1e40af; }
.status-shipped { background: #c7d2fe; color: #3730a3; }
.status-delivered { background: #d1fae5; color: #065f46; }
.status-cancelled { background: #fee2e2; color: #991b1b; }

.status-form select {
    padding: 8px;
    border-radius: 6px;
    border: 1px solid #d1d5db;
    background: white;
    cursor: pointer;
    font-size: 0.9em;
}

.btn-sm {
    padding: 6px 12px;
    font-size: 0.85em;
}

.toggle-details {
    background: #f1f5f9;
    border: 1px solid #cbd5e1;
    color: #475569;
}

.toggle-details:hover {
    background: #e2e8f0;
}

@media (max-width: 768px) {
    .admin-stats {
        grid-template-columns: 1fr;
    }
    
    .modern-table {
        font-size: 0.9em;
    }
    
    .modern-table th,
    .modern-table td {
        padding: 10px 5px;
    }
}
</style>


<?php include '../includes/footer.php'; ?>
