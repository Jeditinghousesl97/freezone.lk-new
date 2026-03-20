<!DOCTYPE html>
<html lang="en">

<?php require_once ROOT_PATH . 'helpers/ImageHelper.php'; ?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?= $title ?? 'Login' ?>
    </title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .login-card {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .login-card h2 {
            margin-bottom: 1.5rem;
            color: #333;
        }

        .shop-logo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 10px auto;
            display: block;
            border: 1px solid #ddd;
        }

        .shop-name {
            font-size: 18px;
            font-weight: bold;
            color: #666;
            margin-bottom: 5px;
        }

        .login-heading {
            color: #f39c12;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 16px;
            margin-bottom: 30px;
            margin-top: 0;
        }

        .form-group {
            margin-bottom: 1rem;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #666;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
            background-color: #f2f2f2;
            border: none;
        }

        .btn {
            background-color: #ff2b55;
            color: white;
            padding: 12px 15px;
            border: none;
            border-radius: 5px;
            width: 100%;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s;
            font-weight: bold;
        }

        .btn:hover {
            background-color: #e6224a;
        }

        .error {
            background-color: #ffebee;
            color: #d32f2f;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }

        .forgot-pass {
            margin-top: 20px;
            font-size: 13px;
            color: #aaa;
            text-decoration: underline;
            display: block;
        }
    </style>
</head>

<body>

    <div class="login-card">
        <!-- Logo -->
        <?php if (!empty($settings['shop_logo'])): ?>
            <img src="<?= htmlspecialchars(ImageHelper::settingsImageUrl($settings['shop_logo'], $settings['shop_logo'])) ?>" alt="Shop Logo" class="shop-logo">
        <?php else: ?>
            <div class="shop-logo" style="background:#ccc; display:flex; align-items:center; justify-content:center;">Logo
            </div>
        <?php endif; ?>

        <!-- Shop Name -->
        <div class="shop-name">
            <?= !empty($settings['shop_name']) ? htmlspecialchars($settings['shop_name']) : 'My Shop' ?>
        </div>

        <!-- Title -->
        <h2 class="login-heading">SHOP OWNER LOGIN</h2>

        <?php if (isset($_GET['error'])): ?>
            <div class="error">
                <?php
                if ($_GET['error'] == 'empty_fields')
                    echo "Please fill in all fields.";
                if ($_GET['error'] == 'invalid_credentials')
                    echo "Invalid Username or Password.";
                ?>
            </div>
        <?php endif; ?>

        <form action="<?= BASE_URL ?>auth/authenticate" method="POST">
            <?= csrf_input() ?>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required placeholder="Enter username">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div style="position: relative;">
                    <input type="password" id="password" name="password" required placeholder="Enter password"
                        style="padding-right: 40px;">
                    <img src="<?= BASE_URL ?>assets/icons/eye-close.png" id="togglePassword" alt="Show Password"
                        style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); width: 20px; cursor: pointer;">
                </div>
            </div>

            <button type="submit" class="btn">Login as Shop Owner</button>

            <a href="<?= BASE_URL ?>settings/index" class="forgot-pass">Forget Password?</a>
        </form>
    </div>

    <script>
        const togglePassword = document.getElementById('togglePassword');
        const password = document.getElementById('password');

        togglePassword.addEventListener('click', function () {
            // Toggle the type attribute
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);

            // Toggle the eye icon
            if (type === 'password') {
                this.src = '<?= BASE_URL ?>assets/icons/eye-close.png';
            } else {
                this.src = '<?= BASE_URL ?>assets/icons/eye-open.png';
            }
        });
    </script>


</body>

</html>
