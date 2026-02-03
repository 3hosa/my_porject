<!-- 4 -->
<?php include 'includes/header.php'; ?>
<?php require 'includes/db.php'; ?>

<?php
$cat = $_GET['cat'] ?? "phones";

// جلب الفئات
$categories = $pdo->query("SELECT * FROM categories ORDER BY id")
                                                                                                                                              ->fetchAll(PDO::FETCH_ASSOC);

// جلب المنتجات حسب الفئة
$stmt = $pdo->prepare("
    SELECT p.*, c.slug as category_slug
    FROM products p
    JOIN categories c ON p.category_id = c.id
    WHERE c.slug = ?
    ORDER BY p.id
");
$stmt->execute([$cat]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// التحقق من تسجيل الدخول للسلة
$isLoggedIn = isset($_SESSION['user_id']);
$current_url = urlencode($_SERVER['REQUEST_URI']);
?>

<h2 class="section-title">منتجات <?= ucfirst($cat) ?></h2>

<div class="products-grid">
<?php foreach($products as $p): ?>
    <div class="product-card" data-name="<?= htmlspecialchars(mb_strtolower($p['name'])) ?>">
        <img data-src="<?= htmlspecialchars($p['img'] ?: 'images/placeholder.svg') ?>" loading="lazy" src="images/placeholder.svg" alt="<?= htmlspecialchars($p['name']) ?>">
        <div class="card-body">
            <h3><?= htmlspecialchars($p['name']) ?></h3>
            <p class="price">$<?= $p['price'] ?></p>

            <div class="buttons">
                <a href="store.php?id=<?= $p['id'] ?>" class="btn btn-outline btn-small">
                    التفاصيل
                </a>

                <?php if ($isLoggedIn): ?>
                    <button class="btn btn-full add-to-cart btn-small" data-id="<?= $p['id'] ?>">إضافة للسلة</button>
                <?php else: ?>
                    <a href="login.php?redirect=<?= $current_url ?>" class="btn btn-login-required btn-small">إضافة للسلة</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endforeach; ?>
</div>

<!-- add-to-cart handled centrally in assets/js/site.js -->

<?php include 'includes/footer.php'; ?>