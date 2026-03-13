</div> <!-- End Main Wrapper -->

<!-- Mobile Footer Links -->
<div class="mobile-policy-links d-lg-none" style="padding: 56px 20px 100px; font-size: 12px; color: #666;">
    <div style="display:flex; flex-wrap:wrap; gap:8px; justify-content:center; line-height:1.7; text-align:center;">
        <a href="<?= BASE_URL ?>page/refundReturns">Refund &amp; Returns Policy</a>
        <span>|</span>
        <a href="<?= BASE_URL ?>page/termsConditions">Terms &amp; Conditions</a>
        <span>|</span>
        <a href="<?= BASE_URL ?>page/privacyPolicy">Privacy Policy</a>
    </div>
</div>

<!-- Desktop Policy Links -->
<div class="desktop-policy-links display-desktop-only" style="padding: 40px 20px 24px; font-size: 14px; color: #666;">
    <div class="desktop-policy-links-inner" style="display:flex; flex-wrap:wrap; gap:12px; justify-content:center; align-items:center; text-align:center;">
        <a href="<?= BASE_URL ?>page/refundReturns">Refund &amp; Returns Policy</a>
        <span>|</span>
        <a href="<?= BASE_URL ?>page/termsConditions">Terms &amp; Conditions</a>
        <span>|</span>
        <a href="<?= BASE_URL ?>page/privacyPolicy">Privacy Policy</a>
    </div>
</div>

<!-- Mobile Bottom Navigation -->
<nav class="bottom-nav">
    <a href="<?= BASE_URL ?>" class="nav-item <?= ($current_page ?? '') == 'home' ? 'active' : '' ?>">
        <img src="<?= BASE_URL ?>assets/icons/home.png" class="nav-icon-img" alt="Home">
        <span>Home</span>
    </a>
    <a href="<?= BASE_URL ?>discounts" class="nav-item">
        <img src="<?= BASE_URL ?>assets/icons/discount.png" class="nav-icon-img" alt="Discounts">
        <span>Discounts</span>
    </a>
    <a href="<?= BASE_URL ?>shop/categories"
        class="nav-item <?= ($current_page ?? '') == 'categories' ? 'active' : '' ?>">
        <img src="<?= BASE_URL ?>assets/icons/category.png" class="nav-icon-img" alt="Categories">
        <span>Categories</span>
    </a>
    <a href="<?= BASE_URL ?>cart" class="nav-item">
        <img src="<?= BASE_URL ?>assets/icons/cart.png" class="nav-icon-img" alt="My Cart">
        <span>My Cart</span>
    </a>
    <a href="<?= BASE_URL ?>reviews" class="nav-item">
        <img src="<?= BASE_URL ?>assets/icons/reviews.png" class="nav-icon-img" alt="Reviews">
        <span>Reviews</span>
    </a>
</nav>

<!-- Desktop Footer -->
<footer class="main-footer display-desktop-only">
    <div class="container">
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 40px;">
            <div>
                <h3>
                    <?= isset($settings['shop_name']) ? htmlspecialchars($settings['shop_name']) : 'Shop Name' ?>
                </h3>
                <p>Tailored to your tastes...</p>
                <p style="font-size: 14px; color: #666;">
                    No: 213/7, Ghanaimula Mw,<br>
                    Hewagama, Kaduwela.<br>
                    <?= isset($settings['shop_whatsapp']) ? $settings['shop_whatsapp'] : '076 000 0000' ?><br>
                    info@darklavender.com
                </p>
                <button class="btn-success"
                    style="padding: 10px 20px; border:none; border-radius: 5px; cursor: pointer; color: white; background: #25d366;">Give
                    us a Review!</button>
                <div style="margin-top: 15px; display: flex; gap: 10px;">
                    <i class="fab fa-facebook" style="font-size: 24px; color: #1877F2;"></i>
                    <i class="fab fa-tiktok" style="font-size: 24px; color: black;"></i>
                    <i class="fab fa-instagram" style="font-size: 24px; color: #E4405F;"></i>
                    <i class="fab fa-youtube" style="font-size: 24px; color: #FF0000;"></i>
                </div>
            </div>
            <div>
                <div style="display:flex; flex-wrap:wrap; gap:12px; align-items:center; font-size:14px; color:#555;">
                    <a href="<?= BASE_URL ?>page/refundReturns">Refund &amp; Returns Policy</a>
                    <span>|</span>
                    <a href="<?= BASE_URL ?>page/termsConditions">Terms &amp; Conditions</a>
                    <span>|</span>
                    <a href="<?= BASE_URL ?>page/privacyPolicy">Privacy Policy</a>
                </div>
            </div>
            <div>
                <!-- Newsletter or other info -->
            </div>
        </div>
    </div>
