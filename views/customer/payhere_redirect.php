<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirecting to PayHere</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            background: #f8f8fb;
            color: #222;
        }
        .payhere-redirect-card {
            width: min(420px, 92vw);
            background: #fff;
            border-radius: 24px;
            padding: 32px 28px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.08);
            text-align: center;
        }
        .spinner {
            width: 52px;
            height: 52px;
            border: 5px solid #ece7ff;
            border-top-color: #7a3cff;
            border-radius: 50%;
            margin: 0 auto 18px;
            animation: spin 0.9s linear infinite;
        }
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        .fallback-btn {
            display: inline-block;
            margin-top: 18px;
            padding: 12px 20px;
            border-radius: 999px;
            background: #7a3cff;
            color: #fff;
            text-decoration: none;
            border: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="payhere-redirect-card">
        <div class="spinner"></div>
        <h1 style="font-size: 24px; margin: 0 0 10px;">Redirecting to PayHere</h1>
        <p style="margin: 0; color: #666; line-height: 1.6;">Your order has been created. Please wait while we connect you to the secure payment gateway.</p>

        <form id="payhereRedirectForm" method="post" action="<?= htmlspecialchars($endpoint) ?>">
            <?php foreach ($payherePayload as $key => $value): ?>
                <input type="hidden" name="<?= htmlspecialchars($key) ?>" value="<?= htmlspecialchars($value) ?>">
            <?php endforeach; ?>
            <button type="submit" class="fallback-btn">Continue to PayHere</button>
        </form>
    </div>

    <script>
        window.addEventListener('load', function () {
            document.getElementById('payhereRedirectForm').submit();
        });
    </script>
</body>
</html>
