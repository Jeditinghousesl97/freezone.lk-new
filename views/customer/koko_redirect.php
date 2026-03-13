<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirecting to KOKO...</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f6f7fb; display:flex; align-items:center; justify-content:center; min-height:100vh; margin:0;">
    <div style="background:#fff; padding:28px; border-radius:24px; box-shadow:0 18px 40px rgba(0,0,0,0.08); max-width:520px; width:calc(100% - 32px); text-align:center;">
        <div style="width:70px; height:70px; border-radius:20px; margin:0 auto 16px; background:#fff3dc; display:flex; align-items:center; justify-content:center; font-size:30px; color:#b87300;">K</div>
        <h1 style="margin:0 0 10px; font-size:28px; color:#111;">Redirecting to KOKO</h1>
        <p style="margin:0; color:#666; line-height:1.7;">Please wait while we connect you to the KOKO secure payment page.</p>

        <form id="kokoRedirectForm" action="<?= htmlspecialchars($kokoEndpoint) ?>" method="post" style="margin-top:20px;">
            <?php foreach ($kokoPayload as $key => $value): ?>
                <input type="hidden" name="<?= htmlspecialchars($key) ?>" value="<?= htmlspecialchars((string) $value, ENT_QUOTES) ?>">
            <?php endforeach; ?>
            <button type="submit" style="padding:12px 18px; border:none; border-radius:999px; background:#111; color:#fff; font-weight:700; cursor:pointer;">
                Continue to KOKO
            </button>
        </form>
    </div>

    <script>
        setTimeout(function () {
            var form = document.getElementById('kokoRedirectForm');
            if (form) {
                form.submit();
            }
        }, 200);
    </script>
</body>
</html>
