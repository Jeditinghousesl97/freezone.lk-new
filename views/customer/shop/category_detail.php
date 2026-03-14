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
            <div style="font-size: 11px; color: #888; margin-bottom: 15px;">
                Home >
                <?= htmlspecialchars($category['name']) ?>
            </div>

            <!-- Title Row -->
            <div style="display: flex; align-items: center; justify-content: space-between;">

                <!-- Left: Back Btn + Title -->
                <div style="display: flex; align-items: center; gap: 15px;">
                    <!-- Back Button (Black Circle) -->
                    <a href="<?= BASE_URL ?>shop/categories" style="
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
                            <?= htmlspecialchars($category['name']) ?> Collection
                        </h1>
                        <p style="font-size: 11px; color: #666; margin: 0;">Product Category</p>
                    </div>
                </div>

                <!-- Right: Shop Avatar (No Search Icon in this view) -->
                <div style="display: flex; align-items: center;">
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
        </div>

        <!-- Sub Categories (Horizontal Scroll) -->
        <?php if (!empty($subCategories)): ?>
            <div class="section-header" style="padding: 0 20px;">
                <h2 class="section-title">Sub Categories</h2>
            </div>

            <div class="categories-scroll" style="margin-bottom: 30px;">
                <?php foreach ($subCategories as $sub): ?>
                    <a href="<?= BASE_URL ?>shop?cat=<?= $sub['id'] ?>" class="cat-item" style="text-decoration: none;">
                        <?php
                        $subImg = ImageHelper::uploadUrl(
                            $sub['image'] ?? '',
                            'https://via.placeholder.com/80?text=' . urlencode($sub['name'])
                        );
                        ?>
                        <img <?= ImageHelper::attrs([
                            'src' => $subImg,
                            'class' => 'cat-img',
                            'alt' => $sub['name'] ?? 'Category',
                            'loading' => 'lazy',
                            'decoding' => 'async',
                            'fetchpriority' => 'low'
                        ]) ?>>
                        <div class="cat-name">
                            <?= htmlspecialchars($sub['name']) ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Collection Title Over Grid -->
        <div class="section-header" style="padding: 0 20px;">
            <!-- Using name 'Denim Collection' style format -->
            <h2 class="section-title">
                <?= htmlspecialchars($category['name']) ?> Collection
            </h2>
        </div>

        <!-- Product Grid -->
        <div class="product-grid"
            style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; padding: 0 0 40px 0;">
            <?php if (empty($products)): ?>
                <div style="grid-column: 1 / -1; padding: 40px; text-align: center; color: #777;">
                    <p>No products found in this collection.</p>
                </div>
            <?php else: ?>
                <?php foreach ($products as $prod): ?>
                    <?php include 'views/customer/partials/product_card.php'; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </main>

</div>

<?php require_once 'views/layouts/customer_footer.php'; ?>
