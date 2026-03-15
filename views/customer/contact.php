<?php
$hide_mobile_welcome = true;
require_once ROOT_PATH . 'helpers/ImageHelper.php';
require_once 'helpers/SeoHelper.php';
require_once 'views/layouts/customer_header.php';

$shopName = trim((string) ($settings['shop_name'] ?? 'Our Shop'));
$shopUrl = trim((string) ($settings['shop_url'] ?? ''));
$shopAbout = trim((string) ($settings['shop_about'] ?? ''));
$shopWhatsapp = trim((string) ($settings['shop_whatsapp'] ?? ''));
$shopEmail = trim((string) ($settings['smtp_from_email'] ?? ''));
$shopLogo = ImageHelper::settingsImageUrl($settings['shop_logo'] ?? '', 'https://via.placeholder.com/160');
$shopLogoFile = basename((string) parse_url($shopLogo, PHP_URL_PATH));
$shopLink = $shopUrl !== '' ? SeoHelper::normalizeExternalUrl($shopUrl) : '';
$whatsAppDigits = preg_replace('/[^0-9]/', '', $shopWhatsapp);
$whatsAppLink = $whatsAppDigits !== '' ? 'https://wa.me/' . $whatsAppDigits : '';
$emailLink = $shopEmail !== '' ? 'mailto:' . $shopEmail : '';
?>

