<?php
// Suppress default Welcome Header
$hide_mobile_welcome = true;
require_once ROOT_PATH . 'helpers/ImageHelper.php';
require_once 'views/layouts/customer_header.php';
?>

<div class="home-layout">

    <!-- SIDEBAR -->
    <?php include 'views/customer/partials/sidebar.php'; ?>

    <main class="main-content">

        <!-- CUSTOM HEADER -->
        <div class="mobile-header-custom d-lg-none" style="padding: 20px 20px 0 20px; margin-bottom: 20px;">

            <!-- Breadcrumb -->
            <div style="font-size: 11px; color: #888; margin-bottom: 15px;">Home > Categories</div>

            <!-- Title Row with Actions -->
            <div style="display: flex; align-items: center; justify-content: space-between;">

                <!-- Left: Back Btn + Title -->
                <div style="display: flex; align-items: center; gap: 15px;">
                    <!-- Back Button (Black Circle with Chevron) -->
                    <a href="<?= BASE_URL ?>" style="
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
                        <h1 style="font-size: 24px; font-weight: 800; line-height: 1.1; margin: 0; color: #000;">
                            Categories</h1>
                        <p style="font-size: 13px; color: #666; margin: 0;">Our Product Range</p>
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

        <!-- Categories Grid -->
        <div class="product-grid" style="display: grid; gap: 15px; padding: 0 0 40px 0;">
            <?php foreach ($categories as $cat): ?>
                <a href="<?= BASE_URL ?>shop/category/<?= $cat['id'] ?>" class="cat-grid-item"
                    style="display: block; text-align: center; text-decoration: none;">
                    <?php
                    $img = ImageHelper::uploadUrl(
                        $cat['image'] ?? '',
                        'https://via.placeholder.com/150?text=' . urlencode($cat['name'])
                    );
                    ?>
                    <div
                        style="border-radius: 20px; overflow: hidden; aspect-ratio: 1/1; margin-bottom: 10px; background: #f0f0f0;">
                        <?= ImageHelper::renderResponsivePicture(
                            $cat['image'] ?? '',
                            $img,
                            [
                                'alt' => $cat['name'] ?? 'Category',
                                'loading' => 'lazy',
                                'decoding' => 'async',
                                'fetchpriority' => 'low',
                                'style' => 'width: 100%; height: 100%; object-fit: cover;'
                            ],
                            'category_card'
                        ) ?>
                    </div>
                    <div style="font-weight: 700; font-size: 14px; text-align: left; color: #000;">
                        <?= htmlspecialchars($cat['name']) ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

    </main>

</div>

<?php require_once 'views/layouts/customer_footer.php'; ?>
