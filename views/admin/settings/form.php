<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin.css">
    <style>
        .header-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .logo-img {
            height: 40px;
        }

        .publish-txt {
            color: #007aff;
            font-weight: bold;
            cursor: pointer;
            border: none;
            background: none;
            font-size: 16px;
        }

        .label {
            font-weight: bold;
            color: #555;
            font-size: 13px;
            margin-bottom: 5px;
            display: block;
        }

        .input-box {
            width: 100%;
            padding: 12px 15px;
            background: #f9f9f9;
            border: 1px solid #eee;
            border-radius: 8px;
            font-size: 14px;
            box-sizing: border-box;
            margin-bottom: 20px;
        }

        .img-row {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
        }

        .img-card {
            flex: 1;
            text-align: center;
        }

        .img-upload-box {
            background: white;
            border: 1px solid #eee;
            border-radius: 12px;
            height: 120px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .preview-thumb {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 10px;
            box-sizing: border-box;
        }

        /* Red Border Section */
        .user-mgmt-box {
            border: 1px solid #ff3b30;
            border-radius: 15px;
            padding: 25px 20px;
            margin-top: 30px;
            background: white;
            position: relative;
        }

        .user-mgmt-label {
            color: #ff3b30;
            font-weight: bold;
            margin-bottom: 8px;
            font-size: 13px;
            display: block;
        }

        .input-pink {
            background: #ffeaea;
            border: none;
            margin-bottom: 15px;
        }

        .update-link {
            color: #ff3b30;
            text-decoration: underline;
            font-weight: bold;
            float: right;
            font-size: 15px;
            cursor: pointer;
            border: none;
            background: none;
            padding: 0;
        }

        /* Green Button */
        .btn-global-styles {
            display: block;
            width: 100%;
            background-color: #7ab586;
            color: white;
            padding: 12px;
            border-radius: 25px;
            text-align: center;
            text-decoration: none;
            font-weight: bold;
            margin-top: 40px;
            border: none;
            font-size: 16px;
        }
    </style>
</head>

<body>

    <form action="<?= BASE_URL ?>settings/update" method="POST" enctype="multipart/form-data">
        <div class="container" style="padding-bottom:100px;">

            <style>
                .btn-publish {
                    background: linear-gradient(135deg, #007aff, #0056b3);
                    color: white;
                    border: none;
                    padding: 10px 20px;
                    border-radius: 25px;
                    font-weight: bold;
                    cursor: pointer;
                    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                    transition: transform 0.2s, box-shadow 0.2s;
                }

                .btn-publish:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
                }

                .btn-exit {
                    background: white;
                    color: #ff3b30;
                    border: 2px solid #ff3b30;
                    padding: 8px 16px;
                    border-radius: 25px;
                    text-decoration: none;
                    font-weight: bold;
                    font-size: 14px;
                    transition: all 0.3s;
                }

                .btn-exit:hover {
                    background: #ff3b30;
                    color: white;
                }

                @media (max-width: 600px) {
                    .header-bar {
                        flex-direction: row;
                        flex-wrap: wrap;
                        gap: 10px;
                    }

                    .logo-img {
                        height: 30px;
                        /* Slightly smaller logo */
                    }

                    .header-actions {
                        margin-left: auto;
                        /* Push to right */
                        display: flex;
                        gap: 8px !important;
                        /* Reduce gap */
                        align-items: center;
                    }

                    .btn-publish {
                        padding: 8px 14px;
                        font-size: 13px;
                    }

                    .btn-exit {
                        padding: 6px 12px;
                        font-size: 13px;
                    }
                }
            </style>
            <div class="header-bar">
                <img src="<?= BASE_URL ?>assets/icons/Asseminate-Logo.png" class="logo-img" alt="Asseminate">
                <div class="header-actions" style="display:flex; gap:15px; align-items:center;">
                    <a href="<?= BASE_URL ?>settings/exit_dev" class="btn-exit">Exit</a>
                    <button type="submit" class="publish-txt btn-publish">PUBLISH</button>
                </div>
            </div>

            <h2 style="margin:0 0 20px 0;">Shop Settings</h2>

            <label class="label">Shop Name</label>
            <input type="text" name="shop_name" class="input-box" placeholder="Enter Shop Name"
                value="<?= htmlspecialchars($settings['shop_name'] ?? '') ?>">

            <label class="label">Shop Link</label>
            <input type="text" name="shop_url" class="input-box" placeholder="Enter Shop URL"
                value="<?= htmlspecialchars($settings['shop_url'] ?? '') ?>">

            <div class="img-row">
                <!-- Logo -->
                <div class="img-card">
                    <span class="label">Shop Logo</span>
                    <div class="img-upload-box" onclick="document.getElementById('logoInput').click()">
                        <?php if (!empty($settings['shop_logo'])): ?>
                            <img src="<?= $settings['shop_logo'] ?>" class="preview-thumb">
                        <?php endif; ?>
                        <div style="font-size:20px;">📷</div>
                        <p style="font-size:10px; color:#999;">Tap here to<br>upload a photo</p>
                        <input type="file" name="shop_logo" id="logoInput" style="display:none;">
                    </div>
                </div>
                <!-- QR -->
                <div class="img-card">
                    <span class="label">Shop QR</span>
                    <div class="img-upload-box" onclick="document.getElementById('qrInput').click()">
                        <?php if (!empty($settings['shop_qr'])): ?>
                            <img src="<?= $settings['shop_qr'] ?>" class="preview-thumb">
                        <?php endif; ?>
                        <div style="font-size:20px;">📷</div>
                        <p style="font-size:10px; color:#999;">Tap here to<br>upload a photo</p>
                        <input type="file" name="shop_qr" id="qrInput" style="display:none;">
                    </div>
                </div>
            </div>

            <!-- New Row for Favicon -->
            <div class="img-row">
                <div class="img-card">
                    <span class="label">Shop Favicon</span>
                    <div class="img-upload-box" onclick="document.getElementById('favInput').click()">
                        <?php if (!empty($settings['shop_favicon'])): ?>
                            <img src="<?= $settings['shop_favicon'] ?>" class="preview-thumb">
                        <?php endif; ?>
                        <div style="font-size:20px;">📷</div>
                        <p style="font-size:10px; color:#999;">Tap here to<br>upload a photo</p>
                        <input type="file" name="shop_favicon" id="favInput" style="display:none;">
                    </div>
                </div>
                <!-- Empty spacer to keep alignment if needed, or just let it be left aligned -->
                <div class="img-card" style="visibility: hidden;"></div>
            </div>

            <label class="label">Shop About</label>
            <textarea name="shop_about" class="input-box" rows="4"
                placeholder="Insert Shop Slogan, Address, Email..."><?= htmlspecialchars($settings['shop_about'] ?? '') ?></textarea>

            <label class="label">Shop Owner's Whatsapp Number</label>
            <p style="font-size:10px; color:#999; margin-bottom:5px;">Order request notifications will be sent to this
                number.</p>
            <input type="text" name="shop_whatsapp" class="input-box" placeholder="+94XXXXXX"
                value="<?= htmlspecialchars($settings['shop_whatsapp'] ?? '') ?>">

            <label class="label">Currency Symbol</label>
            <input type="text" name="currency_symbol" class="input-box" placeholder="LKR"
                value="<?= htmlspecialchars($settings['currency_symbol'] ?? '') ?>">

            <!-- User Management -->
            <div class="user-mgmt-box">
                <!-- Hidden ID if exists -->
                <input type="hidden" name="owner_id" value="<?= $owner['id'] ?? '' ?>">

                <label class="user-mgmt-label">Shop Owner Username</label>
                <input type="text" name="owner_username" class="input-box input-pink"
                    value="<?= htmlspecialchars($owner['username'] ?? '') ?>" placeholder="Enter Username" required>

                <label class="user-mgmt-label">Shop Owner Password</label>
                <input type="text" name="owner_password" class="input-box input-pink" placeholder="***********"
                    <?= empty($owner) ? 'required' : '' ?>>

                <div style="display:flex; gap:10px; margin-top:15px;">
                    <button type="submit" name="owner_action" value="update" class="update-link"
                        style="border:1px solid #ff3b30; border-radius:15px; padding:8px 15px; text-decoration:none; font-size:14px;">Update
                        Existing</button>

                    <button type="submit" name="owner_action" value="create" class="update-link"
                        style="background:#ff3b30; color:white; border-radius:15px; padding:8px 15px; text-decoration:none; font-size:14px;"
                        onclick="return confirm('Warning: This will DELETE the existing owner account and create a new one. The old login will stop working. Continue?')">Create
                        New</button>
                </div>
                <div style="clear:both;"></div>
            </div>

            <!-- Global Styles Button -->
            <a href="<?= BASE_URL ?>settings/styles" class="btn-global-styles">Global Styles</a>

        </div>
    </form>

    <?php $current_page = 'settings';
    include 'views/layouts/bottom_nav.php'; ?>

</body>

</html>