<div class="home-layout">
    <?php include 'views/customer/partials/sidebar.php'; ?>

    <main class="main-content" style="padding-top:0;">
        <div class="d-lg-none" style="padding: 18px 20px 28px;">
            <div style="font-size:11px; color:#888; margin-bottom:14px;">Home > Contact Us</div>

            <div style="display:flex; align-items:center; gap:14px; margin-bottom:22px;">
                <a href="javascript:history.back()" style="width:40px; height:40px; border-radius:50%; background:#000; color:#fff; text-decoration:none; display:flex; align-items:center; justify-content:center;">
                    <i class="fas fa-chevron-left" style="font-size:15px;"></i>
                </a>
                <div>
                    <h1 style="font-size:24px; font-weight:800; margin:0; color:#111; line-height:1.1;">Contact Us</h1>
                    <p style="font-size:12px; color:#7a7a7a; margin:4px 0 0;">Let customers reach you quickly and confidently.</p>
                </div>
            </div>

            <div style="background:#fff; border-radius:24px; padding:22px; box-shadow:0 12px 24px rgba(0,0,0,0.06); margin-bottom:18px; text-align:center;">
                <?= ImageHelper::renderResponsivePicture(
                    $shopLogoFile,
                    $shopLogo,
                    [
                        'alt' => $shopName,
                        'loading' => 'eager',
                        'decoding' => 'async',
                        'fetchpriority' => 'high',
                        'style' => 'width:88px; height:88px; border-radius:50%; object-fit:cover; margin:0 auto 14px; display:block;'
                    ],
                    'logo'
                ) ?>
                <h2 style="margin:0 0 10px; font-size:22px; color:#111;"><?= htmlspecialchars($shopName) ?></h2>
                <?php if ($shopAbout !== ''): ?>
                    <p style="margin:0; font-size:14px; color:#666; line-height:1.7;"><?= nl2br(htmlspecialchars($shopAbout)) ?></p>
                <?php endif; ?>
            </div>

            <div style="display:grid; gap:14px;">
                <div style="background:#fff; border-radius:20px; padding:18px; box-shadow:0 10px 22px rgba(0,0,0,0.05);">
                    <div style="font-size:11px; font-weight:800; color:#888; text-transform:uppercase; letter-spacing:0.08em; margin-bottom:8px;">Shop Link</div>
                    <?php if ($shopLink !== ''): ?>
                        <a href="<?= htmlspecialchars($shopLink) ?>" target="_blank" rel="noopener noreferrer" style="font-size:15px; color:#111; font-weight:700; word-break:break-word; text-decoration:none;">
                            <?= htmlspecialchars($shopUrl) ?>
                        </a>
                    <?php else: ?>
                        <div style="font-size:14px; color:#999;">Not added yet</div>
                    <?php endif; ?>
                </div>

                <div style="background:#fff; border-radius:20px; padding:18px; box-shadow:0 10px 22px rgba(0,0,0,0.05);">
                    <div style="font-size:11px; font-weight:800; color:#888; text-transform:uppercase; letter-spacing:0.08em; margin-bottom:8px;">WhatsApp</div>
                    <?php if ($shopWhatsapp !== ''): ?>
                        <a href="<?= htmlspecialchars($whatsAppLink) ?>" target="_blank" rel="noopener noreferrer" style="font-size:15px; color:#111; font-weight:700; text-decoration:none;">
                            <?= htmlspecialchars($shopWhatsapp) ?>
                        </a>
                    <?php else: ?>
                        <div style="font-size:14px; color:#999;">Not added yet</div>
                    <?php endif; ?>
                </div>

                <div style="background:#fff; border-radius:20px; padding:18px; box-shadow:0 10px 22px rgba(0,0,0,0.05);">
                    <div style="font-size:11px; font-weight:800; color:#888; text-transform:uppercase; letter-spacing:0.08em; margin-bottom:8px;">Email</div>
                    <?php if ($shopEmail !== ''): ?>
                        <a href="<?= htmlspecialchars($emailLink) ?>" style="font-size:15px; color:#111; font-weight:700; word-break:break-word; text-decoration:none;">
                            <?= htmlspecialchars($shopEmail) ?>
                        </a>
                    <?php else: ?>
                        <div style="font-size:14px; color:#999;">Not added yet</div>
                    <?php endif; ?>
                </div>
            </div>

            <div style="display:grid; gap:12px; margin-top:20px;">
                <?php if ($whatsAppLink !== ''): ?>
                    <a href="<?= htmlspecialchars($whatsAppLink) ?>" target="_blank" rel="noopener noreferrer" style="display:flex; align-items:center; justify-content:center; min-height:54px; border-radius:16px; background:#25d366; color:#fff; font-weight:800; text-decoration:none;">
                        Chat on WhatsApp
                    </a>
                <?php endif; ?>
                <?php if ($shopLink !== ''): ?>
                    <a href="<?= htmlspecialchars($shopLink) ?>" target="_blank" rel="noopener noreferrer" style="display:flex; align-items:center; justify-content:center; min-height:54px; border-radius:16px; background:#111; color:#fff; font-weight:800; text-decoration:none;">
                        Visit Website
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="d-none d-lg-block contact-desktop-page">
            <section class="contact-desktop-hero">
                <div class="contact-desktop-copy">
                    <div class="contact-desktop-eyebrow">Contact & Support</div>
                    <h1>Let customers reach your store in seconds.</h1>
                    <p><?= $shopAbout !== '' ? nl2br(htmlspecialchars($shopAbout)) : 'Share your shop link, WhatsApp, and email so customers can easily contact you.' ?></p>
                    <div class="contact-desktop-actions">
                        <?php if ($whatsAppLink !== ''): ?>
                            <a href="<?= htmlspecialchars($whatsAppLink) ?>" target="_blank" rel="noopener noreferrer" class="contact-desktop-cta contact-whatsapp">
                                WhatsApp Us
                            </a>
                        <?php endif; ?>
                        <?php if ($emailLink !== ''): ?>
                            <a href="<?= htmlspecialchars($emailLink) ?>" class="contact-desktop-cta contact-email">
                                Send Email
                            </a>
                        <?php endif; ?>
                        <?php if ($shopLink !== ''): ?>
                            <a href="<?= htmlspecialchars($shopLink) ?>" target="_blank" rel="noopener noreferrer" class="contact-desktop-secondary">
                                Visit Website
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="contact-desktop-brand">
                    <?= ImageHelper::renderResponsivePicture(
                        $shopLogoFile,
                        $shopLogo,
                        [
                            'alt' => $shopName,
                            'loading' => 'eager',
                            'decoding' => 'async',
                            'fetchpriority' => 'high',
                            'class' => 'contact-desktop-logo'
                        ],
                        'logo'
                    ) ?>
                    <strong><?= htmlspecialchars($shopName) ?></strong>
                    <?php if ($shopLink !== ''): ?>
                        <a href="<?= htmlspecialchars($shopLink) ?>" target="_blank" rel="noopener noreferrer"><?= htmlspecialchars($shopUrl) ?></a>
                    <?php endif; ?>
                </div>
            </section>

            <section class="contact-desktop-grid">
                <article class="contact-detail-card">
                    <span class="contact-detail-label">Shop Name</span>
                    <h2><?= htmlspecialchars($shopName) ?></h2>
                </article>

                <article class="contact-detail-card">
                    <span class="contact-detail-label">Shop Link</span>
                    <?php if ($shopLink !== ''): ?>
                        <a href="<?= htmlspecialchars($shopLink) ?>" target="_blank" rel="noopener noreferrer" class="contact-detail-link">
                            <?= htmlspecialchars($shopUrl) ?>
                        </a>
                    <?php else: ?>
                        <div class="contact-detail-muted">Not added yet</div>
                    <?php endif; ?>
                </article>

                <article class="contact-detail-card">
                    <span class="contact-detail-label">Shop Owner's WhatsApp</span>
                    <?php if ($shopWhatsapp !== ''): ?>
                        <a href="<?= htmlspecialchars($whatsAppLink) ?>" target="_blank" rel="noopener noreferrer" class="contact-detail-link">
                            <?= htmlspecialchars($shopWhatsapp) ?>
                        </a>
                    <?php else: ?>
                        <div class="contact-detail-muted">Not added yet</div>
                    <?php endif; ?>
                </article>

                <article class="contact-detail-card">
                    <span class="contact-detail-label">From Email</span>
                    <?php if ($shopEmail !== ''): ?>
                        <a href="<?= htmlspecialchars($emailLink) ?>" class="contact-detail-link">
                            <?= htmlspecialchars($shopEmail) ?>
                        </a>
                    <?php else: ?>
                        <div class="contact-detail-muted">Not added yet</div>
                    <?php endif; ?>
                </article>
            </section>
        </div>
    </main>
</div>

<?php require_once 'views/layouts/customer_footer.php'; ?>
