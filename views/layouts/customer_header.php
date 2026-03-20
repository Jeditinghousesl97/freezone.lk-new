<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php require_once 'helpers/SeoHelper.php'; ?>
    <?php require_once ROOT_PATH . 'helpers/ImageHelper.php'; ?>
    <title>
        <?= htmlspecialchars(isset($seo_title) ? $seo_title : (isset($title) ? $title : 'Ecom Shop')) ?>
    </title>
    <?php
    $metaTitle = isset($seo_title) ? $seo_title : (isset($title) ? $title : 'Ecom Shop');
    $metaDescription = isset($seo_description) ? $seo_description : ($share_description ?? ($settings['shop_about'] ?? ''));
    $metaImage = isset($seo_image) ? $seo_image : ($share_image ?? ($settings['shop_logo'] ?? ''));
    $metaImage = SeoHelper::normalizeAssetUrl($metaImage);
    $metaUrl = isset($seo_canonical) ? $seo_canonical : SeoHelper::currentUrl(true);
    $metaRobots = isset($seo_robots) ? $seo_robots : 'index,follow';
    $metaType = isset($seo_type) && $seo_type === 'product' ? 'product' : 'website';
    $shopName = !empty($settings['shop_name']) ? $settings['shop_name'] : 'Online Shop';
    $customerCssVersion = @filemtime(ROOT_PATH . 'assets/css/customer.css') ?: time();
    $desktopCssVersion = @filemtime(ROOT_PATH . 'assets/css/customer-desktop-refresh.css') ?: time();
    $currencyCode = !empty($settings['currency_symbol']) ? preg_replace('/[^A-Z]/', '', strtoupper((string) $settings['currency_symbol'])) : 'LKR';
    if (strlen($currencyCode) !== 3) {
        $currencyCode = 'LKR';
    }
    $googleAnalyticsId = trim((string) ($settings['google_analytics_id'] ?? ''));
    $metaPixelId = preg_replace('/[^0-9]/', '', (string) ($settings['meta_pixel_id'] ?? ''));
    ?>
    <meta name="description" content="<?= htmlspecialchars($metaDescription) ?>">
    <meta name="robots" content="<?= htmlspecialchars($metaRobots) ?>">
    <link rel="canonical" href="<?= htmlspecialchars($metaUrl) ?>">
    <meta property="og:title" content="<?= htmlspecialchars($metaTitle) ?>">
    <meta property="og:site_name" content="<?= htmlspecialchars($shopName) ?>">
    <?php if (!empty($metaDescription)): ?>
        <meta property="og:description" content="<?= htmlspecialchars($metaDescription) ?>">
        <meta name="twitter:description" content="<?= htmlspecialchars($metaDescription) ?>">
    <?php endif; ?>
    <meta property="og:type" content="<?= htmlspecialchars($metaType) ?>">
    <meta property="og:url" content="<?= htmlspecialchars($metaUrl) ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($metaTitle) ?>">
    <?php if (!empty($metaImage)): ?>
        <meta property="og:image" content="<?= htmlspecialchars($metaImage) ?>">
        <meta name="twitter:image" content="<?= htmlspecialchars($metaImage) ?>">
    <?php endif; ?>
    <?php if (!empty($seo_json_ld) && is_array($seo_json_ld)): ?>
        <?php foreach ($seo_json_ld as $schema): ?>
            <?php if (!empty($schema)): ?>
                <script type="application/ld+json"><?= json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>
    <!-- Use the new Customer CSS -->
    <?php if (!empty($settings['shop_favicon'])): ?>
        <link rel="icon" type="image/x-icon" href="<?= htmlspecialchars(ImageHelper::settingsImageUrl($settings['shop_favicon'], str_replace('/Ecom-CMS/', BASE_URL, $settings['shop_favicon']))) ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/customer.css?v=<?= $customerCssVersion ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/customer-desktop-refresh.css?v=<?= $desktopCssVersion ?>">
    <!-- Font Awesome for Icons (Optional, or use images) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Dynamic Google Fonts Loader -->
    <?php if (!empty($settings['font_family'])):
        $font = $settings['font_family'];
        $fontUrl = urlencode($font);
        // Load common weights: 300, 400, 500, 600, 700, 800
        $gFontLink = "https://fonts.googleapis.com/css2?family={$fontUrl}:wght@300;400;500;600;700;800&display=swap";
        ?>
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="<?= $gFontLink ?>" rel="stylesheet">
    <?php endif; ?>

    <?php if ($googleAnalyticsId !== ''): ?>
        <script async src="https://www.googletagmanager.com/gtag/js?id=<?= htmlspecialchars($googleAnalyticsId) ?>"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag() { dataLayer.push(arguments); }
            gtag('js', new Date());
            gtag('config', '<?= htmlspecialchars($googleAnalyticsId, ENT_QUOTES) ?>', {
                send_page_view: true
            });
        </script>
    <?php endif; ?>

    <?php if ($metaPixelId !== ''): ?>
        <script>
            !function (f, b, e, v, n, t, s) {
                if (f.fbq) return;
                n = f.fbq = function () {
                    n.callMethod ? n.callMethod.apply(n, arguments) : n.queue.push(arguments);
                };
                if (!f._fbq) f._fbq = n;
                n.push = n;
                n.loaded = !0;
                n.version = '2.0';
                n.queue = [];
                t = b.createElement(e);
                t.async = !0;
                t.src = v;
                s = b.getElementsByTagName(e)[0];
                s.parentNode.insertBefore(t, s);
            }(window, document, 'script', 'https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', '<?= htmlspecialchars($metaPixelId, ENT_QUOTES) ?>');
            fbq('track', 'PageView');
        </script>
        <noscript>
            <img height="1" width="1" style="display:none"
                src="https://www.facebook.com/tr?id=<?= htmlspecialchars($metaPixelId) ?>&ev=PageView&noscript=1" alt="">
        </noscript>
    <?php endif; ?>

    <script>
        window.APP_CSRF_TOKEN = '<?= htmlspecialchars(csrf_token(), ENT_QUOTES) ?>';
        window.APP_BASE_URL = '<?= htmlspecialchars(BASE_URL, ENT_QUOTES) ?>';
        window.APP_CURRENCY = '<?= htmlspecialchars($currencyCode, ENT_QUOTES) ?>';

        window.appendCsrfToken = function (form) {
            if (!form || !window.APP_CSRF_TOKEN) {
                return form;
            }

            var existing = form.querySelector('input[name="_csrf"]');
            if (!existing) {
                existing = document.createElement('input');
                existing.type = 'hidden';
                existing.name = '_csrf';
                form.appendChild(existing);
            }

            existing.value = window.APP_CSRF_TOKEN;
            return form;
        };

        window.csrfHeaders = function (headers) {
            var nextHeaders = headers || {};
            nextHeaders['X-CSRF-Token'] = window.APP_CSRF_TOKEN;
            return nextHeaders;
        };

        window.trackAnalyticsEvent = function (eventName, params, metaEventName, metaParams) {
            var payload = params || {};

            try {
                window.dataLayer = window.dataLayer || [];
                window.dataLayer.push(Object.assign({ event: eventName }, payload));
            } catch (e) {
                console.warn('dataLayer push failed', e);
            }

            if (typeof window.gtag === 'function') {
                try {
                    window.gtag('event', eventName, payload);
                } catch (e) {
                    console.warn('GA tracking failed', e);
                }
            }

            if (typeof window.fbq === 'function' && metaEventName) {
                try {
                    window.fbq('track', metaEventName, metaParams || payload);
                } catch (e) {
                    console.warn('Meta tracking failed', e);
                }
            }
        };

        window.trackPurchaseOnce = function (orderNumber, payload, metaParams) {
            if (!orderNumber) {
                window.trackAnalyticsEvent('purchase', payload, 'Purchase', metaParams || payload);
                return;
            }

            var storageKey = 'purchase_tracked_' + orderNumber;
            try {
                if (window.localStorage.getItem(storageKey)) {
                    return;
                }
            } catch (e) {
            }

            window.trackAnalyticsEvent('purchase', payload, 'Purchase', metaParams || payload);

            try {
                window.localStorage.setItem(storageKey, '1');
            } catch (e) {
            }
        };

        window.buildAnalyticsItem = function (item) {
            return {
                item_id: String(item.id || ''),
                item_name: String(item.title || item.name || 'Product'),
                item_variant: String(item.variant || item.variants || ''),
                price: Number(item.price || 0),
                quantity: Number(item.quantity || item.qty || 1)
            };
        };
    </script>

    <!-- Dynamic Global Styles -->
