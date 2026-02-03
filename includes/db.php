<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

// قراءة إعدادات قاعدة البيانات من متغيرات البيئة (Railway أو أي مزود آخر)
$host     = getenv('DB_HOST') ?: getenv('MYSQL_HOST') ?: '127.0.0.1';
$port     = getenv('DB_PORT') ?: getenv('MYSQL_PORT') ?: 3306;
$username = getenv('DB_USER') ?: getenv('DB_USERNAME') ?: getenv('MYSQL_USER') ?: 'root';
$password = getenv('DB_PASS') ?: getenv('DB_PASSWORD') ?: getenv('MYSQL_PASSWORD') ?: '';
$dbname   = getenv('DB_NAME') ?: getenv('MYSQL_DATABASE') ?: getenv('DB_DATABASE') ?: 'my_store_db';

// دعم DATABASE_URL (مثلاً: mysql://user:pass@host:port/dbname)
if (getenv('DATABASE_URL')) {
    $dbUrl = getenv('DATABASE_URL');
    $parts = parse_url($dbUrl);
    if ($parts !== false) {
        if (!empty($parts['host'])) $host = $parts['host'];
        if (!empty($parts['port'])) $port = $parts['port'];
        if (!empty($parts['user'])) $username = $parts['user'];
        if (!empty($parts['pass'])) $password = $parts['pass'];
        if (!empty($parts['path'])) $dbname = ltrim($parts['path'], '/');
    }
}

try {
    // اتصال بدون تحديد قاعدة بيانات أولاً (بعض الاستضافات لا تسمح بإنشاء قواعد)
    $pdo = new PDO(
        "mysql:host={$host};port={$port};charset=utf8mb4",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // محاولة إنشاء قاعدة البيانات إن أمكن
    try {
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbname}` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
        $pdo->exec("USE `{$dbname}`");
    } catch (PDOException $inner) {
        // بعض الاستضافات لا تسمح بإنشاء قواعد البيانات عبر الكود — نحاول الاتصال مباشرة بقاعدة البيانات إن كانت موجودة
        try {
            $pdo = new PDO(
                "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4",
                $username,
                $password,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (PDOException $e) {
            throw $inner; // رمي الخطأ الأصلي لو فشل الاتصال
        }
    }

} catch (PDOException $e) {
    // طباعة رسالة أكثر فائدة للمطور
    die("❌ فشل اتصال قاعدة البيانات. تحقق من متغيرات البيئة وإعدادات DB. رسالة الخطأ: " . $e->getMessage());
}
$pdo->exec("USE `$dbname`");
