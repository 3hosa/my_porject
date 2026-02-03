<?php
require 'includes/db.php';
if (session_status() == PHP_SESSION_NONE) session_start();

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo 'unauthorized';
    exit;
}

// دعم كل من POST (المفترض) و fallback لـ GET
$id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
$qty = (int)($_POST['qty'] ?? $_GET['qty'] ?? 1);
if ($id <= 0 || $qty <= 0) exit;

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (isset($_SESSION['cart'][$id])) {
    $_SESSION['cart'][$id]['qty'] += $qty;
} else {
    $_SESSION['cart'][$id] = ['qty' => $qty];
}

/* حساب عدد العناصر */
$count = 0;
foreach ($_SESSION['cart'] as $item) {
    $count += $item['qty'];
}

echo $count;
?>