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
                <h2 style="margin:0;">Order Details</h2>
                <p style="margin:4px 0 0; font-size:12px; color:#888;"><?= htmlspecialchars($order['order_number']) ?></p>
            </div>
            <a href="<?= BASE_URL ?>order/manage" style="text-decoration:none; color:#007aff; font-weight:700;">Back to Orders</a>
        </div>

        <div style="display:grid; gap:18px;">
            <div style="background:#fff; border-radius:18px; padding:20px; box-shadow:0 4px 20px rgba(0,0,0,0.04);">
                <h3 style="margin:0 0 14px;">Payment Summary</h3>
                <div style="display:grid; gap:10px; font-size:14px;">
                    <div><strong>Status:</strong> <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $order['payment_status'] ?? 'pending'))) ?></div>
                    <div><strong>Order Type:</strong> <?= htmlspecialchars(strtoupper($order['payment_method'] ?? $order['payment_gateway'] ?? '-')) ?></div>
                    <div><strong>Order Status:</strong> <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $order['order_status'] ?? 'pending'))) ?></div>
                    <div><strong>Gateway:</strong> <?= htmlspecialchars(strtoupper($order['payment_gateway'])) ?></div>
                    <div><strong>Amount:</strong> <?= htmlspecialchars($order['currency']) ?> <?= number_format((float) $order['total_amount'], 2) ?></div>
                    <div><strong>Payment ID:</strong> <?= htmlspecialchars($order['gateway_payment_id'] ?: '-') ?></div>
                    <div><strong>Message:</strong> <?= htmlspecialchars($order['gateway_message'] ?: '-') ?></div>
                    <div><strong>Created:</strong> <?= htmlspecialchars($order['created_at']) ?></div>
                </div>
                <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top:16px;">
                <?php if (($order['payment_method'] ?? '') === 'cod' && ($order['payment_status'] ?? 'pending') !== 'paid'): ?>
                    <form action="<?= BASE_URL ?>order/markPaymentReceived/<?= urlencode($order['order_number']) ?>" method="POST" onsubmit="return confirm('Mark COD payment as received?');">
                        <button type="submit" onclick="showGlobalLoader()" style="border:none; background:#1a9b57; color:#fff; padding:12px 18px; border-radius:999px; font-weight:700; cursor:pointer;">
                            Payment Received
                        </button>
                    </form>
                <?php endif; ?>
                <?php if (($order['order_status'] ?? 'pending') !== 'completed'): ?>
                    <form action="<?= BASE_URL ?>order/markCompleted/<?= urlencode($order['order_number']) ?>" method="POST" onsubmit="return confirm('Mark this order as completed?');">
                        <button type="submit" onclick="showGlobalLoader()" style="border:none; background:#111; color:#fff; padding:12px 18px; border-radius:999px; font-weight:700; cursor:pointer;">
                            Mark Order as Completed
                        </button>
                    </form>
                <?php endif; ?>
                <?php if (($order['order_status'] ?? 'pending') !== 'cancelled'): ?>
                    <form action="<?= BASE_URL ?>order/cancel/<?= urlencode($order['order_number']) ?>" method="POST" onsubmit="return confirm('Cancel this order?');">
                        <button type="submit" onclick="showGlobalLoader()" style="border:none; background:#f39c12; color:#fff; padding:12px 18px; border-radius:999px; font-weight:700; cursor:pointer;">
                            Cancel Order
                        </button>
                    </form>
                <?php endif; ?>
                    <form action="<?= BASE_URL ?>order/delete/<?= urlencode($order['order_number']) ?>" method="POST" onsubmit="return confirm('Delete this order permanently?');">
                        <button type="submit" onclick="showGlobalLoader()" style="border:none; background:#e2552f; color:#fff; padding:12px 18px; border-radius:999px; font-weight:700; cursor:pointer;">
                            Delete Order
                        </button>
                    </form>
                </div>
            </div>

            <div style="background:#fff; border-radius:18px; padding:20px; box-shadow:0 4px 20px rgba(0,0,0,0.04);">
                <h3 style="margin:0 0 14px;">Customer Details</h3>
                <div style="display:grid; gap:10px; font-size:14px;">
                    <div><strong>Name:</strong> <?= htmlspecialchars($order['customer_name']) ?></div>
                    <div><strong>Email:</strong> <?= htmlspecialchars($order['email']) ?></div>
                    <div><strong>Phone:</strong> <?= htmlspecialchars($order['phone']) ?></div>
                    <div><strong>Alternate Phone:</strong> <?= htmlspecialchars($order['phone_alt'] ?: '-') ?></div>
                    <div><strong>Address:</strong> <?= htmlspecialchars($order['address']) ?></div>
                    <div><strong>City:</strong> <?= htmlspecialchars($order['city']) ?></div>
                    <div><strong>District:</strong> <?= htmlspecialchars($order['district'] ?: '-') ?></div>
                    <div><strong>Postal Code:</strong> <?= htmlspecialchars($order['postal_code'] ?: '-') ?></div>
                    <div><strong>Country:</strong> <?= htmlspecialchars($order['country']) ?></div>
                    <div><strong>Note:</strong> <?= htmlspecialchars($order['note'] ?: '-') ?></div>
                </div>
            </div>

            <div style="background:#fff; border-radius:18px; padding:20px; box-shadow:0 4px 20px rgba(0,0,0,0.04);">
                <h3 style="margin:0 0 14px;">Items</h3>
                <div style="display:grid; gap:14px;">
                    <?php if (empty($order['items'])): ?>
                        <div style="padding:14px; border-radius:14px; background:#fafafa; color:#777;">No order items found.</div>
                    <?php else: ?>
                        <?php foreach ($order['items'] as $item): ?>
                            <div style="padding:14px; border-radius:14px; background:#fafafa;">
                                <div style="font-size:15px; font-weight:700; color:#111;"><?= htmlspecialchars($item['product_title']) ?></div>
                                <div style="font-size:12px; color:#666; margin-top:4px;"><?= htmlspecialchars($item['variant_text'] ?: '-') ?></div>
                                <div style="font-size:13px; color:#333; margin-top:8px;">
                                    Qty <?= (int) $item['qty'] ?> x <?= htmlspecialchars($order['currency']) ?> <?= number_format((float) $item['unit_price'], 2) ?>
                                    = <strong><?= htmlspecialchars($order['currency']) ?> <?= number_format((float) $item['line_total'], 2) ?></strong>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php $current_page = 'orders';
    include 'views/layouts/bottom_nav.php'; ?>
</body>
</html>
