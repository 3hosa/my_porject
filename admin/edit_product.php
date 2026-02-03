<?php
session_start();
require '../includes/db.php';

// التحقق الصارم من صلاحية الأدمن
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php?error=unauthorized');
    exit;
}

include '../includes/header.php';

$message = '';

// التحقق من وجود معرف المنتج
$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header('Location: admin.php');
    exit;
}

// جلب بيانات المنتج
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header('Location: admin.php');
    exit;
}

// جلب الفئات
$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// معالجة النموذج عند الإرسال
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $category_id = intval($_POST['category'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $features = trim($_POST['features'] ?? '');
    $img = trim($_POST['img'] ?? '');

    if ($name && $price > 0 && $category_id) {
        try {
            $stmt = $pdo->prepare("
                UPDATE products 
                SET name = ?, price = ?, img = ?, category_id = ?, description = ?, features = ? 
                WHERE id = ?
            ");
            $stmt->execute([$name, $price, $img, $category_id, $description, $features, $id]);
            $message = "<div class='success-message'>✅ تم تحديث المنتج بنجاح!</div>";

            // إعادة جلب البيانات بعد التحديث
            $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->execute([$id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $message = "<div class='error-message'>❌ خطأ: " . $e->getMessage() . "</div>";
        }
    } else {
        $message = "<div class='error-message'>❌ يرجى ملء الحقول المطلوبة بشكل صحيح.</div>";
    }
}
?>

<h2 class="section-title">تعديل المنتج</h2>

<?= $message ?>

<div class="form-card">
    <form method="post" action="">
        <input type="text" name="name" placeholder="اسم المنتج" value="<?= htmlspecialchars($product['name']) ?>" required>
        <input type="number" step="0.01" name="price" placeholder="السعر" value="<?= $product['price'] ?>" required>
        <input type="text" name="img" placeholder="رابط الصورة" value="<?= htmlspecialchars($product['img']) ?>" required>
        
        <select name="category" required>
            <option value="">اختر الفئة</option>
            <?php foreach($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $product['category_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <textarea name="description" placeholder="وصف المنتج" rows="3"><?= htmlspecialchars($product['description']) ?></textarea>
        <textarea name="features" placeholder="المميزات (افصل بين كل ميزة بفاصلة)" rows="3"><?= htmlspecialchars($product['features']) ?></textarea>

        <button type="submit">تحديث المنتج</button>
        <a href="admin.php" class="btn btn-secondary" style="display:block; text-align:center; margin-top:10px; text-decoration:none; background:#6c757d; color:white; padding:12px; border-radius:8px;">إلغاء</a>
    </form>
</div>

<style>
.form-card { background:#fff; padding:30px 40px; border-radius:12px; box-shadow:0 8px 25px rgba(0,0,0,0.1); width:100%; max-width:500px; margin:20px auto; }
.form-card input, .form-card select, .form-card textarea { width:100%; padding:12px 15px; margin:8px 0 20px; border:1px solid #ccc; border-radius:8px; font-size:16px; transition:.3s; box-sizing:border-box; }
.form-card input:focus, .form-card select:focus, .form-card textarea:focus { border-color:#007bff; outline:none; box-shadow:0 0 5px rgba(0,123,255,0.5); }
.form-card button { width:100%; padding:12px; background-color:#007bff; border:none; border-radius:8px; color:white; font-size:16px; cursor:pointer; transition:.3s; }
.form-card button:hover { background-color:#0056b3; }
.success-message { background:#d4edda; color:#155724; padding:12px; border-radius:8px; margin-bottom:15px; text-align:center; }
.error-message { background:#f8d7da; color:#721c24; padding:12px; border-radius:8px; margin-bottom:15px; text-align:center; }
</style>

<?php include '../includes/footer.php'; ?>