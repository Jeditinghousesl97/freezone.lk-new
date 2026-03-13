<?php
// Hide default mobile header to use our custom one
$hide_mobile_welcome = true;
require_once 'views/layouts/customer_header.php';
?>

<div class="home-layout">

    <!-- Sidebar (Desktop) -->
    <?php include 'views/customer/partials/sidebar.php'; ?>

    <main class="main-content">

        <!-- Custom Header for Mobile Discount Page -->
        <div class="mobile-header-custom d-lg-none" style="padding: 20px 20px 0 20px; margin-bottom: 30px;">
            <!-- Breadcrumb -->
            <div style="font-size: 11px; color: #888; margin-bottom: 15px;">Home > Discount</div>
            
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <!-- Left: Title Group -->
                <div style="display: flex; align-items: center; gap: 15px;">

                    <!-- Back Button -->
                    <a href="javascript:history.back()" style="
                        width: 35px; 
                        height: 35px; 
                        background: #000; 
                        border-radius: 50%; 
                        display: flex; 
                        align-items: center; 
                        justify-content: center; 
                        color: white; 
                        text-decoration: none;
                    ">
                        <i class="fas fa-chevron-left" style="font-size: 14px;"></i>
                    </a>

                    <div>
                        <h1 style="font-size: 24px; font-weight: 800; line-height: 1.1; margin: 0; color: #000;">
                            Discounts
                        </h1>
                        <p style="margin: 0; font-size: 12px; color: #666;">Limited Time Offers</p>
                    </div>
                </div>

                <!-- Right: Actions -->
                <div style="display: flex; align-items: center; gap: 10px;">
                    <!-- Search Trigger -->
                    <div id="searchTriggerBtn" onclick="toggleMobileSearch()" style="
                        background: #ede7f6; 
                        width: 40px; 
                        height: 40px; 
                        border-radius: 12px; 
                        display: flex; 
                        align-items: center; 
                        justify-content: center; 
                        cursor: pointer;
                    ">
                        <i class="fas fa-search" style="color: #5e35b1; font-size: 18px;"></i>
                    </div>

                    <!-- Shop Avatar -->
                    <?php
                    $logoUrl = $settings['shop_logo'] ?? '';
                    $logoUrl = str_replace('/Ecom-CMS/', BASE_URL, $logoUrl);
                    $physicalPath = $_SERVER['DOCUMENT_ROOT'] . $logoUrl;
                    $logo = (!empty($logoUrl) && file_exists($physicalPath))
                        ? $logoUrl
                        : 'https://via.placeholder.com/40';
                    ?>
                    <img src="<?= $logo ?>" alt="Shop" style="
                        width: 40px; 
                        height: 40px; 
                        border-radius: 50%; 
                        object-fit: cover;
                        border: 1px solid #eee;
                    ">
                </div>
            </div>

            <!-- Mobile Search Bar -->
            <div id="mobileSearchBar" class="search-bar mobile-search" style="
                display: none;
                margin-top: 15px; 
                width: 100%;
            ">
                <div style="position: relative;">
                    <input type="text" id="mobileSearchInput" placeholder="Search products........."
                        class="search-input"
                        style="width: 100%; height: 45px; padding: 0 45px 0 20px; border-radius: 50px; border: none; background: #ede7f6; font-size: 14px; color: #333; outline: none;">

                    <i class="fas fa-search" onclick="triggerMobileSearch()"
                        style="position: absolute; right: 20px; top: 50%; transform: translateY(-50%); color: #5e35b1; cursor: pointer; font-size: 16px;"></i>
                </div>
            </div>

            <script>
                function toggleMobileSearch() {
                    const searchBar = document.getElementById('mobileSearchBar');
                    const triggerBtn = document.getElementById('searchTriggerBtn');

                    if (searchBar.style.display === 'none') {
                        searchBar.style.display = 'block';
                        triggerBtn.style.display = 'none';
                        setTimeout(() => { document.getElementById('mobileSearchInput').focus(); }, 50);
                    } else {
                        hideSearch();
                    }
                }

                function hideSearch() {
                    const searchBar = document.getElementById('mobileSearchBar');
                    const triggerBtn = document.getElementById('searchTriggerBtn');
                    searchBar.style.display = 'none';
                    triggerBtn.style.display = 'flex';
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

                // Close on outside click
                document.addEventListener('click', function (event) {
                    const searchBar = document.getElementById('mobileSearchBar');
                    const triggerBtn = document.getElementById('searchTriggerBtn');

                    if (searchBar && searchBar.style.display === 'block') {
                        const isClickInsideSearch = searchBar.contains(event.target);
                        const isClickInsideTrigger = triggerBtn.contains(event.target);

                        if (!isClickInsideSearch && !isClickInsideTrigger) {
                            hideSearch();
                        }
                    }
                });
            </script>
        </div>

        <!-- Section Title (Desktop Only) -->
        <div class="section-header d-none d-lg-flex">
            <h2 class="section-title">Discounts & Offers</h2>
        </div>

        <!-- Product Grid -->
        <div id="product-grid-container" class="shop-grid discount-grid-custom"
            style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 20px;">

            <?php if (empty($products)): ?>
                <div style="grid-column: 1 / -1; padding: 40px; text-align: center; color: #777;">
                    <h3>No discounts at the moment.</h3>
                    <p>Check back later or browse our latest products.</p>
                    <a href="<?= BASE_URL ?>" class="btn-red"
                        style="display:inline-block; margin-top:20px; text-decoration:none;">Go Home</a>
                </div>
            <?php else: ?>
                <?php foreach ($products as $prod): ?>
                    <?php include 'views/customer/partials/product_card.php'; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Related Products Section -->
        <?php if (!empty($relatedProducts)): ?>
            <div style="margin-top: 50px; border-top: 1px solid #eee; padding-top: 30px; margin-bottom: 20px;">
                <div class="section-header" style="display: flex; align-items: center; justify-content: space-between;">
                    <h2 class="section-title" style="margin: 0;">Related Products</h2>
                    <span class="d-lg-none" style="
                        background: linear-gradient(90deg, #00d2ff 0%, #3a7bd5 100%); 
                        color: white; 
                        font-size: 10px; 
                        padding: 5px 10px; 
                        border-radius: 20px;
                        font-weight: 600;
                     ">You may also like...</span>
                </div>
                <div class="products-scroll"
                    style="display:flex; overflow-x:auto; gap:15px; padding-bottom:10px; margin-top: 15px;">
                    <?php foreach ($relatedProducts as $prod): ?>
                        <div style="min-width: 160px; max-width: 160px;"> <!-- Fixed Width for Scroll Item -->
                            <?php include 'views/customer/partials/product_card.php'; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <style>
            /* Discount Page Specific Overrides (Mobile Only mostly) */
            @media (max-width: 1023px) {
                #product-grid-container {
                    grid-template-columns: repeat(2, 1fr) !important;
                    gap: 15px !important;
                }

                /* Red Pill Price Style Override for this page */
                .discount-grid-custom .product-card .price {
                    display: inline-flex;
                    align-items: center;
                    gap: 5px;
                    flex-wrap: wrap;
                }

                .discount-grid-custom .product-card .price del {
                    color: #999;
                    font-size: 12px;
                }

                .discount-grid-custom .product-card .price strong {
                    background: #FF3B30;
                    color: white !important;
                    padding: 2px 8px;
                    border-radius: 50px;
                    font-size: 12px;
                }

                /* Adjust Product Card Padding/Title for cleaner Mobile View */
                .discount-grid-custom .product-card {
                    border: none !important;
                }
            }
        </style>

    </main>
</div>

<?php require_once 'views/layouts/customer_footer.php'; ?>