<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin.css?v=<?= time() ?>">
    <style>
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .add-btn-blue {
            background-color: #007aff;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            font-size: 14px;
        }

        /* Search Bar */
        .search-container {
            position: relative;
            margin-bottom: 25px;
        }

        .search-input {
            width: 100%;
            padding: 15px 50px 15px 20px;
            background: #f5f5f5;
            border: none;
            border-radius: 30px;
            font-size: 14px;
            box-sizing: border-box;
            color: #666;
        }

        .search-icon-circle {
            position: absolute;
            right: 5px;
            top: 5px;
            width: 40px;
            height: 40px;
            background: #fdd835;
            /* Yellow */
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .search-icon-img {
            width: 18px;
            height: 18px;
            opacity: 0.8;
        }

        /* List Header */
        .list-header {
            background: #eee;
            padding: 10px 15px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            color: #666;
            font-size: 13px;
            font-weight: bold;
        }

        .delete-all-btn {
            background: #ffcccc;
            color: #ff3b30;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 11px;
            display: flex;
            align-items: center;
            gap: 5px;
            text-decoration: none;
        }

        /* Product Item */
        .prod-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding-bottom: 20px;
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
        }

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

        .prod-thumb {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            background: #eee;
        }

        .prod-info {
            flex: 1;
        }

        .prod-title {
            font-weight: bold;
            color: #222;
            font-size: 15px;
            margin-bottom: 3px;
        }

        .prod-cat {
            font-size: 12px;
            color: #888;
        }

        @media (min-width: 992px) {
            .page-header {
                margin-bottom: 28px;
            }

            .add-btn-blue {
                padding: 12px 20px;
                border-radius: 999px;
                box-shadow: 0 10px 22px rgba(0, 122, 255, 0.18);
            }

            .search-container {
                max-width: 560px;
            }

            .list-header {
                padding: 14px 18px;
                border-radius: 16px;
                font-size: 14px;
            }

            .product-list {
                background: #fff;
                border-radius: 24px;
                padding: 18px 22px;
                box-shadow: 0 16px 36px rgba(17, 24, 39, 0.06);
                border: 1px solid rgba(17, 24, 39, 0.05);
            }

            .prod-item {
                gap: 18px;
                padding: 14px 0;
                margin-bottom: 0;
            }
        }
    </style>
</head>

<body>
    <?php require_once ROOT_PATH . 'helpers/ImageHelper.php'; ?>
 <!-- Global Loader Injection -->
    <?php include 'views/admin/partials/loader.php'; ?>
    <div class="container" style="padding-bottom: 80px;">


        <div class="page-header">
            <div>
                <h2 style="margin:0;">All Products</h2>
                <p style="margin:0; font-size:11px; color:#888;">Dark Lavender Clothing!</p>
            </div>
            <div>
                <!-- Shop Logo Placeholder -->
                <!-- <div style="width:30px; height:30px; background:#ddd; border-radius:50%; display:inline-block;"></div> -->
                <a href="<?= BASE_URL ?>product/add" class="add-btn-blue">Add New</a>
            </div>
        </div>

        <!-- Search -->
        <form class="search-container" action="<?= BASE_URL ?>product/index" method="GET">
            <input type="text" name="search" class="search-input" placeholder="Type here to search..."
                value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            <div class="search-icon-circle" onclick="this.parentElement.submit()" style="cursor:pointer;">
                <img src="<?= BASE_URL ?>assets/icons/search.png" class="search-icon-img" alt="S">
            </div>
        </form>

        <!-- List Header -->
        <div class="list-header">
            <span>Products</span>
            <?php if (!empty($products)): ?>
                <a href="<?= BASE_URL ?>product/delete_all" class="delete-all-btn"
                    onclick="if(confirm('Delete ALL products? This cannot be undone!')){ showGlobalLoader(); return true; } else { return false; }">
                    🗑 Delete All
                </a>
            <?php endif; ?>
        </div>

        <!-- Product List -->
        <div class="product-list">
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $prod): ?>
                    <div class="prod-item">
                        <div style="display:flex; flex-direction:column; gap:5px; margin-right:15px;">
                            <a href="<?= BASE_URL ?>product/edit/<?= $prod['id'] ?>" class="trash-icon"
                                onclick="if(confirm('Edit this item?')){ showGlobalLoader(); return true; } else { return false; }" style="color:#00c4b4; border-color:#00c4b4;">
                                ✏️
                            </a>
                            <a href="<?= BASE_URL ?>product/delete/<?= $prod['id'] ?>" class="trash-icon"
                                onclick="if(confirm('Delete this item?')){ showGlobalLoader(); return true; } else { return false; }">
                                🗑
                            </a>
                        </div>

                        <?php
                        $imgSrc = ImageHelper::uploadUrl($prod['main_image'] ?? '', BASE_URL . "assets/icons/products.png");
                        ?>
                        <?= ImageHelper::renderResponsivePicture(
                            $prod['main_image'] ?? '',
                            $imgSrc,
                            [
                                'class' => 'prod-thumb',
                                'alt' => $prod['title'] ?? 'Product',
                                'loading' => 'lazy',
                                'decoding' => 'async',
                                'fetchpriority' => 'low'
                            ],
                            'admin_thumb'
                        ) ?>

                        <div style="flex: 1; display: flex; justify-content: space-between; align-items: center;">
                            <div class="prod-info" style="flex: unset;">
                                <div class="prod-title"><?= htmlspecialchars($prod['title']) ?></div>
                                <div class="prod-cat"><?= htmlspecialchars($prod['category_name'] ?? 'Uncategorized') ?></div>
                            </div>
                            
                            <!-- Visibility Toggle -->
                            <a href="<?= BASE_URL ?>product/toggleActive/<?= $prod['id'] ?>" 
                               class="toggle-btn <?= $prod['is_active'] ? 'active' : '' ?>" 
                               title="Toggle Visibility" 
                               onclick="showGlobalLoader();">
                                <div class="toggle-circle"></div>
                            </a>
                        </div>

                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="text-align:center; color:#999; margin-top:30px;">
                    No products found.<br>
                    <a href="<?= BASE_URL ?>product/add" style="color:#007aff;">Add your first product</a>
                </p>
            <?php endif; ?>
        </div>

    </div>

    <!-- Bottom Navigation -->
    <?php $current_page = 'products';
    include 'views/layouts/bottom_nav.php'; ?>

</body>

</html>
