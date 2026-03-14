<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Stock Report') ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin.css?v=<?= time() ?>">
    <style>
        .report-header { display:flex; justify-content:space-between; gap:14px; align-items:flex-start; flex-wrap:wrap; margin-bottom:20px; }
        .report-actions { display:flex; gap:10px; flex-wrap:wrap; }
        .report-btn { display:inline-flex; align-items:center; justify-content:center; padding:11px 15px; border-radius:12px; text-decoration:none; font-size:13px; font-weight:800; }
        .report-btn.primary { background:#111; color:#fff; }
        .report-btn.secondary { background:#fff; color:#333; border:1px solid #ececec; }
        .report-btn.export { background:#1f8f45; color:#fff; }
        .report-filter-card,
        .report-panel,
        .report-summary-card,
        .report-highlight { background:#fff; border-radius:18px; box-shadow:0 4px 20px rgba(0,0,0,0.04); }
        .report-filter-card { padding:18px; margin-bottom:20px; }
        .report-filter-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:12px; }
        .report-input,
        .report-select { width:100%; padding:12px 14px; border-radius:12px; border:1px solid #e6e6e6; background:#fff; font-size:13px; box-sizing:border-box; }
        .report-summary-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(160px, 1fr)); gap:14px; margin-bottom:20px; }
        .report-summary-card { padding:18px; }
        .report-summary-label { font-size:11px; color:#777; font-weight:800; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:8px; }
        .report-summary-value { font-size:28px; font-weight:900; color:#111; }
        .report-summary-sub { margin-top:6px; font-size:12px; color:#777; }
        .report-highlight { padding:20px; margin-bottom:20px; background:linear-gradient(135deg, #111 0%, #1d3f72 100%); color:#fff; }
        .report-highlight-label { font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:0.06em; opacity:0.72; margin-bottom:8px; }
        .report-highlight-title { font-size:28px; font-weight:900; margin-bottom:8px; }
        .report-highlight-meta { display:flex; gap:18px; flex-wrap:wrap; font-size:13px; opacity:0.9; }
        .report-two-col { display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:16px; margin-bottom:20px; }
        .report-panel { padding:18px; }
        .report-panel h3 { margin:0 0 6px; font-size:18px; }
        .report-panel p { margin:0 0 14px; font-size:12px; color:#777; }
        .report-list { display:grid; gap:10px; }
        .report-list-item { display:flex; justify-content:space-between; gap:12px; align-items:flex-start; border:1px solid #f0f0f0; border-radius:14px; padding:12px 14px; }
        .report-list-title { font-size:14px; font-weight:800; color:#111; }
        .report-list-sub { margin-top:4px; font-size:12px; color:#777; }
        .report-list-value { text-align:right; font-size:13px; font-weight:800; color:#111; }
        .report-table-wrap { overflow:auto; }
        .report-table { width:100%; border-collapse:collapse; min-width:1080px; }
        .report-table th,
        .report-table td { padding:12px 10px; border-bottom:1px solid #f0f0f0; font-size:12px; text-align:left; vertical-align:top; }
        .report-table th { font-size:11px; text-transform:uppercase; letter-spacing:0.05em; color:#777; }
        .status-pill { display:inline-flex; padding:6px 10px; border-radius:999px; font-size:10px; font-weight:900; text-transform:uppercase; letter-spacing:0.05em; }
        .status-pill.in_stock { background:#ecf8ef; color:#1d7a40; }
        .status-pill.low_stock { background:#fff5e8; color:#9a6a11; }
        .status-pill.out_of_stock { background:#fff1f0; color:#d83b31; }
        .empty-state { padding:18px; border-radius:14px; background:#fafafa; color:#777; font-size:13px; }
    </style>
</head>
<body>
<?php include 'views/admin/partials/loader.php'; ?>
<?php $currency = htmlspecialchars($settings['currency_symbol'] ?? 'LKR'); ?>
<div class="container">
    <div class="report-header">
        <div>
            <h1 class="page-title" style="margin-bottom:6px;">Stock Report</h1>
            <p class="shop-subtitle">Full inventory visibility, best sellers, attention items, and export-ready stock data for your online store.</p>
        </div>
        <div class="report-actions">
            <a href="<?= BASE_URL ?>admin/dashboard" class="report-btn secondary">Back to Dashboard</a>
            <a href="<?= BASE_URL ?>stock/index" class="report-btn secondary">Stock Management</a>
            <a href="<?= BASE_URL ?>stock/exportReport?<?= htmlspecialchars(http_build_query(array_filter($filters ?? [], function ($value) { return $value !== ''; }))) ?>" class="report-btn export">Export Excel</a>
        </div>
    </div>

    <form method="GET" action="<?= BASE_URL ?>stock/report" class="report-filter-card">
        <div class="report-filter-grid">
            <input type="text" name="search" class="report-input" placeholder="Search product, SKU, category" value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
            <select name="stock_state" class="report-select">
                <option value="">All Stock States</option>
                <option value="in_stock" <?= ($filters['stock_state'] ?? '') === 'in_stock' ? 'selected' : '' ?>>In Stock</option>
                <option value="low_stock" <?= ($filters['stock_state'] ?? '') === 'low_stock' ? 'selected' : '' ?>>Low Stock</option>
                <option value="out_of_stock" <?= ($filters['stock_state'] ?? '') === 'out_of_stock' ? 'selected' : '' ?>>Out of Stock</option>
            </select>
            <select name="product_type" class="report-select">
                <option value="">All Product Types</option>
                <option value="simple" <?= ($filters['product_type'] ?? '') === 'simple' ? 'selected' : '' ?>>Simple Products</option>
                <option value="variant" <?= ($filters['product_type'] ?? '') === 'variant' ? 'selected' : '' ?>>Variant Products</option>
            </select>
            <select name="payment_status" class="report-select">
                <option value="">All Payment States</option>
                <option value="paid" <?= ($filters['payment_status'] ?? '') === 'paid' ? 'selected' : '' ?>>Paid</option>
                <option value="pending" <?= ($filters['payment_status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="failed" <?= ($filters['payment_status'] ?? '') === 'failed' ? 'selected' : '' ?>>Failed</option>
            </select>
            <select name="order_status" class="report-select">
                <option value="">All Order States</option>
                <option value="pending" <?= ($filters['order_status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="processing" <?= ($filters['order_status'] ?? '') === 'processing' ? 'selected' : '' ?>>Processing</option>
                <option value="completed" <?= ($filters['order_status'] ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
                <option value="cancelled" <?= ($filters['order_status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
            </select>
            <input type="date" name="date_from" class="report-input" value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>">
            <input type="date" name="date_to" class="report-input" value="<?= htmlspecialchars($filters['date_to'] ?? '') ?>">
        </div>
        <div class="report-actions" style="margin-top:14px;">
            <button type="submit" class="report-btn primary" style="border:none; cursor:pointer;">Apply Filters</button>
            <a href="<?= BASE_URL ?>stock/report" class="report-btn secondary">Reset</a>
        </div>
    </form>

    <div class="report-summary-grid">
        <div class="report-summary-card">
            <div class="report-summary-label">Products in Report</div>
            <div class="report-summary-value"><?= (int) ($summary['total_products'] ?? 0) ?></div>
            <div class="report-summary-sub"><?= (int) ($summary['tracked_products'] ?? 0) ?> tracked stock items</div>
        </div>
        <div class="report-summary-card">
            <div class="report-summary-label">Units On Hand</div>
            <div class="report-summary-value"><?= (int) ($summary['units_on_hand'] ?? 0) ?></div>
            <div class="report-summary-sub"><?= $currency ?> <?= number_format((float) ($summary['inventory_value'] ?? 0), 2) ?> estimated stock value</div>
        </div>
        <div class="report-summary-card">
            <div class="report-summary-label">Units Sold</div>
            <div class="report-summary-value"><?= (int) ($summary['total_units_sold'] ?? 0) ?></div>
            <div class="report-summary-sub"><?= $currency ?> <?= number_format((float) ($summary['total_sales_revenue'] ?? 0), 2) ?> sales revenue</div>
        </div>
        <div class="report-summary-card">
            <div class="report-summary-label">In Stock</div>
            <div class="report-summary-value"><?= (int) ($summary['in_stock'] ?? 0) ?></div>
            <div class="report-summary-sub"><?= (int) ($summary['low_stock'] ?? 0) ?> low stock, <?= (int) ($summary['out_of_stock'] ?? 0) ?> out of stock</div>
        </div>
        <div class="report-summary-card">
            <div class="report-summary-label">Best Seller Count</div>
            <div class="report-summary-value"><?= (int) ($summary['products_with_sales'] ?? 0) ?></div>
            <div class="report-summary-sub">Products with at least one sale</div>
        </div>
        <div class="report-summary-card">
            <div class="report-summary-label">Dead Stock</div>
            <div class="report-summary-value"><?= (int) ($summary['zero_sales_products'] ?? 0) ?></div>
            <div class="report-summary-sub"><?= (int) ($summary['variant_products'] ?? 0) ?> variant, <?= (int) ($summary['simple_products'] ?? 0) ?> simple</div>
        </div>
    </div>

    <?php if (!empty($bestSeller)): ?>
        <div class="report-highlight">
            <div class="report-highlight-label">Best Selling Product</div>
            <div class="report-highlight-title"><?= htmlspecialchars($bestSeller['title'] ?? 'Product') ?></div>
            <div class="report-highlight-meta">
                <span><?= (int) ($bestSeller['units_sold'] ?? 0) ?> units sold</span>
                <span><?= (int) ($bestSeller['orders_count'] ?? 0) ?> orders</span>
                <span><?= $currency ?> <?= number_format((float) ($bestSeller['revenue_total'] ?? 0), 2) ?> revenue</span>
                <span><?= !empty($bestSeller['has_variant_stock']) ? 'Variant Product' : 'Simple Product' ?></span>
            </div>
        </div>
    <?php endif; ?>

    <div class="report-two-col">
        <div class="report-panel">
            <h3>Top Sellers</h3>
            <p>Products ranked by quantity sold in the filtered period.</p>
            <div class="report-list">
                <?php foreach (($topSellers ?? []) as $row): ?>
                    <div class="report-list-item">
                        <div>
                            <div class="report-list-title"><?= htmlspecialchars($row['title'] ?? 'Product') ?></div>
                            <div class="report-list-sub"><?= htmlspecialchars($row['sku'] ?: 'No SKU') ?><?php if (!empty($row['category_name'])): ?> • <?= htmlspecialchars($row['category_name']) ?><?php endif; ?></div>
                        </div>
                        <div class="report-list-value">
                            <div><?= (int) ($row['units_sold'] ?? 0) ?> units</div>
                            <div style="margin-top:4px; color:#777; font-size:12px;"><?= (int) ($row['orders_count'] ?? 0) ?> orders</div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($topSellers)): ?>
                    <div class="empty-state">No sales found for the selected filters yet.</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="report-panel">
            <h3>Top Revenue Products</h3>
            <p>Products generating the highest sales value.</p>
            <div class="report-list">
                <?php foreach (($topRevenue ?? []) as $row): ?>
                    <div class="report-list-item">
                        <div>
                            <div class="report-list-title"><?= htmlspecialchars($row['title'] ?? 'Product') ?></div>
                            <div class="report-list-sub"><?= (int) ($row['units_sold'] ?? 0) ?> units • <?= (int) ($row['orders_count'] ?? 0) ?> orders</div>
                        </div>
                        <div class="report-list-value">
                            <div><?= $currency ?> <?= number_format((float) ($row['revenue_total'] ?? 0), 2) ?></div>
                            <div style="margin-top:4px; color:#777; font-size:12px;"><?= htmlspecialchars($row['status'] ?? 'in_stock') ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($topRevenue)): ?>
                    <div class="empty-state">No revenue data found for the selected filters yet.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="report-two-col">
        <div class="report-panel">
            <h3>Needs Attention</h3>
            <p>Products most likely to need replenishment or review.</p>
            <div class="report-list">
                <?php foreach (($attentionProducts ?? []) as $row): ?>
                    <div class="report-list-item">
                        <div>
                            <div class="report-list-title"><?= htmlspecialchars($row['title'] ?? 'Product') ?></div>
                            <div class="report-list-sub">
                                <span class="status-pill <?= htmlspecialchars($row['status'] ?? 'in_stock') ?>"><?= htmlspecialchars(str_replace('_', ' ', $row['status'] ?? 'in_stock')) ?></span>
                            </div>
                        </div>
                        <div class="report-list-value">
                            <div><?= $row['available_qty'] === null ? 'Manual / unlimited' : ((int) $row['available_qty'] . ' qty') ?></div>
                            <div style="margin-top:4px; color:#777; font-size:12px;"><?= (int) ($row['units_sold'] ?? 0) ?> sold</div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($attentionProducts)): ?>
                    <div class="empty-state">No stock issues found for the selected filters.</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="report-panel">
            <h3>Dead Stock</h3>
            <p>Products with no recorded sales in the filtered period.</p>
            <div class="report-list">
                <?php foreach (($deadStock ?? []) as $row): ?>
                    <div class="report-list-item">
                        <div>
                            <div class="report-list-title"><?= htmlspecialchars($row['title'] ?? 'Product') ?></div>
                            <div class="report-list-sub"><?= $row['available_qty'] === null ? 'Manual stock' : ((int) $row['available_qty'] . ' units on hand') ?></div>
                        </div>
                        <div class="report-list-value">
                            <div><?= $row['inventory_value'] === null ? '-' : ($currency . ' ' . number_format((float) $row['inventory_value'], 2)) ?></div>
                            <div style="margin-top:4px; color:#777; font-size:12px;"><?= htmlspecialchars($row['sku'] ?: 'No SKU') ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($deadStock)): ?>
                    <div class="empty-state">Every product in this filtered report has recorded sales.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="report-panel">
        <h3>Full Stock Report</h3>
        <p>Use this table for daily stock decisions, replenishment checks, and Excel exports.</p>
        <div class="report-table-wrap">
            <table class="report-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Status</th>
                        <th>Type</th>
                        <th>Available Qty</th>
                        <th>Inventory Value</th>
                        <th>Units Sold</th>
                        <th>Orders</th>
                        <th>Revenue</th>
                        <th>Last Ordered</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (($rows ?? []) as $row): ?>
                        <tr>
                            <td>
                                <div style="font-weight:800; color:#111;"><?= htmlspecialchars($row['title'] ?? 'Product') ?></div>
                                <div style="margin-top:4px; color:#777;"><?= htmlspecialchars($row['sku'] ?: 'No SKU') ?><?php if (!empty($row['category_name'])): ?> • <?= htmlspecialchars($row['category_name']) ?><?php endif; ?></div>
                            </td>
                            <td>
                                <span class="status-pill <?= htmlspecialchars($row['status'] ?? 'in_stock') ?>"><?= htmlspecialchars(str_replace('_', ' ', $row['status'] ?? 'in_stock')) ?></span>
                            </td>
                            <td><?= !empty($row['has_variant_stock']) ? 'Variant (' . (int) ($row['variant_count'] ?? 0) . ')' : 'Simple' ?></td>
                            <td><?= $row['available_qty'] === null ? 'Unlimited / manual' : (int) $row['available_qty'] ?></td>
                            <td><?= $row['inventory_value'] === null ? '-' : ($currency . ' ' . number_format((float) $row['inventory_value'], 2)) ?></td>
                            <td><?= (int) ($row['units_sold'] ?? 0) ?></td>
                            <td><?= (int) ($row['orders_count'] ?? 0) ?></td>
                            <td><?= $currency ?> <?= number_format((float) ($row['revenue_total'] ?? 0), 2) ?></td>
                            <td><?= !empty($row['last_ordered_at']) ? htmlspecialchars(date('Y-m-d H:i', strtotime((string) $row['last_ordered_at']))) : 'Never' ?></td>
                            <td>
                                <a href="<?= BASE_URL ?>product/edit/<?= (int) $row['id'] ?>" class="report-btn secondary" style="padding:8px 10px; font-size:12px;">Manage</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($rows)): ?>
                        <tr>
                            <td colspan="10">
                                <div class="empty-state">No products matched your stock report filters.</div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
