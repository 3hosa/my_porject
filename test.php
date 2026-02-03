<?php
require 'includes/db.php';

$pdo->exec("CREATE TABLE test_success (
    id INT AUTO_INCREMENT PRIMARY KEY
)");

echo "🎉 قاعدة البيانات والجداول تعمل بشكل صحيح";
