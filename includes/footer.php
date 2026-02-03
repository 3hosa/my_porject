<!-- 3 -->
<footer class="main-footer">
    <div class="site-container">
        <p>© 2026 جميع الحقوق محفوظة | متجر الإلكترونيات</p>
        <p style="margin-top:6px;">
            <a href="#" style="color:inherit; margin:0 6px;"><i class="fab fa-facebook"></i></a>
            <a href="#" style="color:inherit; margin:0 6px;"><i class="fab fa-instagram"></i></a>
            <a href="#" style="color:inherit; margin:0 6px;"><i class="fab fa-twitter"></i></a>
        </p>
    </div>
</footer>

</div> <!-- End of container -->
</body>
</html>

<script>
// تحديث عداد السلة تلقائياً من endpoint
document.addEventListener('DOMContentLoaded', function(){
    function updateCartCount(){
        fetch('/php1/cart_count.php', {cache: 'no-store'})
            .then(r => r.text())
            .then(count => {
                document.querySelectorAll('.cart-count').forEach(el => el.textContent = count);
            })
            .catch(()=>{});
    }

    // تحديث فور التحميل
    updateCartCount();

    // تحديث كل 30 ثانية (أقل تحميل على الخادم)
    setInterval(updateCartCount, 30000);
});
</script>

<!-- Site JS -->
<script defer src="/php1/assets/js/site.js"></script>
<script defer src="/php1/assets/js/pjax.js"></script>


