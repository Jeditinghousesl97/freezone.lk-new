<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin.css?v=<?= time() ?>">
    <style>
        /* Specific tweaks for dashboard */
        .welcome-section {
            margin-bottom: 30px;
        }

        .welcome-title {
            font-size: 28px;
            font-weight: 800;
            margin: 0;
        }

        .welcome-sub {
            color: #888;
                        margin: 5px 0 0 0;
        }
        
        /* Action Buttons (Ported from Products List) */
        .trash-icon {
            color: #ff3b30;
            border: 1px solid #ff3b30;
            border-radius: 5px;
            width: 30px;
            height: 30px;
            display: flex;
            justify-content: center;
            align-items: center;
            text-decoration: none;
            font-size: 16px;
        }

    </style>
</head>

<body>
 <!-- Global Loader Injection -->
    <?php include 'views/admin/partials/loader.php'; ?>
    <div class="container">
        <!-- Header -->
        <div class="page-header"
            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div class="welcome-section" style="margin-bottom: 0; display:flex; align-items:center; gap:15px;">
                <!-- Shop Logo Injection -->
                <?php if (!empty($settings['shop_logo'])): ?>
                    <img src="<?= htmlspecialchars($settings['shop_logo']) ?>" alt="Shop Logo"
                        style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover; border: 1px solid #ddd;">
                <?php endif; ?>

                <div>
                    <h1 class="welcome-title">
                        <?= !empty($settings['shop_name']) ? htmlspecialchars($settings['shop_name']) : 'Welcome back!' ?>
                    </h1>
                    <p class="welcome-sub"><?= $_SESSION['username'] ?? 'Shop Owner' ?></p>
                </div>
            </div>

            <!-- Header Right Side -->
            <div style="display: flex; gap: 10px; align-items: center;">
                <!-- Logout Button -->
                <a href="<?= BASE_URL ?>auth/logout"
                    style="background-color: #ff3b30; color: white; padding: 8px 12px; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: bold;">Logout</a>
            </div>
        </div>

        <!-- Stats Grid -->
        <style>
            .stat-card-link {
                text-decoration: none;
                color: inherit;
                transition: transform 0.2s;
                display: block;
            }

            .stat-card-link:hover {
                transform: translateY(-5px);
            }
        </style>
        <div class="stats-grid">
            <a href="<?= BASE_URL ?>category/index" class="stat-card stat-card-link">
                <h2 class="stat-number"><?= $stats['categories'] ?? 0 ?></h2>
                <p class="stat-label">Categories</p>
            </a>
            <a href="<?= BASE_URL ?>product/index" class="stat-card stat-card-link">
                <h2 class="stat-number"><?= $stats['products'] ?? 0 ?></h2>
                <p class="stat-label">Products</p>
            </a>
            <a href="<?= BASE_URL ?>sizeGuide/index" class="stat-card stat-card-link">
                <h2 class="stat-number"><?= $stats['size_guides'] ?? 0 ?></h2>
                <p class="stat-label">Size Guides</p>
            </a>
            <a href="<?= BASE_URL ?>feedback/index" class="stat-card stat-card-link">
                <h2 class="stat-number"><?= $stats['feedbacks'] ?? 0 ?></h2>
                <p class="stat-label">Feedbacks</p>
            </a>
            <a href="<?= BASE_URL ?>order/manage" class="stat-card stat-card-link">
                <h2 class="stat-number"><?= $stats['orders'] ?? 0 ?></h2>
                <p class="stat-label">Orders</p>
            </a>
        </div>

        <!-- Products Section -->
        <h3 class="section-title">Products in your Store</h3>

        <div class="product-list-container">
            <!-- Header Row  -->
            <div
                style="background:#eee; padding: 10px; border-radius: 6px; font-size:12px; color:#666; margin-bottom:10px;">
                Products
            </div>

            <?php if (empty($latest_products)): ?>
                <p style="text-align:center; padding:20px; color:#999;">No products yet.</p>
            <?php else: ?>
                                <?php foreach ($latest_products as $product): ?>
                    <div class="product-item">
                        <div style="display:flex; flex-direction:column; gap:5px; margin-right:15px;">
                            <a href="<?= BASE_URL ?>product/edit/<?= $product['id'] ?>" class="trash-icon"
                                style="color:#00c4b4; border-color:#00c4b4;">
                                ✏️
                            </a>
                            <a href="<?= BASE_URL ?>product/delete/<?= $product['id'] ?>" class="trash-icon"
                                onclick="if(confirm('Delete this item?')){ showGlobalLoader(); return true; } else { return false; }">
                                🗑
                            </a>
                        </div>
                        <img src="<?= BASE_URL ?>assets/uploads/<?= $product['main_image'] ?? 'default.png' ?>"
                            class="product-thumb" alt="Img">
                        <div style="flex: 1; display: flex; justify-content: space-between; align-items: center;">
                            <div class="product-info" style="flex: unset;">
                                <h4 class="product-name"><?= htmlspecialchars($product['title']) ?></h4>
                                <p class="product-category"><?= htmlspecialchars($product['category_name'] ?? 'Uncategorized') ?></p>
                            </div>
                            
                            <!-- Visibility Toggle -->
                            <a href="<?= BASE_URL ?>product/toggleActive/<?= $product['id'] ?>" 
                               class="toggle-btn <?= $product['is_active'] ? 'active' : '' ?>" 
                               title="Toggle Visibility" 
                               onclick="showGlobalLoader();">
                                <div class="toggle-circle"></div>
                            </a>
                        </div>

                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bottom Nav -->
    <?php $current_page = 'dashboard';
    include 'views/layouts/bottom_nav.php'; ?>

</body>

</html>
