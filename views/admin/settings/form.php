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

            <div style="margin-top:30px; padding:20px; border-radius:16px; background:#ffffff; border:1px solid #e9e9e9;">
                <h3 style="margin:0 0 14px;">SMTP Email Settings</h3>
                <p style="font-size:12px; color:#777; margin:0 0 16px;">These settings are used for order emails to the customer and shop owner.</p>

                <label class="label">SMTP Host</label>
                <input type="text" name="smtp_host" class="input-box" placeholder="mail.yourdomain.com"
                    value="<?= htmlspecialchars($settings['smtp_host'] ?? '') ?>">

                <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:14px;">
                    <div>
                        <label class="label">SMTP Port</label>
                        <input type="text" name="smtp_port" class="input-box" placeholder="587"
                            value="<?= htmlspecialchars($settings['smtp_port'] ?? '587') ?>">
                    </div>
                    <div>
                        <label class="label">Encryption</label>
                        <select name="smtp_encryption" class="input-box">
                            <?php foreach (['tls' => 'TLS', 'ssl' => 'SSL', 'none' => 'None'] as $encKey => $encLabel): ?>
                                <option value="<?= $encKey ?>" <?= (($settings['smtp_encryption'] ?? 'tls') === $encKey) ? 'selected' : '' ?>><?= htmlspecialchars($encLabel) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <label class="label">SMTP Username</label>
                <input type="text" name="smtp_username" class="input-box" placeholder="username"
                    value="<?= htmlspecialchars($settings['smtp_username'] ?? '') ?>">

                <label class="label">SMTP Password</label>
                <input type="password" name="smtp_password" class="input-box" placeholder="Leave blank to keep current password" value="">

                <label class="label">From Email</label>
                <input type="email" name="smtp_from_email" class="input-box" placeholder="noreply@yourshop.com"
                    value="<?= htmlspecialchars($settings['smtp_from_email'] ?? '') ?>">

                <label class="label">From Name</label>
                <input type="text" name="smtp_from_name" class="input-box" placeholder="Your Shop Name"
                    value="<?= htmlspecialchars($settings['smtp_from_name'] ?? ($settings['shop_name'] ?? '')) ?>">
            </div>

            <div style="margin-top:20px; padding:20px; border-radius:16px; background:#ffffff; border:1px solid #e9e9e9;">
                <h3 style="margin:0 0 14px;">PayHere Settings</h3>
                <p style="font-size:12px; color:#777; margin:0 0 16px;">Manage PayHere checkout here. Leave the secret blank if you want to keep the current saved value.</p>

                <label style="display:flex; align-items:center; gap:8px; margin-bottom:14px; font-size:14px; color:#333;">
                    <input type="checkbox" name="payhere_enabled" value="1" <?= !empty($settings['payhere_enabled']) ? 'checked' : '' ?>>
                    Enable PayHere checkout
                </label>

                <label style="display:flex; align-items:center; gap:8px; margin-bottom:18px; font-size:14px; color:#333;">
                    <input type="checkbox" name="payhere_sandbox" value="1" <?= !empty($settings['payhere_sandbox']) ? 'checked' : '' ?>>
                    Use PayHere sandbox mode
                </label>

                <label class="label">PayHere Merchant ID</label>
                <input type="text" name="payhere_merchant_id" class="input-box" placeholder="PayHere Merchant ID"
                    value="<?= htmlspecialchars($settings['payhere_merchant_id'] ?? '') ?>">

                <label class="label">PayHere Merchant Secret</label>
                <input type="password" name="payhere_merchant_secret" class="input-box"
                    autocomplete="new-password"
                    placeholder="Leave blank to keep current PayHere Merchant Secret"
                    value="">
            </div>

            <div style="margin-top:20px; padding:20px; border-radius:16px; background:#ffffff; border:1px solid #e9e9e9;">
                <h3 style="margin:0 0 14px;">KOKO Settings</h3>
                <p style="font-size:12px; color:#777; margin:0 0 16px;">Configure KOKO Buy Now Pay Later. Leave secret fields blank if you want to keep the current saved values.</p>

                <label style="display:flex; align-items:center; gap:8px; margin-bottom:14px; font-size:14px; color:#333;">
                    <input type="checkbox" name="koko_enabled" value="1" <?= !empty($settings['koko_enabled']) ? 'checked' : '' ?>>
                    Enable KOKO checkout
                </label>

                <label style="display:flex; align-items:center; gap:8px; margin-bottom:18px; font-size:14px; color:#333;">
                    <input type="checkbox" name="koko_sandbox" value="1" <?= !empty($settings['koko_sandbox']) ? 'checked' : '' ?>>
                    Use KOKO sandbox mode
                </label>

                <label class="label">Gateway Title</label>
                <input type="text" name="koko_title" class="input-box" placeholder="Koko: Buy Now Pay Later"
                    value="<?= htmlspecialchars($settings['koko_title'] ?? 'Koko: Buy Now Pay Later') ?>">

                <label class="label">Gateway Description</label>
                <textarea name="koko_description" class="input-box" rows="3" placeholder="Pay in 3 interest free installments with Koko."><?= htmlspecialchars($settings['koko_description'] ?? 'Pay in 3 interest free installments with Koko.') ?></textarea>

                <label class="label">KOKO Merchant ID</label>
                <input type="text" name="koko_merchant_id" class="input-box" placeholder="Merchant ID"
                    value="<?= htmlspecialchars($settings['koko_merchant_id'] ?? '') ?>">

                <label class="label">KOKO API Key</label>
                <input type="password" name="koko_api_key" class="input-box" autocomplete="new-password"
                    placeholder="Leave blank to keep current KOKO API Key" value="">

                <label class="label">KOKO Public Key</label>
                <textarea name="koko_public_key" class="input-box" rows="7" placeholder="-----BEGIN PUBLIC KEY-----"><?= htmlspecialchars($settings['koko_public_key'] ?? '') ?></textarea>

                <label class="label">KOKO Private Key</label>
                <textarea name="koko_private_key" class="input-box" rows="8" placeholder="Leave blank to keep current KOKO Private Key"></textarea>

                <label class="label">KOKO Callback Secret</label>
                <input type="password" name="koko_callback_secret" class="input-box" autocomplete="new-password"
                    placeholder="Optional shared secret for callback validation" value="">
            </div>

            <div style="margin-top:20px; padding:20px; border-radius:16px; background:#ffffff; border:1px solid #e9e9e9;">
                <h3 style="margin:0 0 14px;">Customer SMS Settings</h3>
                <p style="font-size:12px; color:#777; margin:0 0 16px;">Uses SMSLenz to send order updates only to the customer. API format based on <code>https://smslenz.lk/api/send-sms</code>.</p>

                <label style="display:flex; align-items:center; gap:8px; margin-bottom:18px; font-size:14px; color:#333;">
                    <input type="checkbox" name="sms_enabled" value="1" <?= !empty($settings['sms_enabled']) ? 'checked' : '' ?>>
                    Enable customer SMS notifications
                </label>

                <label style="display:flex; align-items:center; gap:8px; margin-bottom:18px; font-size:14px; color:#333;">
                    <input type="checkbox" name="sms_owner_enabled" value="1" <?= !empty($settings['sms_owner_enabled']) ? 'checked' : '' ?>>
                    Send order received SMS to shop owner
                </label>

                <div style="font-size:12px; color:#777; margin:-8px 0 14px;">
                    Shop owner SMS uses the existing <strong>Shop Owner's Whatsapp Number</strong> as the destination phone number.
                </div>

                <label class="label">SMS API Base URL</label>
                <input type="text" name="sms_base_url" class="input-box" placeholder="https://smslenz.lk/api"
                    value="<?= htmlspecialchars($settings['sms_base_url'] ?? 'https://smslenz.lk/api') ?>">

                <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:14px;">
                    <div>
                        <label class="label">SMS User ID</label>
                        <input type="text" name="sms_user_id" class="input-box" placeholder="1459"
                            value="<?= htmlspecialchars($settings['sms_user_id'] ?? '') ?>">
                    </div>
                    <div>
                        <label class="label">Sender ID</label>
                        <input type="text" name="sms_sender_id" class="input-box" placeholder="Approved Sender ID"
                            value="<?= htmlspecialchars($settings['sms_sender_id'] ?? '') ?>">
                    </div>
                </div>

                <label class="label">SMS API Key</label>
                <input type="password" name="sms_api_key" class="input-box" autocomplete="new-password"
                    placeholder="Leave blank to keep current SMS API Key" value="">

                <div style="font-size:12px; color:#777; margin:-8px 0 14px;">
                    Available placeholders:
                    <code>{shop_name}</code>,
                    <code>{customer_name}</code>,
                    <code>{order_number}</code>,
                    <code>{currency}</code>,
                    <code>{total_amount}</code>,
                    <code>{payment_status}</code>,
                    <code>{order_status}</code>,
                    <code>{payment_method}</code>,
                    <code>{courier_service}</code>,
                    <code>{tracking_number}</code>,
                    <code>{shop_whatsapp}</code>,
                    <code>{website_url}</code>
                </div>

                <label class="label">Order Placed SMS</label>
                <textarea name="sms_template_order_placed" class="input-box" rows="3" placeholder="Hi {customer_name}, your order {order_number} at {shop_name} has been placed. Total: {currency} {total_amount}."><?= htmlspecialchars($settings['sms_template_order_placed'] ?? '') ?></textarea>

                <label class="label">Payment Completed SMS</label>
                <textarea name="sms_template_payment_completed" class="input-box" rows="3" placeholder="Good news {customer_name}. Payment completed for order {order_number} at {shop_name}."><?= htmlspecialchars($settings['sms_template_payment_completed'] ?? '') ?></textarea>

                <label class="label">Payment Cancelled SMS</label>
                <textarea name="sms_template_payment_cancelled" class="input-box" rows="3" placeholder="Your payment was cancelled for order {order_number} at {shop_name}."><?= htmlspecialchars($settings['sms_template_payment_cancelled'] ?? '') ?></textarea>

                <label class="label">Payment Failed SMS</label>
                <textarea name="sms_template_payment_failed" class="input-box" rows="3" placeholder="We could not confirm payment for order {order_number} at {shop_name}. Please try again or contact us."><?= htmlspecialchars($settings['sms_template_payment_failed'] ?? '') ?></textarea>

                <label class="label">COD Payment Received SMS</label>
                <textarea name="sms_template_payment_received" class="input-box" rows="3" placeholder="Payment received for your order {order_number} at {shop_name}. Thank you."><?= htmlspecialchars($settings['sms_template_payment_received'] ?? '') ?></textarea>

                <label class="label">Order Completed SMS</label>
                <textarea name="sms_template_order_completed" class="input-box" rows="3" placeholder="Your order {order_number} from {shop_name} is completed. Courier: {courier_service}. Tracking: {tracking_number}."><?= htmlspecialchars($settings['sms_template_order_completed'] ?? '') ?></textarea>

                <label class="label">Order Cancelled SMS</label>
                <textarea name="sms_template_order_cancelled" class="input-box" rows="3" placeholder="Your order {order_number} from {shop_name} has been cancelled."><?= htmlspecialchars($settings['sms_template_order_cancelled'] ?? '') ?></textarea>

                <label class="label">Shop Owner Order Received SMS</label>
                <textarea name="sms_template_owner_order_received" class="input-box" rows="3" placeholder="New order {order_number} received at {shop_name} from {customer_name}. Total: {currency} {total_amount}."><?= htmlspecialchars($settings['sms_template_owner_order_received'] ?? '') ?></textarea>
            </div>

            <div style="margin-top:20px; padding:20px; border-radius:16px; background:#ffffff; border:1px solid #e9e9e9;">
                <h3 style="margin:0 0 14px;">Order Email Content</h3>
                <p style="font-size:12px; color:#777; margin:0 0 16px;">Customize the email body text shown inside the email template. Leave any field blank to use the default message.</p>

                <div style="font-size:12px; color:#777; margin:0 0 16px;">
                    Available placeholders:
                    <code>{shop_name}</code>,
                    <code>{customer_name}</code>,
                    <code>{order_number}</code>,
                    <code>{currency}</code>,
                    <code>{total_amount}</code>,
                    <code>{payment_status}</code>,
                    <code>{order_status}</code>,
                    <code>{payment_method}</code>,
                    <code>{courier_service}</code>,
                    <code>{tracking_number}</code>,
                    <code>{shop_whatsapp}</code>,
                    <code>{website_url}</code>,
                    <code>{customer_email}</code>,
                    <code>{customer_phone}</code>,
                    <code>{customer_address}</code>
                </div>

                <h4 style="margin:0 0 12px;">Customer Email Bodies</h4>
                <label class="label">Order Placed</label>
                <textarea name="email_customer_template_order_placed" class="input-box" rows="3" placeholder="Your order has been created successfully and is now in our system."><?= htmlspecialchars($settings['email_customer_template_order_placed'] ?? '') ?></textarea>
                <label class="label">Payment Completed</label>
                <textarea name="email_customer_template_payment_completed" class="input-box" rows="3" placeholder="Your payment was completed successfully. We can now process your order."><?= htmlspecialchars($settings['email_customer_template_payment_completed'] ?? '') ?></textarea>
                <label class="label">Payment Cancelled</label>
                <textarea name="email_customer_template_payment_cancelled" class="input-box" rows="3" placeholder="The payment for your order was cancelled."><?= htmlspecialchars($settings['email_customer_template_payment_cancelled'] ?? '') ?></textarea>
                <label class="label">Payment Failed</label>
                <textarea name="email_customer_template_payment_failed" class="input-box" rows="3" placeholder="We could not confirm payment for your order."><?= htmlspecialchars($settings['email_customer_template_payment_failed'] ?? '') ?></textarea>
                <label class="label">COD Payment Received</label>
                <textarea name="email_customer_template_payment_received" class="input-box" rows="3" placeholder="We have marked your order payment as received."><?= htmlspecialchars($settings['email_customer_template_payment_received'] ?? '') ?></textarea>
                <label class="label">Order Completed</label>
                <textarea name="email_customer_template_order_completed" class="input-box" rows="3" placeholder="Your order has been marked as completed. Courier: {courier_service}. Tracking Number: {tracking_number}."><?= htmlspecialchars($settings['email_customer_template_order_completed'] ?? '') ?></textarea>
                <label class="label">Order Cancelled</label>
                <textarea name="email_customer_template_order_cancelled" class="input-box" rows="3" placeholder="Your order has been cancelled."><?= htmlspecialchars($settings['email_customer_template_order_cancelled'] ?? '') ?></textarea>

                <h4 style="margin:24px 0 12px;">Shop Owner Email Bodies</h4>
                <label class="label">Order Placed</label>
                <textarea name="email_owner_template_order_placed" class="input-box" rows="3" placeholder="A new order has just been placed in your shop."><?= htmlspecialchars($settings['email_owner_template_order_placed'] ?? '') ?></textarea>
                <label class="label">Payment Completed</label>
                <textarea name="email_owner_template_payment_completed" class="input-box" rows="3" placeholder="A payment has been completed for an order in your shop."><?= htmlspecialchars($settings['email_owner_template_payment_completed'] ?? '') ?></textarea>
                <label class="label">Payment Cancelled</label>
                <textarea name="email_owner_template_payment_cancelled" class="input-box" rows="3" placeholder="A customer payment was cancelled."><?= htmlspecialchars($settings['email_owner_template_payment_cancelled'] ?? '') ?></textarea>
                <label class="label">Payment Failed</label>
                <textarea name="email_owner_template_payment_failed" class="input-box" rows="3" placeholder="A customer payment failed and needs attention."><?= htmlspecialchars($settings['email_owner_template_payment_failed'] ?? '') ?></textarea>
                <label class="label">COD Payment Received</label>
                <textarea name="email_owner_template_payment_received" class="input-box" rows="3" placeholder="Cash on delivery payment has been marked as received."><?= htmlspecialchars($settings['email_owner_template_payment_received'] ?? '') ?></textarea>
                <label class="label">Order Completed</label>
                <textarea name="email_owner_template_order_completed" class="input-box" rows="3" placeholder="An order has been marked as completed. Courier: {courier_service}. Tracking Number: {tracking_number}."><?= htmlspecialchars($settings['email_owner_template_order_completed'] ?? '') ?></textarea>
                <label class="label">Order Cancelled</label>
                <textarea name="email_owner_template_order_cancelled" class="input-box" rows="3" placeholder="An order has been cancelled."><?= htmlspecialchars($settings['email_owner_template_order_cancelled'] ?? '') ?></textarea>
            </div>

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
