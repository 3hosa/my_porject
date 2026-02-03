<!-- 1 -->
<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

$host     = '127.0.0.1';
$port     = 3306;
$username = 'root';
$password = '';
$dbname   = 'my_store_db';

try {
    // اتصال بدون تحديد قاعدة بيانات
    $pdo = new PDO(
        "mysql:host=$host;port=$port;charset=utf8mb4",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // إنشاء قاعدة البيانات إن لم تكن موجودة
    $pdo->exec("
        CREATE DATABASE IF NOT EXISTS `$dbname`
        CHARACTER SET utf8mb4
        COLLATE utf8mb4_general_ci
    ");

    // استخدام قاعدة البيانات
    $pdo->exec("USE `$dbname`");

} catch (PDOException $e) {
    die("❌ فشل الاتصال: " . $e->getMessage());
}
