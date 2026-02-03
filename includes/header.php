<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// حساب المسار الأساسي للتطبيق ديناميكياً حتى تعمل الروابط سواءً كان المشروع في مجلد فرعي أو بالجذر
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
if ($base === '' || $base === '.') { $base = ''; }
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>متجر الإلكترونيات</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<!-- Google Font (Arabic friendly) -->
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">
<!-- Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-jQG6k6Vh9+K0e2uZqGZ8Y3Yj6Jq2Q1q9YV1z8Zl+QY5G9YdK1c9t9E2ZKJ1z9K1Q6e5a2Y3Z4a5b6c7d8e9f0g==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link rel="stylesheet" href="<?= $base ?>/assets/css/style.css">
</head>
<body>

<header class="main-header">
    <div class="header-container">
        <div class="logo">🛒 متجر الإلكترونيات</div>
        <button class="nav-toggle" aria-label="قائمة">☰</button>
        <nav class="main-nav">
            <a href="<?= $base ?>/index.php">الرئيسية</a>
                <a href="<?= $base ?>/products.php">المنتجات</a>
            <form action="/php1/products.php" method="get" style="display:inline-block; margin-left:10px;">
                    <form action="<?= $base ?>/products.php" method="get" style="display:inline-block; margin-left:10px;">
                <input type="search" name="q" placeholder="ابحث عن منتج..." aria-label="ابحث" style="padding:8px 10px; border-radius:8px; border:1px solid rgba(255,255,255,0.12); background:rgba(255,255,255,0.03); color:inherit;">
            </form>
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="<?= $base ?>/cart.php">جميع الطلبات</a>
                    <a href="<?= $base ?>/admin/addpro.php">إضافة منتج</a>
                    <a href="<?= $base ?>/admin/admin.php">لوحة الإدارة</a>
                    <?php else: ?>
                    <a href="<?= $base ?>/cart.php">سلتي (<span class="cart-count"><?= isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'qty')) : 0; ?></span>)</a>
                <?php endif; ?>
                <a href="<?= $base ?>/profile.php">الملف الشخصي</a>
                <a href="<?= $base ?>/logout.php" data-no-pjax>تسجيل الخروج (<?= htmlspecialchars($_SESSION['username']); ?>)</a>
            <?php else: ?>
                <a href="<?= $base ?>/login.php" data-no-pjax>تسجيل الدخول</a>
                <a href="<?= $base ?>/register.php" data-no-pjax>التسجيل</a>
            <?php endif; ?>

        </nav>
    </div>
</header>
<div class="site-container">