</footer>

<!-- Floating WhatsApp -->
<?php if (!empty($settings['shop_whatsapp'])): ?>
    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $settings['shop_whatsapp']) ?>"
        class="floating-whatsapp display-desktop-only" target="_blank">
        <i class="fab fa-whatsapp"></i>
    </a>
<?php endif; ?>

<!-- Floating Cart Bubble (Mobile Only) -->
<?php
$cartCount = isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'qty')) : 0;
// Render IF not on cart page (Hidden by default if count is 0)
if (($current_page ?? '') !== 'cart'):
    ?>
    <a href="<?= BASE_URL ?>cart" class="floating-cart d-lg-none" 
       style="display: <?= $cartCount > 0 ? 'flex' : 'none' ?>;">
        <i class="fas fa-shopping-cart"></i>
        <span class="floating-cart-count"><?= $cartCount ?></span>
    </a>
<?php endif; ?>


<!-- Cart Toast Overlay -->
<div id="cartToast">
    <div class="ct-content">
        <div class="ct-emoji">😍</div>
        <div class="ct-message-pill">
            Great Choice!<br>
            The Product added to the Cart!
        </div>
        <div class="ct-view-cart" onclick="window.location.href='<?= BASE_URL ?>cart'">View Cart</div>
        <div class="ct-close" onclick="hideCartToast()">
            <i class="fas fa-times"></i>
        </div>
    </div>
</div>

<script>
    let toastTimeout;

    function showCartToast() {
        const toast = document.getElementById('cartToast');
        toast.style.display = 'flex';

        // Auto Hide after 3.5 seconds
        clearTimeout(toastTimeout);
        toastTimeout = setTimeout(() => {
            hideCartToast();
        }, 3500);

    }

    function hideCartToast() {
        document.getElementById('cartToast').style.display = 'none';
        clearTimeout(toastTimeout);
    }

    
    // --- Global Loader Controller---
    let globalLoaderTimeout;

    function showGlobalLoader() {
        // Clear any existing timer to avoid double triggers
        clearTimeout(globalLoaderTimeout);

        const loader = document.getElementById('globalLoader');
        if (loader) {
            loader.style.display = 'flex';
        }
    }

    function hideGlobalLoader() {
        // Cancel the show timer immediately
        clearTimeout(globalLoaderTimeout);
        // Hide the loader immediately
        const loader = document.getElementById('globalLoader');
        if (loader) {
            loader.style.display = 'none';
        }
    }

    
        // --- Navigation Interceptor (Fixed) ---
    document.addEventListener('click', function(e) {
        const link = e.target.closest('a');
        if (!link) return;
        
        // 1. Basic Safety
        if (link.target === '_blank') return; 
        const hrefAttr = link.getAttribute('href');
        if (!hrefAttr || hrefAttr.startsWith('#') || hrefAttr.startsWith('javascript:') || hrefAttr.startsWith('mailto:') || hrefAttr.startsWith('tel:')) return;
        if (link.classList.contains('no-loader')) return;

        // 2. Internal Link Check (Hostname Matching)
        // This works for both relative (/shop) and absolute (http://site.com/shop) links
        if (link.hostname === window.location.hostname) {
            showGlobalLoader();
        }
    });


    // --- Safety Valve  ---
    // If user clicks "Back" button, the page might be loaded from cache with loader still visible.
    // This forces it to hide.
    window.addEventListener('pageshow', function(event) {
        hideGlobalLoader();
    });


</script>

<!-- Global Loading Overlay -->
<div id="globalLoader" class="global-loader-overlay">
    <div class="global-loader-spinner"></div>
    <div class="global-loader-text">Loading...</div>
</div>

</body>

</html>
