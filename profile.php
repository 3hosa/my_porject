<?php
session_start();
require 'includes/db.php';
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// جلب بيانات المستخدم
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// جلب الطلبات الخاصة بالمستخدم
$orders = $pdo->prepare("SELECT * FROM orders WHERE customer_name = ? ORDER BY created_at DESC");
$orders->execute([$user['username']]);
$user_orders = $orders->fetchAll(PDO::FETCH_ASSOC);
?>

<h2 class="section-title">الملف الشخصي</h2>

<div class="profile-info">
    <h3>معلومات الحساب</h3>
    <p><strong>اسم المستخدم:</strong> <?= htmlspecialchars($user['username']) ?></p>
    <p><strong>البريد الإلكتروني:</strong> <?= htmlspecialchars($user['email']) ?></p>
    <p><strong>تاريخ التسجيل:</strong> <?= $user['created_at'] ?></p>
</div>

<h3>طلباتي</h3>
<?php if (empty($user_orders)): ?>
    <p>لا توجد طلبات سابقة.</p>
<?php else: ?>
    <table class="profile-table">
        <thead>
            <tr>
                <th>رقم الطلب</th>
                <th>المبلغ الإجمالي</th>
                <th>طريقة الدفع</th>
                <th>تاريخ الطلب</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($user_orders as $order): ?>
            <tr>
                <td><?= $order['id'] ?></td>
                <td>$<?= $order['total'] ?></td>
                <td><?= htmlspecialchars($order['payment_method']) ?></td>
                <td><?= $order['created_at'] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
