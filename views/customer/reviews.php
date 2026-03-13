<?php
// Hide Default Mobile Header
$hide_mobile_welcome = true;
require_once 'views/layouts/customer_header.php';
?>

<div class="home-layout">

    <!-- Desktop Sidebar -->
    <?php include 'views/customer/partials/sidebar.php'; ?>

    <main class="main-content" style="padding-top: 0;">

        <!-- Mobile-Only Layout Wrapper -->
                <!-- Mobile-Only Layout Wrapper (REORDERED) -->
        <div class="reviews-page-mobile d-lg-none">
            
            <!-- 1. Top Header Area (Matches Figma) -->
            <div style="padding: 20px 20px 10px 20px;">
                <div style="font-size: 11px; color: #888; margin-bottom: 15px;">Home > Reviews</div>
                
                <div style="display: flex; align-items: center; gap: 15px;">
                    <!-- Back Button -->
                    <a href="javascript:history.back()" style="
                        width: 40px; 
                        height: 40px; 
                        background: #000; 
                        border-radius: 50%; 
                        display: flex; 
                        align-items: center; 
                        justify-content: center; 
                        color: white; 
                        text-decoration: none;
                        flex-shrink: 0;
                    ">
                        <i class="fas fa-chevron-left" style="font-size: 16px;"></i>
                    </a>
                    
                    <!-- Title Text -->
                    <div>
                        <h1 style="font-size: 22px; font-weight: 800; margin: 0; color: #000; line-height: 1.2;">Customer Feedbacks</h1>
                        <p style="font-size: 12px; color: #888; margin: 2px 0 0 0;">We are always try to make to fully satisfied!</p>
                    </div>
                </div>
            </div>

            <!-- 2. Feedback Images Scroll (MOVED TO TOP) -->
            <div style="padding: 0 0 0 20px; margin-top: 20px; margin-bottom: 40px;">
                <div class="feedback-scroll" style="
                    display: flex; 
                    overflow-x: auto; 
                    gap: 15px; 
                    padding-bottom: 20px; 
                    padding-right: 20px;
                    scroll-snap-type: x mandatory;
                    scrollbar-width: none;
                ">
                    <?php if (empty($feedbacks)): ?>
                        <div style="width:100%; text-align:center; padding: 20px; color:#aaa;">No feedback available yet.</div>
                    <?php else: ?>
                        <?php foreach ($feedbacks as $fb):
                            $fbPath = 'assets/uploads/' . $fb['image_path'];
                            $fbImg = (file_exists(ROOT_PATH . $fbPath)) ? BASE_URL . $fbPath : '';

                            if ($fbImg):
                                ?>
                                <div style="
                                flex: 0 0 85%; 
                                scroll-snap-align: center;
                                border-radius: 15px;
                                overflow: hidden;
                                box-shadow: 0 5px 15px rgba(0,0,0,0.1);
                                border: 1px solid #f0f0f0;
                                height: 450px; /* FIXED HEIGHT */
                                position: relative;
                            ">
                                    <img src="<?= $fbImg ?>" alt="Feedback" onclick="openImageModal(this.src)" style="
                                    width: 100%; 
                                    height: 100%; 
                                    object-fit: cover; /* Zoom/Crop to fill */
                                    object-position: top; /* Standardize alignment */
                                    display: block;
                                ">
                                </div>

                            <?php endif; endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- 3. Shop Info (MOVED TO BOTTOM) -->
            <div style="position: relative; margin-bottom: 30px; margin-top: 20px;">
                 <!-- Logo -->
                 <div style="display: flex; justify-content: center; position: relative; z-index: 10;">
                    <?php
                    $logoUrl = $settings['shop_logo'] ?? '';
                    $logoUrl = str_replace('/Ecom-CMS/', BASE_URL, $logoUrl);
                    $physicalPath = $_SERVER['DOCUMENT_ROOT'] . $logoUrl;
                    $logo = (!empty($logoUrl) && file_exists($physicalPath)) ? $logoUrl : 'https://via.placeholder.com/120';
                    ?>
                    <img src="<?= $logo ?>" alt="Shop Logo" style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 4px solid #fff; background: #fff; box-shadow: 0 5px 15px rgba(0,0,0,0.05);">
                </div>

                <!-- Gray Info Box -->
                <div style="
                    background: #f8f9fa; 
                    border-radius: 25px; 
                    padding: 70px 20px 30px 20px; 
                    margin: -60px 20px 0 20px; /* Overlap logic */
                    text-align: center;
                    position: relative;
                    z-index: 5;
                ">
                    <h2 style="font-size: 20px; font-weight: 800; margin: 0 0 10px 0; color: #000;">
                        <?= !empty($settings['shop_name']) ? htmlspecialchars($settings['shop_name']) : 'Dark Lavender Clothing' ?>
                    </h2>
                    <p style="font-size: 14px; color: #666; margin: 0 0 20px 0; line-height: 1.5;">
                        <?= !empty($settings['shop_about']) ? htmlspecialchars($settings['shop_about']) : 'Tailored to your tastes...' ?>
                    </p>
                    <div style="font-size: 13px; color: #333; line-height: 1.6;">
                        <div>No: 213/7, Ghanawimala Mw,<br>Hewagama, Kaduwela.</div>
                        <div style="margin-top: 5px;">076 260 00 00 / 077 255 55 55</div>
                        <div>info@darklavender.com</div>
                    </div>
                </div>
            </div>

                        <!-- 4. Review Button (Dynamic Smart Link) -->
            <div style="padding: 0 20px; margin-bottom: 30px;">
                <?php 
                    // 1. Get the specific 'review_link' setting
                    $reviewUrl = isset($settings['review_link']) ? $settings['review_link'] : '#';
                    
                    // 2. Smart Check: Is it a URL or a Number?
                    if ($reviewUrl !== '#' && strpos($reviewUrl, 'http') !== 0) {
                        // It's a Number -> Convert to WhatsApp Link
                        $reviewUrl = 'https://wa.me/' . str_replace(['+', ' '], '', $reviewUrl) . '?text=I%20would%20like%20to%20leave%20a%20review!';
                    }
                    // Else: It operates as a normal link (e.g. Google Reviews)
                ?>
                <a href="<?= $reviewUrl ?>"
                    target="_blank" style="display: block; width: 100%; background: #50d176; color: white; text-align: center; padding: 18px; border-radius: 15px; font-weight: 700; text-decoration: none; box-shadow: 0 4px 15px rgba(80, 209, 118, 0.3); font-size: 16px;">
                    Give us a Review!
                </a>
            </div>


                        <!-- 5. Social Icons (Dynamic) -->
            <div style="display: flex; justify-content: center; gap: 20px; margin-bottom: 50px;">
                <!-- Facebook -->
                <a href="<?= !empty($settings['social_fb']) ? $settings['social_fb'] : '#' ?>" target="_blank">
                    <img src="<?= BASE_URL ?>assets/icons/facebook.png" style="width: 45px;">
                </a>
                <!-- Tiktok -->
                <a href="<?= !empty($settings['social_tiktok']) ? $settings['social_tiktok'] : '#' ?>" target="_blank">
                    <img src="<?= BASE_URL ?>assets/icons/tiktok.png" style="width: 45px;">
                </a>
                <!-- Instagram -->
                <a href="<?= !empty($settings['social_insta']) ? $settings['social_insta'] : '#' ?>" target="_blank">
                    <img src="<?= BASE_URL ?>assets/icons/instagram.png" style="width: 45px;">
                </a>
                <!-- Youtube -->
                <a href="<?= !empty($settings['social_youtube']) ? $settings['social_youtube'] : '#' ?>" target="_blank">
                    <img src="<?= BASE_URL ?>assets/icons/youtube.png" style="width: 45px;">
                </a>
                <!-- WhatsApp -->
                <?php 
                    $waLink = isset($settings['social_whatsapp']) ? $settings['social_whatsapp'] : '#';
                    
                    // Safe Check: If not empty, not '#', and does NOT start with http
                    if ($waLink !== '#' && strpos($waLink, 'http') !== 0) {
                        // It's a number, so add wa.me prefix
                        $waLink = 'https://wa.me/' . str_replace(['+', ' '], '', $waLink);
                    }
                ?>
                <a href="<?= $waLink ?>" target="_blank">
                    <img src="<?= BASE_URL ?>assets/icons/whatsapp.png" style="width: 45px;"> 
                </a>

                


            </div>




        <!-- Desktop View Fallback -->
        <div class="d-none d-lg-block">
            <div class="section-header">
                <h2 class="section-title">Customer Reviews</h2>
            </div>
            <div class="shop-grid" style="grid-template-columns: repeat(4, 1fr);">
                <?php if (!empty($feedbacks)):
                    foreach ($feedbacks as $fb):
                        $fbPath = 'assets/uploads/' . $fb['image_path'];
                        if (file_exists(ROOT_PATH . $fbPath)):
                            ?>
                            <div style="border-radius: 10px; overflow: hidden; border: 1px solid #eee;">
                                <img src="<?= BASE_URL . $fbPath ?>" style="width: 100%;">
                            </div>
                        <?php endif; endforeach; endif; ?>
            </div>
        </div>

    </main>
    <!-- Image Lightbox Modal (Same as Product Page) -->
