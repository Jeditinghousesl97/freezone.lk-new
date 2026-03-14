<?php
// Check if we are in "Sub-Category View" (Single Category Selected)
$isSubCategoryView = !empty($currentCategory);

if ($isSubCategoryView) {
    // Suppress default header ONLY for sub-category view
    $hide_mobile_welcome = true;
}

require_once ROOT_PATH . 'helpers/ImageHelper.php';
require_once 'views/layouts/customer_header.php';
?>

<div class="home-layout">

    <!-- SIDEBAR (Reused) -->
    <?php include 'views/customer/partials/sidebar.php'; ?>

    <!-- MAIN CONTENT AREA -->
    <main class="main-content">

        <!-- CUSTOM HEADER  -->
        <?php if ($isSubCategoryView): ?>
            <div class="mobile-header-custom d-lg-none" style="padding: 20px 20px 0 20px; margin-bottom: 20px;">

                <!-- Breadcrumb: Home > Parent > Current -->
                <div style="font-size: 11px; color: #888; margin-bottom: 15px;">
                    Home >
                    <?php if (!empty($currentCategory['parent_name'])): ?>
                        <?= htmlspecialchars($currentCategory['parent_name']) ?> >
                    <?php endif; ?>
                    <?= htmlspecialchars($currentCategory['name']) ?>
                </div>

                <!-- Title Row -->
                <div style="display: flex; align-items: center; justify-content: space-between;">

                    <!-- Left: Back Btn + Title -->
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <!-- Back Button (Black Circle) -->
                        <a href="<?= BASE_URL ?>shop/category/<?= $currentCategory['parent_id'] ?? '' ?>" style="
                        width: 35px; 
                        height: 35px; 
                        background: #000; 
                        border-radius: 50%; 
                        display: flex; 
                        align-items: center; 
                        justify-content: center; 
                        color: white;
                        text-decoration: none;
                        flex-shrink: 0;
                    ">
                            <i class="fas fa-chevron-left" style="font-size: 14px;"></i>
                        </a>

                        <!-- Title Block -->
                        <div>
                            <h1 style="font-size: 20px; font-weight: 800; line-height: 1.1; margin: 0; color: #000;">
                                <?= htmlspecialchars($currentCategory['name']) ?>
                            </h1>
                            <p style="font-size: 11px; color: #666; margin: 0;">
                                <?= !empty($currentCategory['parent_name']) ? htmlspecialchars($currentCategory['parent_name']) . " Collection" : "Product Category" ?>
                            </p>
                        </div>
                    </div>

                    <!-- Right: Search + Avatar -->
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <!-- Search Trigger (Light Purple Square) -->
                        <div style="
                        background: #ede7f6; 
                        width: 40px; 
                        height: 40px; 
                        border-radius: 12px; 
                        display: flex; 
                        align-items: center; 
                        justify-content: center; 
                    ">
                            <i class="fas fa-search" style="color: #5e35b1; font-size: 18px;"></i>
                        </div>

                        <!-- Shop Avatar -->
                        <?php
                        $logoUrl = ImageHelper::settingsImageUrl($settings['shop_logo'] ?? '', 'https://via.placeholder.com/40');
                        $logoFile = basename((string) parse_url($logoUrl, PHP_URL_PATH));
                        ?>
                        <?= ImageHelper::renderResponsivePicture(
                            $logoFile,
                            $logoUrl,
                            [
                                'alt' => 'Shop',
                                'loading' => 'eager',
                                'decoding' => 'async',
                                'fetchpriority' => 'high',
                                'style' => 'width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 1px solid #eee;'
                            ],
                            'logo'
                        ) ?>
                    </div>

                </div>
            </div>

            <!-- Collection Title (e.g. "Wide Leg Collection") -->
            <div class="section-header" style="padding: 0 20px;">
                <h2 class="section-title"><?= htmlspecialchars($currentCategory['name']) ?> Collection</h2>
            </div>

        <?php elseif (!empty($isSpecialPage)): ?>
            <!-- Special Pages (Sales, Featured, Recent) -->
            <div class="section-header">
                <!-- Use $title passed from Controller (Discounts!, Featured Products, Recent Items) -->
                <h2 class="section-title" style="<?= ($title === 'Discounts!') ? 'color: red;' : '' ?>">
                    <?= htmlspecialchars($title) ?>
                </h2>
            </div>

        <?php elseif (!empty($search_query)): ?>
            <!-- Search Results Header -->
            <div class="section-header">
                <h2 class="section-title">Searched Products</h2>
            </div>
        <?php else: ?>
            <!-- Default Shop Header (If visited directly without cat) -->
            <div class="section-header">
                <h2 class="section-title"><?= htmlspecialchars($title ?? 'All Products') ?></h2>
            </div>
        <?php endif; ?>

        <div id="product-grid-container" class="shop-grid"
            style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 20px;">
            <?php if (empty($products)): ?>
                <div style="grid-column: 1 / -1; padding: 40px; text-align: center; color: #777;">
                    <h3>No products found.</h3>
                    <p>Try searching for something else or browse our categories.</p>
                    <a href="<?= BASE_URL ?>" class="btn-red"
                        style="display:inline-block; margin-top:20px; text-decoration:none;">Go Home</a>
                </div>
            <?php else: ?>
                <?php foreach ($products as $prod): ?>
                    <?php include 'views/customer/partials/product_card.php'; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <style>
            @media (max-width: 768px) {
                #product-grid-container {
                    grid-template-columns: repeat(2, 1fr) !important;
                    gap: 15px !important;
                }

                .mobile-header-custom {
                    display: block !important;
                }
            }
        </style>

    </main>

</div>

<?php require_once 'views/layouts/customer_footer.php'; ?>
