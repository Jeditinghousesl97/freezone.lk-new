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

        $chartWidth = 720;
        $chartHeight = 240;
        $chartPaddingX = 24;
        $chartPaddingTop = 18;
        $chartPaddingBottom = 30;
        $usableWidth = max(1, $chartWidth - ($chartPaddingX * 2));
        $usableHeight = max(1, $chartHeight - $chartPaddingTop - $chartPaddingBottom);
        $pointCount = count($chartRows);
        $revenuePoints = [];
        $orderPoints = [];
        $xLabels = [];

        foreach ($chartRows as $index => $row) {
            $x = $chartPaddingX + ($pointCount > 1 ? ($usableWidth / ($pointCount - 1)) * $index : ($usableWidth / 2));
            $revenueValue = (float) ($row['gross_total'] ?? 0);
            $orderValue = (float) ($row['orders_count'] ?? 0);
            $revenueY = $chartPaddingTop + ($usableHeight - (($maxRevenue > 0 ? $revenueValue / $maxRevenue : 0) * $usableHeight));
            $orderY = $chartPaddingTop + ($usableHeight - (($maxOrders > 0 ? $orderValue / $maxOrders : 0) * $usableHeight));

            $revenuePoints[] = round($x, 2) . ',' . round($revenueY, 2);
            $orderPoints[] = round($x, 2) . ',' . round($orderY, 2);
            $xLabels[] = [
                'x' => round($x, 2),
                'label' => date('M d', strtotime((string) $row['report_date']))
            ];
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

                <div style="display:flex; gap:16px; flex-wrap:wrap; align-items:center; margin-bottom:14px; font-size:12px;">
                    <div style="display:flex; align-items:center; gap:8px; color:#111; font-weight:700;">
                        <span style="width:14px; height:4px; border-radius:999px; background:#007aff; display:inline-block;"></span>
                        Revenue
                    </div>
                    <div style="display:flex; align-items:center; gap:8px; color:#111; font-weight:700;">
                        <span style="width:14px; height:4px; border-radius:999px; background:#ff9800; display:inline-block;"></span>
                        Orders
                    </div>
                </div>

                <div style="overflow-x:auto; padding-bottom:4px;">
                    <svg viewBox="0 0 <?= $chartWidth ?> <?= $chartHeight ?>" style="width:100%; min-width:680px; height:auto; display:block;">
                        <defs>
                            <linearGradient id="revenueStroke" x1="0%" y1="0%" x2="100%" y2="0%">
                                <stop offset="0%" stop-color="#111111"/>
                                <stop offset="100%" stop-color="#007aff"/>
                            </linearGradient>
                            <linearGradient id="ordersStroke" x1="0%" y1="0%" x2="100%" y2="0%">
                                <stop offset="0%" stop-color="#ffb300"/>
                                <stop offset="100%" stop-color="#ff6f00"/>
                            </linearGradient>
                        </defs>

                        <line x1="<?= $chartPaddingX ?>" y1="<?= $chartHeight - $chartPaddingBottom ?>" x2="<?= $chartWidth - $chartPaddingX ?>" y2="<?= $chartHeight - $chartPaddingBottom ?>" stroke="#ececec" stroke-width="1" />
                        <line x1="<?= $chartPaddingX ?>" y1="<?= $chartPaddingTop ?>" x2="<?= $chartPaddingX ?>" y2="<?= $chartHeight - $chartPaddingBottom ?>" stroke="#f2f2f2" stroke-width="1" />

                        <?php foreach ([0.25, 0.5, 0.75, 1] as $guide): ?>
                            <?php $guideY = round($chartPaddingTop + ($usableHeight - ($usableHeight * $guide)), 2); ?>
                            <line x1="<?= $chartPaddingX ?>" y1="<?= $guideY ?>" x2="<?= $chartWidth - $chartPaddingX ?>" y2="<?= $guideY ?>" stroke="#f5f5f5" stroke-width="1" stroke-dasharray="4 6" />
                        <?php endforeach; ?>

                        <?php if (!empty($revenuePoints)): ?>
                            <polyline fill="none" stroke="url(#revenueStroke)" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" points="<?= htmlspecialchars(implode(' ', $revenuePoints)) ?>" />
                            <?php foreach ($chartRows as $index => $row): ?>
                                <?php
                                [$revX, $revY] = explode(',', $revenuePoints[$index]);
                                [$ordX, $ordY] = explode(',', $orderPoints[$index]);
                                ?>
                                <circle cx="<?= $revX ?>" cy="<?= $revY ?>" r="4.5" fill="#007aff" />
                                <circle cx="<?= $ordX ?>" cy="<?= $ordY ?>" r="4.5" fill="#ff9800" />
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <?php if (!empty($orderPoints)): ?>
                            <polyline fill="none" stroke="url(#ordersStroke)" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" points="<?= htmlspecialchars(implode(' ', $orderPoints)) ?>" />
                        <?php endif; ?>

                        <?php foreach ($xLabels as $label): ?>
                            <text x="<?= $label['x'] ?>" y="<?= $chartHeight - 8 ?>" text-anchor="middle" font-size="11" fill="#777777"><?= htmlspecialchars($label['label']) ?></text>
                        <?php endforeach; ?>
                    </svg>
                </div>

                <div style="display:grid; gap:8px; margin-top:10px;">
                    <?php foreach ($chartRows as $row): ?>
                        <div style="display:flex; justify-content:space-between; gap:10px; flex-wrap:wrap; font-size:12px; color:#666;">
                            <strong style="color:#111;"><?= htmlspecialchars(date('M d', strtotime((string) $row['report_date']))) ?></strong>
                            <span><?= htmlspecialchars($settings['currency_symbol'] ?? 'LKR') ?> <?= number_format((float) ($row['gross_total'] ?? 0), 2) ?> | <?= (int) ($row['orders_count'] ?? 0) ?> orders</span>
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
