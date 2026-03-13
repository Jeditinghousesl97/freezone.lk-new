<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin.css?v=<?= time() ?>">
</head>
<body>
    <?php include 'views/admin/partials/loader.php'; ?>
    <div class="container">
        <div class="page-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <div>
                <h2 style="margin:0;">Orders</h2>
                <p style="margin:4px 0 0; font-size:12px; color:#888;">Recent online payment orders from your store.</p>
            </div>
            <a href="<?= BASE_URL ?>admin/dashboard" style="text-decoration:none; color:#007aff; font-weight:700;">Back to Dashboard</a>
        </div>

        <?php if (empty($orders)): ?>
            <div style="padding:24px; background:#fff; border-radius:16px; color:#777;">No orders yet.</div>
        <?php else: ?>
            <div style="display:grid; gap:14px;">
                <?php foreach ($orders as $order): ?>
                    <a href="<?= BASE_URL ?>order/details/<?= urlencode($order['order_number']) ?>" style="display:block; text-decoration:none; color:inherit; background:#fff; border-radius:18px; padding:18px; box-shadow:0 4px 20px rgba(0,0,0,0.04);">
                        <div style="display:flex; justify-content:space-between; gap:16px; flex-wrap:wrap;">
                            <div>
                                <div style="font-size:16px; font-weight:800; color:#111;"><?= htmlspecialchars($order['order_number']) ?></div>
                                <div style="font-size:13px; color:#666; margin-top:4px;"><?= htmlspecialchars($order['customer_name']) ?></div>
                            </div>
                            <div style="text-align:right;">
                                <div style="font-size:14px; font-weight:700; color:#111;"><?= htmlspecialchars($order['currency']) ?> <?= number_format((float) $order['total_amount'], 2) ?></div>
                                <div style="font-size:12px; color:#888; margin-top:4px;"><?= htmlspecialchars($order['created_at']) ?></div>
                            </div>
                        </div>
                        <div style="margin-top:14px; display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
                            <span style="padding:6px 10px; border-radius:999px; font-size:11px; font-weight:700; background:#f3f0ff; color:#5b33d6;"><?= htmlspecialchars(strtoupper($order['payment_gateway'])) ?></span>
                            <span style="padding:6px 10px; border-radius:999px; font-size:11px; font-weight:700; background:<?= ($order['payment_status'] ?? '') === 'paid' ? '#e8fff0' : ((($order['payment_status'] ?? '') === 'cancelled') ? '#fff2ec' : '#f4f4f4') ?>; color:<?= ($order['payment_status'] ?? '') === 'paid' ? '#1a9b57' : ((($order['payment_status'] ?? '') === 'cancelled') ? '#d2552c' : '#666') ?>;">
                                <?= htmlspecialchars(strtoupper(str_replace('_', ' ', $order['payment_status'] ?? 'pending'))) ?>
                            </span>
                            <span style="padding:6px 10px; border-radius:999px; font-size:11px; font-weight:700; background:#eef5ff; color:#2463d0;">
                                <?= htmlspecialchars(strtoupper(str_replace('_', ' ', $order['order_status'] ?? 'pending'))) ?>
                            </span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php $current_page = 'orders';
    include 'views/layouts/bottom_nav.php'; ?>
</body>
</html>