<style>
        :root {
            /* Core Colors */
            <?php if (!empty($settings['primary_color'])): ?>
                --primary-color:
                    <?= $settings['primary_color'] ?>
                ;
            <?php endif; ?>

            <?php if (!empty($settings['secondary_color'])): ?>
                --secondary-color:
                    <?= $settings['secondary_color'] ?>
                ;
            <?php endif; ?>

            <?php if (!empty($settings['bg_color'])): ?>
                --bg-white:
                    <?= $settings['bg_color'] ?>
                ;
            <?php endif; ?>

            /* Typography */
            <?php if (!empty($settings['font_family'])): ?>
                --font-family: '<?= $settings['font_family'] ?>', sans-serif;
            <?php endif; ?>

            <?php if (!empty($settings['body_color'])): ?>
                --text-dark:
                    <?= $settings['body_color'] ?>
                ;
            <?php endif; ?>

            /* UI Elements */
            <?php if (!empty($settings['global_img_radius'])): ?>
                --border-radius:
                    <?= $settings['global_img_radius'] ?>
                    px;
            <?php endif; ?>
        }

        body {
            <?php if (!empty($settings['font_family'])): ?>
                font-family: var(--font-family);
            <?php endif; ?>
            <?php if (!empty($settings['body_size'])): ?>
                font-size:
                    <?= $settings['body_size'] ?>
                    px;
            <?php endif; ?>
            <?php if (!empty($settings['body_line_height'])): ?>
                line-height:
                    <?= $settings['body_line_height'] ?>
                ;
            <?php endif; ?>
        }

        h1,
        h2,
        h3,
        .section-title,
        .pd-title {
            <?php if (!empty($settings['h1_color'])): ?>
                color:
                    <?= $settings['h1_color'] ?>
                    !important;
            <?php endif; ?>
        }

        <?php if (!empty($settings['h1_size'])): ?>
            h1,
            .pd-title {
                font-size:
                    <?= $settings['h1_size'] ?>
                    px !important;
            }

        <?php endif; ?>

        /* Button Overrides */
        <?php if (!empty($settings['btn_bg_color'])): ?>
            .btn-cart,
            .btn-action,
            .add-btn-blue,
            .btn-red {
                background-color:
                    <?= $settings['btn_bg_color'] ?>
                    !important;
            }

        <?php endif; ?>

        <?php if (!empty($settings['btn_text_color'])): ?>
            .btn-cart,
            .btn-action,
            .add-btn-blue,
            .btn-red {
                color:
                    <?= $settings['btn_text_color'] ?>
                    !important;
            }

        <?php endif; ?>

        <?php if (!empty($settings['btn_radius'])): ?>
            .btn-cart,
            .btn-action,
            .add-btn-blue,
            .btn-red {
                border-radius:
                    <?= $settings['btn_radius'] ?>
                    px !important;
            }

        <?php endif; ?>

        /* Granular Button Overrides (Safe List) */
        /* Add to Cart */
        <?php if (!empty($settings['btn_addcart_bg'])): ?>
            .btn-action.btn-cart {
                background-color:
                    <?= $settings['btn_addcart_bg'] ?>
                    !important;
            }

        <?php endif; ?>
        <?php if (!empty($settings['btn_addcart_text'])): ?>
            .btn-action.btn-cart {
                color:
                    <?= $settings['btn_addcart_text'] ?>
                    !important;
            }

        <?php endif; ?>

        <?php if (!empty($settings['btn_ordernow_bg'])): ?>
            .btn-action.btn-order-now {
                background:
                    <?= $settings['btn_ordernow_bg'] ?>
                    !important;
                background-image: none !important;
            }

        <?php endif; ?>
        <?php if (!empty($settings['btn_ordernow_text'])): ?>
            .btn-action.btn-order-now {
                color:
                    <?= $settings['btn_ordernow_text'] ?>
                    !important;
            }

        <?php endif; ?>

        <?php if (!empty($settings['btn_cart_whatsapp_bg'])): ?>
            .cart-payment-btn-whatsapp {
                background:
                    <?= $settings['btn_cart_whatsapp_bg'] ?>
                    !important;
                background-image: none !important;
            }

        <?php endif; ?>
        <?php if (!empty($settings['btn_cart_whatsapp_text'])): ?>
            .cart-payment-btn-whatsapp {
                color:
                    <?= $settings['btn_cart_whatsapp_text'] ?>
                    !important;
            }

        <?php endif; ?>

        <?php if (!empty($settings['btn_cart_cod_bg'])): ?>
            .cart-payment-btn-cod {
                background:
                    <?= $settings['btn_cart_cod_bg'] ?>
                    !important;
                background-image: none !important;
            }

        <?php endif; ?>
        <?php if (!empty($settings['btn_cart_cod_text'])): ?>
            .cart-payment-btn-cod {
                color:
                    <?= $settings['btn_cart_cod_text'] ?>
                    !important;
            }

        <?php endif; ?>

        <?php if (!empty($settings['btn_cart_payhere_bg'])): ?>
            .cart-payment-btn-payhere {
                background:
                    <?= $settings['btn_cart_payhere_bg'] ?>
                    !important;
                background-image: none !important;
            }

        <?php endif; ?>
        <?php if (!empty($settings['btn_cart_payhere_text'])): ?>
            .cart-payment-btn-payhere {
                color:
                    <?= $settings['btn_cart_payhere_text'] ?>
                    !important;
            }

        <?php endif; ?>

        <?php if (!empty($settings['btn_cart_koko_bg'])): ?>
            .cart-payment-btn-koko {
                background:
                    <?= $settings['btn_cart_koko_bg'] ?>
                    !important;
                background-image: none !important;
            }

        <?php endif; ?>
        <?php if (!empty($settings['btn_cart_koko_text'])): ?>
            .cart-payment-btn-koko {
                color:
                    <?= $settings['btn_cart_koko_text'] ?>
                    !important;
            }

        <?php endif; ?>

        /* 2. Apply Filter */
        <?php if (!empty($settings['btn_apply_bg'])): ?>
            .btn-apply-filter {
                background-color:
                    <?= $settings['btn_apply_bg'] ?>
                    !important;
            }

        <?php endif; ?>
        <?php if (!empty($settings['btn_apply_text'])): ?>
            .btn-apply-filter {
                color:
                    <?= $settings['btn_apply_text'] ?>
                    !important;
            }

        <?php endif; ?>

        /* 3. Category/Nav */
        <?php if (!empty($settings['btn_category_bg'])): ?>
            .cat-btn {
                background-color:
                    <?= $settings['btn_category_bg'] ?>
                    !important;
            }

        <?php endif; ?>
        <?php if (!empty($settings['btn_category_text'])): ?>
            .cat-btn {
                color:
                    <?= $settings['btn_category_text'] ?>
                    !important;
            }

        <?php endif; ?>

        /* 4. Sale/Red */
        <?php if (!empty($settings['btn_sale_bg'])): ?>
            .btn-red {
                background-color:
                    <?= $settings['btn_sale_bg'] ?>
                    !important;
            }

        <?php endif; ?>
        <?php if (!empty($settings['btn_sale_text'])): ?>
            .btn-red {
                color:
                    <?= $settings['btn_sale_text'] ?>
                    !important;
            }

        <?php endif; ?>

        /* 5. Review Link */
        <?php if (!empty($settings['btn_review_bg'])): ?>
            .btn-review {
                background-color:
                    <?= $settings['btn_review_bg'] ?>
                    !important;
            }

        <?php endif; ?>
        <?php if (!empty($settings['btn_review_text'])): ?>
            .btn-review {
                color:
                    <?= $settings['btn_review_text'] ?>
                    !important;
            }

        <?php endif; ?>

        /*Size Guide */
        <?php if (!empty($settings['btn_sizeguide_bg'])): ?>
            button.btn-size-guide {
                background-color:
                    <?= $settings['btn_sizeguide_bg'] ?>
                    !important;
            }

        <?php endif; ?>
        <?php if (!empty($settings['btn_sizeguide_text'])): ?>
            button.btn-size-guide {
                color:
                    <?= $settings['btn_sizeguide_text'] ?>
                    !important;
            }

        <?php endif; ?>

        /* Floating Elements */
        <?php if (!empty($settings['floating_cart_bg'])): ?>
            .floating-cart {
                background-color:
                    <?= $settings['floating_cart_bg'] ?>
                    !important;
            }

        <?php endif; ?>
        <?php if (!empty($settings['floating_cart_text'])): ?>
            .floating-cart i {
                color:
                    <?= $settings['floating_cart_text'] ?>
                    !important;
            }

        <?php endif; ?>

        /* Navigation Styling Overrides */
        <?php if (!empty($settings['nav_mobile_bg'])): ?>
            .bottom-nav {
                background-color:
                    <?= $settings['nav_mobile_bg'] ?>
                    !important;
            }

        <?php endif; ?>

        <?php if (!empty($settings['nav_mobile_icon_color'])): ?>
            .nav-item,
            .nav-item i,
            .nav-icon-img {
                color:
                    <?= $settings['nav_mobile_icon_color'] ?>
                    !important;
            }

        <?php endif; ?>

        <?php if (!empty($settings['nav_mobile_active_color'])): ?>
            .nav-item.active,
            .nav-item.active i,
            .nav-item.active span {
                color:
                    <?= $settings['nav_mobile_active_color'] ?>
                    !important;
            }

        <?php endif; ?>

        <?php if (!empty($settings['nav_desktop_bg'])): ?>
            .desktop-header {
                background-color:
                    <?= $settings['nav_desktop_bg'] ?>
                    !important;
            }

        <?php endif; ?>

        <?php if (!empty($settings['nav_desktop_link_color'])): ?>
            .desktop-nav-links,
            .desktop-nav-links a {
                color:
                    <?= $settings['nav_desktop_link_color'] ?>
                    !important;
            }

        <?php endif; ?>

        /* Custom Search Bars */
        <?php if (!empty($settings['search_mobile_bg'])): ?>
            #searchTriggerBtn, #mobileSearchInput {
                background-color: <?= $settings['search_mobile_bg'] ?> !important;
            }
        <?php endif; ?>
        
        <?php if (!empty($settings['search_mobile_icon'])): ?>
            #searchTriggerBtn i, #mobileSearchInput + i, #mobileSearchInput {
                color: <?= $settings['search_mobile_icon'] ?> !important;
            }
            #mobileSearchInput::placeholder {
                color: <?= $settings['search_mobile_icon'] ?> !important;
                opacity: 0.7;
            }
        <?php endif; ?>

        <?php if (!empty($settings['search_desktop_bg'])): ?>
            .desktop-header .search-input {
                background-color: <?= $settings['search_desktop_bg'] ?> !important;
            }
        <?php endif; ?>

        <?php if (!empty($settings['search_desktop_icon'])): ?>
            .desktop-header .search-input, .desktop-header #desktopSearchIcon {
                color: <?= $settings['search_desktop_icon'] ?> !important;
            }
            .desktop-header .search-input::placeholder {
                color: <?= $settings['search_desktop_icon'] ?> !important;
                opacity: 0.7;
            }
        <?php endif; ?>

    </style>
