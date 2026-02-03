<?php
require 'includes/db.php';

// بدء الجلسة إذا لم تكن مفعلة
if(session_status() == PHP_SESSION_NONE) session_start();

// جلب معرف المنتج من الرابط
$id = $_GET['id'] ?? null;

if($id && isset($_SESSION['cart'][$id])){
    // إزالة المنتج من السلة
    unset($_SESSION['cart'][$id]);
}

// إعادة التوجيه لصفحة السلة
header('Location: cart.php');
exit;

