<!-- 5 -->
<?php include 'includes/header.php'; ?>
<?php require 'includes/db.php'; ?>

<?php
if (isset($_SESSION['message'])) {
    echo '<div class="success-message">' . $_SESSION['message'] . '</div>';
    unset($_SESSION['message']);
}

$id = $_GET['id'] ?? null;

if($id){
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();

    if($product){
        // تحويل features من string إلى array
        $product['features'] = explode(',', $product['features']);
    }
}
?>

<?php if(!$product): ?>
<div class="container">
<p>❌ المنتج غير موجود</p>
<a href="products.php" class="btn">العودة للمنتجات</a>
</div>
<?php else: ?>
<div class="product-details-card">
    <div class="product-image">
        <img data-src="<?= htmlspecialchars($product['img'] ?: 'images/placeholder.svg') ?>" loading="lazy" src="images/placeholder.svg" alt="<?= $product['name'] ?>">
    </div>

    <div class="product-info">
        <h2><?= $product['name'] ?></h2>
        <p class="price">$<?= $product['price'] ?></p>

        <ul class="features">
            <?php foreach($product['features'] as $f): ?>
            <li><?= $f ?></li>
            <?php endforeach; ?>
        </ul>

        <form method="post" action="/php1/add_to_cart.php" class="add-to-cart-form" data-no-pjax>
            <label for="qty">الكمية:</label>
            <input type="number" id="qty" name="qty" value="1" min="1" class="qty-input">
            <input type="hidden" name="id" value="<?= $id ?>">
            <button type="button" class="btn btn-full add-to-cart-inline" data-id="<?= $id ?>">إضافة للسلة</button>
        </form>
        <script>
        (function(){
            var btn = document.querySelector('.add-to-cart-inline');
            if (!btn) return;
            btn.addEventListener('click', function(e){
                var form = btn.closest('.add-to-cart-form');
                var orig = btn.textContent;
                btn.disabled = true; btn.textContent = 'جاري الإضافة...';

                var id = btn.dataset.id || (form && form.querySelector('input[name="id"]') ? form.querySelector('input[name="id"]').value : null);
                var qty = form && (form.querySelector('input[name="qty"]') || form.querySelector('input[name="quantity"]')) ? (form.querySelector('input[name="qty"]') || form.querySelector('input[name="quantity"]').value) : 1;
                qty = qty || 1;

                if (!id) { if (window.showToast) window.showToast('❌ خطأ: معرّف المنتج مفقود'); btn.disabled = false; btn.textContent = orig; return; }

                var body = 'id=' + encodeURIComponent(id) + '&qty=' + encodeURIComponent(qty);

                fetch('/php1/add_to_cart.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: body,
                    credentials: 'same-origin'
                }).then(function(res){
                    if (res.status === 401) { window.location.href = '/php1/login.php?redirect='+encodeURIComponent(window.location.pathname+window.location.search); return null; }
                    return res.text();
                }).then(function(count){
                    if (count) {
                        var cb = document.querySelector('.cart-count');
                        if (cb) cb.textContent = count;
                        if (window.showToast) window.showToast('✅ تم إضافة المنتج للسلة'); else alert('تم الإضافة');
                    }
                }).catch(function(err){
                    console.error('add_to_cart fetch error', err);
                    if (window.showToast) window.showToast('❌ حدث خطأ أثناء الإضافة'); else alert('خطأ');
                }).finally(function(){ btn.disabled = false; btn.textContent = orig; });
            });
        })();
        </script>
    </div>
</div>

<?php endif; ?>

<?php include 'includes/footer.php'; ?>