</head>

<body>

    <!-- Mobile Header (Visible only on Mobile) -->
    <?php if (empty($hide_mobile_welcome)): ?>
        <div class="mobile-header d-lg-none" style="padding-bottom: 10px; width: 100%;">

            <!-- Top Row: Flex Container -->
            <div
                style="display: flex; align-items: center; justify-content: space-between; width: 100%; padding-top: 10px;">

                <!-- Left: Welcome Text -->
                <div class="welcome-text" style="flex: 1;">
                    <!-- Flex 1 allows it to take space but not push controls off -->
                    <h1 style="font-size: 22px; font-weight: 800; margin: 0; line-height: 1.2; color: #000;">Welcome!</h1>
                    <p style="font-size: 13px; color: #757575; margin: 0; line-height: 1.2;">
                        <?= !empty($settings['shop_name']) ? htmlspecialchars($settings['shop_name']) : 'Dark Lavender Clothing!' ?>
                    </p>
                </div>

                <!-- Right: Controls (FORCED RIGHT ALIGNMENT) -->
                <div id="headerControls"
                    style="display: flex; align-items: center; gap: 10px; margin-left: auto; flex-shrink: 0;">

                    <!-- Search Trigger Button -->
                    <div id="searchTriggerBtn" onclick="toggleMobileSearch()" style="
                    background: #ede7f6; /* Matching Screenshot lighter purple */
                    width: 40px; 
                    height: 40px; 
                    border-radius: 12px; 
                    display: flex; 
                    align-items: center; 
                    justify-content: center; 
                    cursor: pointer;
                    transition: all 0.2s;">
                        <i class="fas fa-search" style="color: #5e35b1; font-size: 18px;"></i> <!-- Darker purple icon -->
                    </div>

                    <!-- Shop Avatar/Logo -->
                    <?php
                    $logo = ImageHelper::settingsImageUrl(
                        $settings['shop_logo'] ?? '',
                        'https://via.placeholder.com/40'
                    );
                    ?>
                    <img <?= ImageHelper::attrs([
                        'src' => $logo,
                        'alt' => 'Shop Logo',
                        'loading' => 'eager',
                        'decoding' => 'async',
                        'fetchpriority' => 'high',
                        'style' => '
                    width: 40px; 
                    height: 40px; 
                    border-radius: 50%; 
                    object-fit: cover;
                    border: 1px solid #eee;'
                    ]) ?>>
                </div>
            </div>

            <!-- Mobile Search Bar (Popped Under) -->
            <div id="mobileSearchBar" class="search-bar mobile-search" style="
            display: none;
            margin-top: 15px; 
            width: 100%;
        ">
                <div style="position: relative;">
                    <input type="text" id="mobileSearchInput" placeholder="Search products........." class="search-input"
                        style="width: 100%; height: 45px; padding: 0 45px 0 20px; border-radius: 50px; border: none; background: #ede7f6; font-size: 14px; color: #333; outline: none;">

                    <!-- Icon inside input (Right) -->
                    <i class="fas fa-search" onclick="triggerMobileSearch()"
                        style="position: absolute; right: 20px; top: 50%; transform: translateY(-50%); color: #5e35b1; cursor: pointer; font-size: 16px;"></i>
                </div>
            </div>

            <script>
                function toggleMobileSearch() {
                    const searchBar = document.getElementById('mobileSearchBar');
                    const triggerBtn = document.getElementById('searchTriggerBtn');

                    if (searchBar.style.display === 'none') {
                        // Open
                        searchBar.style.display = 'block';
                        triggerBtn.style.display = 'none'; // Hide trigger
                        setTimeout(() => { document.getElementById('mobileSearchInput').focus(); }, 50);
                    } else {
                        hideSearch();
                    }
                }

                function hideSearch() {
                    const searchBar = document.getElementById('mobileSearchBar');
                    const triggerBtn = document.getElementById('searchTriggerBtn');
                    searchBar.style.display = 'none';
                    triggerBtn.style.display = 'flex'; // Show trigger
                }

                function triggerMobileSearch() {
                    const query = document.getElementById('mobileSearchInput').value;
                    if (query.trim() !== '') {
                        window.location.href = '<?= BASE_URL ?>shop/index?search=' + encodeURIComponent(query);
                    }
                }

                document.getElementById('mobileSearchInput').addEventListener('keypress', function (e) {
                    if (e.key === 'Enter') {
                        triggerMobileSearch();
                    }
                });

                // Global Click Listener for "Background Click"
                document.addEventListener('click', function (event) {
                    const searchBar = document.getElementById('mobileSearchBar');
                    const triggerBtn = document.getElementById('searchTriggerBtn');

                    // Only act if search is visible
                    if (searchBar.style.display === 'block') {
                        const isClickInsideSearch = searchBar.contains(event.target);
                        // Also check trigger to prevent immediate close when opening
                        const isClickInsideTrigger = triggerBtn.contains(event.target);

                        if (!isClickInsideSearch && !isClickInsideTrigger) {
                            hideSearch();
                        }
                    }
                });
            </script>
        </div>
    <?php endif; ?>

    <?php
    require_once 'models/Product.php';
    $headerProductModel = new Product();
    $hasSaleProducts = !empty($headerProductModel->getOnSale(1));
    $hasFreeShippingProducts = !empty($headerProductModel->getFreeShippingProducts(1));
    ?>

    <!-- Desktop Header (Visible only on Desktop) -->
    <header class="desktop-header display-desktop-only">
        <div class="header-inner">
            <div class="logo-area">
                <?php
                // Use same logic as mobile
                $logo = ImageHelper::settingsImageUrl(
                    $settings['shop_logo'] ?? '',
                    'https://via.placeholder.com/50'
                );
                ?>
                <div style="display:flex; align-items:center; gap:10px;">
                    <img <?= ImageHelper::attrs([
                        'src' => $logo,
                        'alt' => 'Logo',
                        'loading' => 'eager',
                        'decoding' => 'async',
                        'fetchpriority' => 'high',
                        'style' => 'width: 50px; height: 50px; border-radius: 50%; object-fit: cover;'
                    ]) ?>>
                    <div>
                        <h2 style="margin:0; font-size: 18px;">
                            <?= !empty($settings['shop_name']) ? htmlspecialchars($settings['shop_name']) : 'Dark Lavender Clothing!' ?>
                        </h2>
                    </div>
                </div>
            </div>

            <div class="search-bar">
                <input type="text" id="desktopSearchInput" placeholder="Search..." class="search-input">
                <i class="fas fa-search" id="desktopSearchIcon"
                    style="position: absolute; right: 15px; top: 12px; color: #aaa; cursor: pointer;"></i>
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        const searchInput = document.getElementById('desktopSearchInput');
                        const searchIcon = document.getElementById('desktopSearchIcon');

                        function performSearch() {
                            const query = searchInput.value;
                            if (query.trim() !== '') {
                                // Redirect to Shop Controller which handles customer items
                                window.location.href = '<?= BASE_URL ?>shop/index?search=' + encodeURIComponent(query);
                            }
                        }

                        if (searchInput) {
                            searchInput.addEventListener('keypress', function (e) {
                                if (e.key === 'Enter') {
                                    performSearch();
                                }
                            });
                        }

                        if (searchIcon) {
                            searchIcon.addEventListener('click', performSearch);
                        }

                        // Initialize Cart Badge
                        // updateCartBadge(); // Disabled: Using PHP Session Count now
                    });

                    // --- Global Cart Logic (LocalStorage) ---
                    // Used by Product Page, Listing, and Cart Page

                    function getCart() {
                        const cart = localStorage.getItem('shopCart');
                        return cart ? JSON.parse(cart) : [];
                    }

                    function saveCart(cart) {
                        localStorage.setItem('shopCart', JSON.stringify(cart));
                        updateCartBadge();
                    }

                    function addToCart(id, title, price, img) {
                        // Show Loader
                        if (typeof showGlobalLoader === 'function') showGlobalLoader();

                        // Prepare Data
                        const payload = {
                            id: id,
                            title: title,
                            price: price,
                            quantity: 1,
                            img: img || '',
                            variants: ''
                        };

                        // Send AJAX Request
                        fetch('<?= BASE_URL ?>cart/add', {
                            method: 'POST',
                            headers: csrfHeaders({ 'Content-Type': 'application/json' }),
                            body: JSON.stringify(payload)
                        })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    trackAnalyticsEvent('add_to_cart', {
                                        currency: window.APP_CURRENCY,
                                        value: Number(price || 0),
                                        items: [buildAnalyticsItem({
                                            id: id,
                                            title: title,
                                            price: price,
                                            quantity: 1
                                        })]
                                    }, 'AddToCart', {
                                        content_ids: [String(id)],
                                        content_name: title,
                                        content_type: 'product',
                                        value: Number(price || 0),
                                        currency: window.APP_CURRENCY,
                                        contents: [{
                                            id: String(id),
                                            quantity: 1,
                                            item_price: Number(price || 0)
                                        }]
                                    });

                                    if (typeof showCartToast === 'function') showCartToast();

                                    // Update Badge Counts
                                    const bubbleCount = document.querySelector('.floating-cart-count');
                                    const headerCount = document.querySelector('.cart-badge-count');

                                    if (data.count) {
                                        if (bubbleCount) bubbleCount.innerText = data.count;
                                        if (headerCount) {
                                            headerCount.innerText = data.count;
                                            headerCount.style.display = 'inline-block';
                                        }
                                        const floatingCart = document.querySelector('.floating-cart');
                                        if (floatingCart) floatingCart.style.display = 'flex';
                                    }
                                } else {
                                    const message = String(data.message || '');
                                    if (message.toLowerCase().includes('variation')) {
                                        window.location.href = '<?= BASE_URL ?>shop/product/' + encodeURIComponent(id);
                                        return;
                                    }

                                    alert(message || 'Failed to add to cart');
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                            })
                            .finally(() => {
                                //  Hide Loader Always
                                if (typeof hideGlobalLoader === 'function') hideGlobalLoader();
                            });
                    }


                    function updateCartBadge() {
                        const cart = getCart();
                        const count = cart.reduce((acc, item) => acc + (item.qty || 1), 0);
                        // Update Badges (Desktop & Mobile if exists)
                        const badges = document.querySelectorAll('.cart-badge, .fa-shopping-cart + span');
                        badges.forEach(b => {
                            b.innerText = count;
                            b.style.display = count > 0 ? 'inline-block' : 'none'; // Or keep visible
                        });
                    }
                </script>
                </script>
            </div>

            <div class="header-actions">
                <a href="<?= BASE_URL ?>shop/categories" class="cat-btn" style="text-decoration:none;"><i
                        class="fas fa-bars"></i> Categories</a>
                <a href="<?= BASE_URL ?>cart"
                    style="text-decoration: none; color: inherit; display: flex; align-items: center; position: relative;">
                    <i class="fas fa-shopping-cart" style="font-size: 20px;"></i>
                    <?php
                    $cartCount = isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'qty')) : 0;
                    ?>
                    <span class="cart-badge-count"
                        style="position: absolute; top: -5px; right: -10px; background: red; color: white; border-radius: 50%; padding: 2px 5px; font-size: 10px; display: <?= $cartCount > 0 ? 'inline-block' : 'none' ?>;">
                        <?= $cartCount ?>
                    </span>
                    <span style="font-size: 14px; margin-left: 5px;">Cart</span>
                </a>
                <?php if ($hasSaleProducts): ?>
                    <a href="<?= BASE_URL ?>shop/sales" class="btn-red" style="text-decoration:none;">Sale Items</a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Secondary Nav Links -->
        <div class="desktop-nav-links">
            <a href="<?= BASE_URL ?>">Home Page</a>
            <a href="<?= BASE_URL ?>shop">All Products</a>
            <a href="<?= BASE_URL ?>shop/new_arrivals">Recent Items</a>
            <a href="<?= BASE_URL ?>shop/featured">Featured Products</a>
            <a href="<?= BASE_URL ?>reviews" class="desktop-nav-reviews">Reviews</a>
            <?php if ($hasFreeShippingProducts): ?>
                <a href="<?= BASE_URL ?>shop/free_shipping" class="desktop-nav-free-shipping">Free Shipping</a>
            <?php endif; ?>
            <?php if ($hasSaleProducts): ?>
                <a href="<?= BASE_URL ?>shop/sales" class="desktop-nav-discounts">Discounts!</a>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>order/myOrders">My Orders</a>
        </div>
    </header>

    <div class="container main-wrapper">
