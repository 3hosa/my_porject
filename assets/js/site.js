// Small UI helpers: mobile nav toggle, lazy-loading fallback, back-to-top, search filter
function siteInit(){
    // Mobile nav toggle
    var toggle = document.querySelector('.nav-toggle');
    var nav = document.querySelector('.main-nav');
    if (toggle && nav) {
        toggle.addEventListener('click', function (e) {
            e.preventDefault();
            nav.classList.toggle('show-nav');
        });
    }

    // Back to top button
    var back = document.getElementById('backToTop');
    if(!back){
        back = document.createElement('button');
        back.id = 'backToTop';
        back.title = 'العودة للأعلى';
        back.innerHTML = '⬆';
        back.style.position = 'fixed';
        back.style.right = '18px';
        back.style.bottom = '18px';
        back.style.padding = '10px 12px';
        back.style.borderRadius = '8px';
        back.style.border = 'none';
        back.style.background = 'linear-gradient(90deg,#2563eb,#1e40af)';
        back.style.color = 'white';
        back.style.cursor = 'pointer';
        back.style.boxShadow = '0 6px 18px rgba(16,24,40,0.2)';
        back.style.display = 'none';
        back.style.zIndex = 2000;
        document.body.appendChild(back);
    }

    window.addEventListener('scroll', function () {
        if (window.scrollY > 300) back.style.display = 'block';
        else back.style.display = 'none';
    });
    back.addEventListener('click', function () { window.scrollTo({top: 0, behavior: 'smooth'}); });

    // Lazy load fallback using IntersectionObserver
    if ('IntersectionObserver' in window) {
        var imgs = document.querySelectorAll('img[data-src]');
        var io = new IntersectionObserver(function (entries, obs) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    var img = entry.target;
                    img.src = img.getAttribute('data-src');
                    img.removeAttribute('data-src');
                    obs.unobserve(img);
                }
            });
        }, {rootMargin: '50px 0px'});
        imgs.forEach(function (i) { io.observe(i); });
    } else {
        // Fallback: load all images
        var imgs = document.querySelectorAll('img[data-src]');
        imgs.forEach(function (i) { i.src = i.getAttribute('data-src'); i.removeAttribute('data-src'); });
    }

    // Instant client-side filtering on products page
    var searchInput = document.querySelector('input[name="q"]');
    if (searchInput && window.location.pathname.indexOf('products.php') !== -1) {
        searchInput.addEventListener('input', function (e) {
            var v = e.target.value.trim().toLowerCase();
            document.querySelectorAll('.product-card').forEach(function (card) {
                var name = (card.getAttribute('data-name') || '').toLowerCase();
                if (!v || name.indexOf(v) !== -1) card.style.display = '';
                else card.style.display = 'none';
            });
        });
    }

    // Delegated add-to-cart handler (works after PJAX content replacement)
    if (!window.__delegated_add_to_cart) {
        document.addEventListener('click', function (e) {
            var btn = e.target.closest('.add-to-cart');
            if (!btn) return;

            // if inside a form, let the form submit normally
            if (btn.closest('form')) return;

            e.preventDefault();
            var id = btn.dataset.id;
            if (!id) return;

            var originalText = btn.textContent;
            btn.textContent = 'جاري الإضافة...';
            btn.disabled = true;

            fetch('/php1/add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'id=' + encodeURIComponent(id) + '&qty=1'
            })
            .then(function (response) {
                if (response.status === 401) {
                    window.location.href = '/php1/login.php';
                    return;
                }
                return response.text();
            })
            .then(function (count) {
                if (count) {
                    // update header badge
                    var cartBadge = document.querySelector('.cart-count');
                    if (cartBadge) cartBadge.textContent = count;
                    showToast('✅ تم إضافة المنتج للسلة بنجاح');
                }
            })
            .catch(function (err) {
                console.error('Add to cart error', err);
                showToast('❌ حدث خطأ أثناء الإضافة');
            })
            .finally(function () {
                btn.textContent = originalText;
                btn.disabled = false;
            });
        });
        window.__delegated_add_to_cart = true;
    }

    // Intercept add-to-cart forms (product detail) and submit via AJAX to avoid navigation
    if (!window.__delegated_add_to_cart_form) {
        document.addEventListener('submit', function (e) {
            var form = e.target;
            if (!form || !form.classList || !form.classList.contains('add-to-cart-form')) return;

            e.preventDefault();

            var submitBtn = form.querySelector('button[type="submit"]') || form.querySelector('input[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                var origText = submitBtn.textContent;
                submitBtn.textContent = 'جاري الإضافة...';
            }

            var id = form.querySelector('input[name="id"]') ? form.querySelector('input[name="id"]').value : null;
            var qtyEl = form.querySelector('input[name="qty"]') || form.querySelector('input[name="quantity"]');
            var qty = qtyEl ? qtyEl.value : 1;
            if (!id) {
                showToast('❌ خطأ: معرّف المنتج مفقود');
                if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = origText; }
                return;
            }

            var body = 'id=' + encodeURIComponent(id) + '&qty=' + encodeURIComponent(qty);

            fetch('/php1/add_to_cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: body,
                credentials: 'same-origin'
            })
            .then(function (res) {
                if (res.status === 401) {
                    window.location.href = '/php1/login.php?redirect=' + encodeURIComponent(window.location.pathname + window.location.search);
                    return null;
                }
                return res.text();
            })
            .then(function (count) {
                if (count) {
                    var cartBadge = document.querySelector('.cart-count');
                    if (cartBadge) cartBadge.textContent = count;
                    showToast('✅ تم إضافة المنتج للسلة');
                }
            })
            .catch(function (err) {
                console.error('Add to cart form error', err);
                showToast('❌ حدث خطأ أثناء الإضافة');
            })
            .finally(function () {
                if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = origText; }
            });
        });
        window.__delegated_add_to_cart_form = true;
    }
}

// Small toast helper (non-blocking)
function showToast(message, timeout){
    timeout = timeout || 3000;
    var container = document.getElementById('site-toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'site-toast-container';
        container.style.position = 'fixed';
        container.style.right = '18px';
        container.style.top = '18px';
        container.style.zIndex = 99999;
        container.style.display = 'flex';
        container.style.flexDirection = 'column';
        container.style.gap = '8px';
        document.body.appendChild(container);
    }

    var t = document.createElement('div');
    t.textContent = message;
    t.style.background = 'rgba(0,0,0,0.8)';
    t.style.color = 'white';
    t.style.padding = '10px 14px';
    t.style.borderRadius = '8px';
    t.style.boxShadow = '0 8px 24px rgba(2,6,23,0.4)';
    t.style.maxWidth = '320px';
    t.style.fontSize = '14px';
    t.style.opacity = '0';
    t.style.transform = 'translateY(-6px)';
    t.style.transition = 'all 220ms ease';

    container.appendChild(t);
    // force reflow
    void t.offsetWidth;
    t.style.opacity = '1';
    t.style.transform = 'translateY(0)';

    setTimeout(function(){
        t.style.opacity = '0';
        t.style.transform = 'translateY(-6px)';
        setTimeout(function(){ container.removeChild(t); }, 220);
    }, timeout);
}

// Auto-run on first load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', siteInit);
} else {
    siteInit();
}
