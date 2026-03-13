<?php
$hide_mobile_welcome = true;
require_once 'views/layouts/customer_header.php';

$paymentStatus = $order['payment_status'] ?? 'unknown';
$isSuccess = $paymentStatus === 'paid';
$isCancelled = $paymentStatus === 'cancelled';
$isAwaitingConfirmation = !$isSuccess && !$isCancelled && (($status_type ?? '') === 'return');
$heading = $isSuccess ? 'Payment Completed' : ($isCancelled ? 'Payment Cancelled' : ($isAwaitingConfirmation ? 'Payment Submitted' : 'Payment Status Pending'));
$message = $isSuccess
    ? 'Your payment was completed successfully. We can now process your order.'
    : ($isCancelled
        ? 'Your payment was cancelled. You can return to the cart and try again anytime.'
        : ($isAwaitingConfirmation
            ? 'Your payment was submitted successfully. We are waiting for final confirmation from PayHere. This page will refresh automatically.'
            : 'We are still waiting for final confirmation from PayHere. This page will refresh automatically.'));

$displayStatus = $isSuccess
    ? 'Payment Completed'
    : ($isCancelled
        ? 'Payment Cancelled'
        : ($isAwaitingConfirmation ? 'Awaiting Payment Confirmation' : ucfirst(str_replace('_', ' ', $paymentStatus))));
?>

<div style="max-width: 760px; margin: 60px auto 0; padding: 24px 20px 48px;">
    <div style="background: #fff; border-radius: 28px; padding: 28px; box-shadow: 0 16px 40px rgba(0,0,0,0.06);">
        <div style="width:64px; height:64px; border-radius:50%; display:flex; align-items:center; justify-content:center; margin-bottom:18px; background: <?= $isSuccess ? '#e8fff0' : ($isCancelled ? '#fff3f0' : '#f4f1ff') ?>; color: <?= $isSuccess ? '#18a957' : ($isCancelled ? '#e2552f' : '#6c45ff') ?>; font-size:28px;">
            <?= $isSuccess ? '✓' : ($isCancelled ? '!' : '...') ?>
        </div>
        <h1 style="margin:0 0 8px; font-size:30px; color:#111;"><?= htmlspecialchars($heading) ?></h1>
        <p style="margin:0 0 24px; color:#666; line-height:1.7;"><?= htmlspecialchars($message) ?></p>

        <?php if (!empty($order)): ?>
            <div style="display:grid; gap:12px; background:#fafafa; border-radius:20px; padding:20px;">
                <div><strong>Order Number:</strong> <?= htmlspecialchars($order['order_number']) ?></div>
                <div><strong>Customer:</strong> <?= htmlspecialchars($order['customer_name']) ?></div>
                <div><strong>Amount:</strong> <?= htmlspecialchars($order['currency']) ?> <?= number_format((float) ($order['total_amount'] ?? 0), 2) ?></div>
                <div><strong>Payment Status:</strong> <?= htmlspecialchars($displayStatus) ?></div>
                <?php if (!empty($order['gateway_payment_id'])): ?>
                    <div><strong>PayHere Payment ID:</strong> <?= htmlspecialchars($order['gateway_payment_id']) ?></div>
                <?php endif; ?>
                <?php if (!empty($order['gateway_message'])): ?>
                    <div><strong>Message:</strong> <?= htmlspecialchars($order['gateway_message']) ?></div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div style="display:flex; gap:12px; flex-wrap:wrap; margin-top:24px;">
            <a href="<?= BASE_URL ?>" style="padding:12px 18px; border-radius:999px; background:#111; color:#fff; text-decoration:none;">Back to Home</a>
            <a href="<?= BASE_URL ?>cart" style="padding:12px 18px; border-radius:999px; background:#f2f2f2; color:#222; text-decoration:none;">Go to Cart</a>
        </div>
    </div>
</div>

<?php if (!$isSuccess && !$isCancelled): ?>
    <script>
        setTimeout(function () {
            window.location.reload();
        }, 5000);
    </script>
<?php endif; ?>

<?php require_once 'views/layouts/customer_footer.php'; ?>
