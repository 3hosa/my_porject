<?php
if (session_status() == PHP_SESSION_NONE) session_start();
require 'includes/db.php';  // الاتصال بقاعدة البيانات
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// جلب السلة من الجلسة
$cart = $_SESSION['cart'] ?? [];

if(empty($cart)){
    echo "<p>سلة الشراء فارغة. <a href='index.php'>العودة للتسوق</a></p>";
    include 'includes/footer.php';
    exit;
}

// حساب الإجمالي
$total = 0;
$product_prices = [];

$product_ids = array_keys($cart);
$placeholders = str_repeat('?,', count($product_ids) - 1) . '?';
$stmt = $pdo->prepare("SELECT id, name, price, img FROM products WHERE id IN ($placeholders)");
$stmt->execute($product_ids);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// إعداد مصفوفة الأسعار للمزيد من السهولة
foreach($products as $p){
    $product_prices[$p['id']] = $p['price'];
    $total += $p['price'] * $cart[$p['id']]['qty'];
}

$message = '';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $name    = trim($_POST['name'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $payment = $_POST['payment'] ?? '';

    if($name && $phone && $address){
        try {
            // إدراج الطلب
            $stmt = $pdo->prepare("INSERT INTO orders (customer_name, customer_phone, customer_address, payment_method, total) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $phone, $address, $payment, $total]);
            $order_id = $pdo->lastInsertId();

            // إدراج عناصر الطلب
            $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            foreach($cart as $product_id => $item){
                $stmt->execute([$order_id, $product_id, $item['qty'], $product_prices[$product_id]]);
            }

            // مسح السلة
            unset($_SESSION['cart']);

            $message = "<div class='success-message'>✅ تم تأكيد الطلب بنجاح! رقم الطلب: $order_id</div>";

        } catch(PDOException $e) {
            $message = "<div class='error-message'>❌ خطأ في حفظ الطلب: " . $e->getMessage() . "</div>";
        }
    } else {
        $message = "<div class='error-message'>❌ يرجى ملء جميع الحقول</div>";
    }
}
?>

<h2 class="section-title">🧾 إتمام الطلب</h2>

<?= $message ?>

<h3>🛒 محتويات السلة</h3>
<table style="width:100%; border-collapse: collapse;">
    <thead>
        <tr style="background:#f0f0f0;">
            <th style="padding:10px; border:1px solid #ccc;">صورة المنتج</th>
            <th style="padding:10px; border:1px solid #ccc;">المنتج</th>
            <th style="padding:10px; border:1px solid #ccc;">الكمية</th>
            <th style="padding:10px; border:1px solid #ccc;">السعر</th>
            <th style="padding:10px; border:1px solid #ccc;">المجموع</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($products as $p): ?>
        <tr>
            <td style="padding:10px; border:1px solid #ccc;">
                <img src="<?= htmlspecialchars($p['img']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" width="60">
            </td>
            <td style="padding:10px; border:1px solid #ccc;"><?= htmlspecialchars($p['name']) ?></td>
            <td style="padding:10px; border:1px solid #ccc; text-align:center;"><?= $cart[$p['id']]['qty'] ?></td>
            <td style="padding:10px; border:1px solid #ccc;">$<?= $p['price'] ?></td>
            <td style="padding:10px; border:1px solid #ccc;">$<?= $p['price'] * $cart[$p['id']]['qty'] ?></td>
        </tr>
        <?php endforeach; ?>
        <tr style="font-weight:bold;">
            <td colspan="4" style="padding:10px; border:1px solid #ccc; text-align:right;">الإجمالي:</td>
            <td style="padding:10px; border:1px solid #ccc;">$<?= $total ?></td>
        </tr>
    </tbody>
</table>

<h3>معلومات العميل</h3>
<form class="checkout-form" method="post" action="" style="max-width:400px;">
    <input type="text" name="name" placeholder="الاسم الكامل" required style="width:100%; padding:8px; margin-bottom:10px;">
    <input type="tel" name="phone" placeholder="رقم الهاتف" required style="width:100%; padding:8px; margin-bottom:10px;">
    <input type="text" name="address" placeholder="العنوان" required style="width:100%; padding:8px; margin-bottom:10px;">
    <select name="payment" style="width:100%; padding:8px; margin-bottom:10px;">
        <option>الدفع عند الاستلام</option>
        <option>بطاقة بنكية</option>
    </select>
    <button type="submit" style="width:100%; padding:10px; background:#28a745; color:white; border:none; cursor:pointer;">تأكيد الطلب</button>
</form>

<?php include 'includes/footer.php'; ?>



