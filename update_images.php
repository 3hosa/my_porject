
<?php
require 'includes/db.php';

try {
    // تحديث مسارات الصور في قاعدة البيانات
    $pdo->exec("UPDATE products SET img = REPLACE(img, 'images/', 'assets/images/') WHERE img LIKE 'images/%'");

    echo "تم تحديث مسارات الصور بنجاح!";
} catch (PDOException $e) {
    echo "خطأ: " . $e->getMessage();
}
?>
