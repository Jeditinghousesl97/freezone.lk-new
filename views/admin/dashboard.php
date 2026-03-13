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

        .dash-card {
            background: #fff;
            border-radius: 18px;
            padding: 18px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.04);
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

        <?php
        $chartRows = $chart_rows ?? [];
        $maxRevenue = 0;
        $maxOrders = 0;
        foreach ($chartRows as $chartRow) {
            $maxRevenue = max($maxRevenue, (float) ($chartRow['gross_total'] ?? 0));
            $maxOrders = max($maxOrders, (int) ($chartRow['orders_count'] ?? 0));
        }
        ?>

        <div class="dash-card" style="margin-bottom:18px;">
            <div style="display:flex; justify-content:space-between; gap:12px; flex-wrap:wrap; align-items:flex-start; margin-bottom:14px;">
                <div>
                    <h3 style="margin:0;">Sales Snapshot</h3>
                    <p style="margin:4px 0 0; font-size:12px; color:#888;">Last 7 days gross sales and order count.</p>
                </div>
                <a href="<?= BASE_URL ?>order/reports" style="text-decoration:none; background:#111; color:#fff; padding:10px 14px; border-radius:999px; font-size:13px; font-weight:700;">Accounting & Reporting</a>
            </div>

            <?php if (empty($chartRows)): ?>
                <div style="padding:14px; border-radius:14px; background:#fafafa; color:#777;">No order data available yet.</div>
            <?php else: ?>
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(150px, 1fr)); gap:12px; margin-bottom:16px;">
                    <div style="background:#eef5ff; border-radius:16px; padding:14px;">
                        <div style="font-size:11px; color:#2463d0; margin-bottom:6px;">Gross Revenue</div>
                        <div style="font-size:20px; font-weight:800; color:#111;"><?= htmlspecialchars($settings['currency_symbol'] ?? 'LKR') ?> <?= number_format((float) ($finance['gross_total'] ?? 0), 2) ?></div>
                    </div>
                    <div style="background:#e8fff0; border-radius:16px; padding:14px;">
                        <div style="font-size:11px; color:#1a9b57; margin-bottom:6px;">Paid Revenue</div>
                        <div style="font-size:20px; font-weight:800; color:#111;"><?= htmlspecialchars($settings['currency_symbol'] ?? 'LKR') ?> <?= number_format((float) ($finance['paid_total'] ?? 0), 2) ?></div>
                    </div>
                    <div style="background:#fff8ee; border-radius:16px; padding:14px;">
                        <div style="font-size:11px; color:#9b5d00; margin-bottom:6px;">COD Outstanding</div>
                        <div style="font-size:20px; font-weight:800; color:#111;"><?= htmlspecialchars($settings['currency_symbol'] ?? 'LKR') ?> <?= number_format((float) ($finance['cod_outstanding_total'] ?? 0), 2) ?></div>
                    </div>
                </div>

                <div style="display:grid; gap:12px;">
                    <?php foreach ($chartRows as $row): ?>
                        <?php
                        $revenueWidth = $maxRevenue > 0 ? max(6, ((float) ($row['gross_total'] ?? 0) / $maxRevenue) * 100) : 0;
                        $orderWidth = $maxOrders > 0 ? max(6, ((int) ($row['orders_count'] ?? 0) / $maxOrders) * 100) : 0;
                        ?>
                        <div style="display:grid; gap:6px;">
                            <div style="display:flex; justify-content:space-between; gap:10px; flex-wrap:wrap; font-size:12px;">
                                <strong style="color:#111;"><?= htmlspecialchars(date('M d', strtotime((string) $row['report_date']))) ?></strong>
                                <span style="color:#666;"><?= htmlspecialchars($settings['currency_symbol'] ?? 'LKR') ?> <?= number_format((float) ($row['gross_total'] ?? 0), 2) ?> | <?= (int) ($row['orders_count'] ?? 0) ?> orders</span>
                            </div>
                            <div style="display:grid; gap:6px;">
                                <div style="height:10px; background:#f1f1f1; border-radius:999px; overflow:hidden;">
                                    <div style="width:<?= $revenueWidth ?>%; height:100%; background:linear-gradient(90deg, #111, #007aff); border-radius:999px;"></div>
                                </div>
                                <div style="height:8px; background:#f7f2e8; border-radius:999px; overflow:hidden;">
                                    <div style="width:<?= $orderWidth ?>%; height:100%; background:linear-gradient(90deg, #ffb300, #ff7a00); border-radius:999px;"></div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
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
            <a href="<?= BASE_URL ?>order/reports" class="stat-card stat-card-link">
                <h2 class="stat-number"><?= (int) ($stats['orders'] ?? 0) ?></h2>
                <p class="stat-label">Accounting</p>
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
