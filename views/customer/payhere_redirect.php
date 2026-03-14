<?php
$hide_mobile_welcome = true;
require_once 'views/layouts/customer_header.php';
?>

<div style="max-width: 620px; margin: 60px auto 0; padding: 24px 20px 48px;">
    <div style="background: #fff; border-radius: 28px; padding: 28px; box-shadow: 0 16px 40px rgba(0,0,0,0.06); text-align: center;">
        <div style="width: 64px; height: 64px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 18px; background: #f4f1ff; color: #6c45ff; font-size: 28px;">
            ...
        </div>
        <h1 style="margin: 0 0 8px; font-size: 30px; color: #111;">Redirecting to Card Payment</h1>
        <p style="margin: 0; color: #666; line-height: 1.7;">Please wait a moment while we connect you to the secure payment page.</p>
    </div>
</div>

<form id="payhereCheckoutForm" action="<?= htmlspecialchars($endpoint) ?>" method="POST" style="display:none;">
    <?php foreach ($payherePayload as $key => $value): ?>
        <input type="hidden" name="<?= htmlspecialchars($key) ?>" value="<?= htmlspecialchars($value) ?>">
    <?php endforeach; ?>
</form>

<script>
    document.getElementById('payhereCheckoutForm').submit();
</script>

<?php require_once 'views/layouts/customer_footer.php'; ?>