<div id="imgModal" class="modal-overlay" onclick="closeImageModal()" 
     style="display: none; align-items: center; justify-content: center; z-index: 3000;">
    
    <!-- Image Wrapper (Relative for button positioning) -->
    <div onclick="event.stopPropagation()"
        style="position: relative; display: inline-block;">
        
        <!-- Close Button (Absolute Top-Right of Image) -->
        <div onclick="closeImageModal()"
            style="position: absolute; top: -15px; right: -15px; cursor: pointer; z-index: 3001; 
                   background: white; border-radius: 50%; width: 35px; height: 35px; 
                   display: flex; align-items: center; justify-content: center; 
                   box-shadow: 0 2px 10px rgba(0,0,0,0.2); border: 1px solid #eee;">
            <i class="fas fa-times" style="color: black; font-size: 16px;"></i>
        </div>

        <img id="imgModalSrc" src=""
            style="max-width: 85vw; max-height: 80vh; width: auto; height: auto; 
                   object-fit: contain; border-radius: 12px; display: block; background: #fff;">
    </div>
</div>

<script>
    function openImageModal(src) {
        document.getElementById('imgModalSrc').src = src;
        document.getElementById('imgModal').style.display = 'flex';
    }
    function closeImageModal() {
        document.getElementById('imgModal').style.display = 'none';
    }
</script>
</div>

<?php require_once 'views/layouts/customer_footer.php'; ?>