<?php
session_start();
require '../includes/db.php';

// التحقق الصارم من صلاحية الأدمن
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php?error=unauthorized');
    exit;
}

include '../includes/header.php';

// معالجة الحذف مع تأكيد إضافي
if (isset($_GET['delete_product']) && is_numeric($_GET['delete_product'])) {
    $id = intval($_GET['delete_product']);
    
    // التحقق من أن المنتج موجود قبل الحذف
    $check = $pdo->prepare("SELECT id FROM products WHERE id = ?");
    $check->execute([$id]);
    
    if ($check->fetch()) {
        try {
            $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
            $delete_message = "<div class='success-message'>✅ تم حذف المنتج بنجاح</div>";
        } catch(PDOException $e) {
            $delete_message = "<div class='error-message'>❌ خطأ في الحذف: " . $e->getMessage() . "</div>";
        }
    } else {
        $delete_message = "<div class='error-message'>❌ المنتج غير موجود</div>";
    }
}

// جلب الطلبات الأخيرة
$orders = $pdo->query("
    SELECT o.*, u.username 
    FROM orders o 
    LEFT JOIN users u ON o.customer_name = u.username 
    ORDER BY o.created_at DESC 
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);

$products = $pdo->query("
    SELECT p.*, c.name AS category_name 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    ORDER BY p.id DESC 
    LIMIT 20
")->fetchAll(PDO::FETCH_ASSOC);

$total_orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$total_products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$total_users = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();
?>

<h2 class="section-title">لوحة الإدارة</h2>

<?= isset($delete_message) ? $delete_message : '' ?>

<!-- الإحصائيات -->
<div class="admin-stats">
    <div class="stat-card">
        <h3>إجمالي الطلبات</h3>
        <p><?= $total_orders; ?></p>
    </div>
    <div class="stat-card">
        <h3>إجمالي المنتجات</h3>
        <p><?= $total_products; ?></p>
    </div>
    <div class="stat-card">
        <h3>إجمالي المستخدمين</h3>
        <p><?= $total_users; ?></p>
    </div>
</div>

<!-- إدارة المنتجات -->
<h3>إدارة المنتجات</h3>
<a href="addpro.php" class="btn btn-success" style="margin-bottom:20px; display:inline-block;">+ إضافة منتج جديد</a>

<table class="admin-table">
    <thead>
        <tr>
            <th>الصورة</th>
            <th>الاسم</th>
            <th>الفئة</th>
            <th>السعر</th>
            <th>الإجراءات</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($products as $product): ?>
        <tr>
            <td>
                <img src="../<?= htmlspecialchars($product['img']) ?>" width="50" alt="صورة المنتج" style="border-radius:5px;">
            </td>
            <td><?= htmlspecialchars($product['name']); ?></td>
            <td><?= htmlspecialchars($product['category_name']); ?></td>
            <td>$<?= number_format($product['price'], 2); ?></td>
            <td>
                <a href="edit_product.php?id=<?= $product['id']; ?>" class="btn btn-outline" style="margin-left:5px;">✏️ تعديل</a>
                <a href="?delete_product=<?= $product['id']; ?>" class="btn btn-danger" onclick="return confirm('⚠️ هل أنت متأكد من حذف هذا المنتج؟ لا يمكن التراجع عن هذا الإجراء.')">🗑️ حذف</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- الطلبات الأخيرة -->
<h3 style="margin-top:40px;">الطلبات الأخيرة</h3>
<table class="admin-table">
    <thead>
        <tr>
            <th>رقم الطلب</th>
            <th>العميل</th>
            <th>المبلغ الإجمالي</th>
            <th>تاريخ الطلب</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($orders as $order): ?>
        <tr>
            <td>#<?= $order['id']; ?></td>
            <td><?= htmlspecialchars($order['customer_name']); ?></td>
            <td>$<?= number_format($order['total'], 2); ?></td>
            <td><?= $order['created_at']; ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<style>
/* تنسيقات خاصة بالأدمن */
.admin-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}
.stat-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    border-radius: 12px;
    text-align: center;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}
.stat-card h3 {
    margin-bottom: 10px;
    font-size: 0.9em;
    opacity: 0.9;
}
.stat-card p {
    font-size: 1.8em;
    font-weight: bold;
    margin: 0;
}

.admin-table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.admin-table th {
    background: #1e293b;
    color: white;
    padding: 12px;
    text-align: right;
}

.admin-table td {
    padding: 12px;
    border-bottom: 1px solid #e2e8f0;
}

.admin-table tr:hover {
    background: #f8fafc;
}

.btn-success {
    background: #059669;
    color: white;
    padding: 10px 20px;
    border-radius: 6px;
    text-decoration: none;
    display: inline-block;
}

.btn-danger {
    background: #dc2626;
    color: white;
    padding: 6px 12px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 0.9em;
    display: inline-block;
}

.btn-outline {
    border: 1px solid #2563eb;
    color: #2563eb;
    padding: 6px 12px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 0.9em;
    display: inline-block;
}

.success-message {
    background: #d1fae5;
    color: #065f46;
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 20px;
    text-align: center;
}

.error-message {
    background: #fee2e2;
    color: #991b1b;
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 20px;
    text-align: center;
}
</style>

<?php include '../includes/footer.php'; ?>