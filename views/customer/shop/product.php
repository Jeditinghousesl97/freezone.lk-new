<?php
// Hide Default Mobile Header for Single Product Page (Task 3.1)
$hide_mobile_welcome = true;
require_once ROOT_PATH . 'helpers/ImageHelper.php';
require_once 'views/layouts/customer_header.php';
$currency = $settings['currency_symbol'] ?? 'LKR';
$productUnitPrice = (!empty($product['sale_price']) && (float) $product['sale_price'] < (float) $product['price'])
    ? (float) $product['sale_price']
    : (float) $product['price'];
?>
<style>
    .var-pill.is-disabled {
        opacity: 0.35;
        cursor: not-allowed;
        pointer-events: none;
        filter: grayscale(0.15);
    }

    .stock-filter-actions {
        display: flex;
        justify-content: flex-end;
        margin: -4px 0 18px;
    }

    .stock-clear-btn {
        border: 1px solid #f2b26b;
        background: #ff9f43;
        color: #fff;
        border-radius: 999px;
        padding: 8px 14px;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
    }

    .stock-clear-btn:hover {
        border-color: #e28928;
        background: #f08f2e;
        color: #fff;
    }

    .lightbox-slider {
        display: flex;
        overflow-x: auto;
        scroll-snap-type: x mandatory;
        width: min(92vw, 720px);
        max-height: 82vh;
        border-radius: 18px;
        scrollbar-width: none;
        -ms-overflow-style: none;
        -webkit-overflow-scrolling: touch;
        touch-action: pan-x pinch-zoom;
        background: #fff;
    }

    .lightbox-slider::-webkit-scrollbar {
        display: none;
    }

    .lightbox-slide {
        min-width: 100%;
        width: 100%;
        scroll-snap-align: center;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fff;
    }

    .lightbox-slide img {
        width: 100%;
        max-height: 82vh;
        object-fit: contain;
        display: block;
        border-radius: 18px;
        user-select: none;
        -webkit-user-drag: none;
    }
</style>

<!-- Wrappers for Sidebar Layout -->
<div class="home-layout">

    <!-- Include Sidebar -->
    <?php include 'views/customer/partials/sidebar.php'; ?>

    <main class="main-content">

        <!-- Single Product View Styles  -->
        <div class="product-detail-page">

            <!-- Image Gallery Section -->
            <div class="product-gallery">
                <a href="javascript:history.back()" class="back-btn-overlay"
                    style="text-decoration: none; position: absolute; top: 10px; left: 10px; z-index: 10; width: 35px; height: 35px; background: rgba(0,0,0,0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-arrow-left" style="color: black; font-size: 16px;"></i>
                </a>

                <div style="position: relative;">
                    <button class="scroll-btn left d-lg-flex" onclick="scrollSection(this, -1)" style="display: none; position: absolute; top: 50%; left: 10px; transform: translateY(-50%); z-index: 10; 
                           width: 35px; height: 35px; border-radius: 50%; background: white; 
                           box-shadow: 0 2px 5px rgba(0,0,0,0.1); border: 1px solid #eee; 
                           cursor: pointer; align-items: center; justify-content: center;">
                        <i class="fas fa-chevron-left" style="color: black; font-size: 14px;"></i>
                    </button>
                    <div class="gallery-slider">
                        <!-- Main Image First -->
                        <?php
                        $mainImg = ImageHelper::uploadUrl(
                            $product['main_image'] ?? '',
                            'https://via.placeholder.com/600x600?text=' . urlencode($product['title'])
                        );
                        ?>
                        <?= ImageHelper::renderResponsivePicture(
                            $product['main_image'] ?? '',
                            $mainImg,
                            [
                                'class' => 'gallery-img current',
                                'alt' => $product['title'] ?? 'Product image',
                                'loading' => 'eager',
                                'decoding' => 'sync',
                                'fetchpriority' => 'high',
                                'data-index' => '0',
                                'onclick' => 'openImageModal(0)'
                            ],
                            'product_gallery'
                        ) ?>

                        <!-- Gallery Images -->
                        <?php if (!empty($gallery)): ?>
                            <?php foreach ($gallery as $galleryIndex => $gImg):
                                $gUrl = ImageHelper::uploadUrl($gImg, '');
                                if ($gUrl):
                                    ?>
                                    <?= ImageHelper::renderResponsivePicture(
                                        $gImg,
                                        $gUrl,
                                        [
                                            'class' => 'gallery-img',
                                            'alt' => ($product['title'] ?? 'Product') . ' gallery image',
                                            'loading' => 'lazy',
                                            'decoding' => 'async',
                                            'fetchpriority' => 'low',
                                            'data-index' => (string) ((int) $galleryIndex + 1),
                                            'onclick' => 'openImageModal(' . ((int) $galleryIndex + 1) . ')'
                                        ],
                                        'product_gallery'
                                    ) ?>
                                <?php endif; endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <button class="scroll-btn right d-lg-flex" onclick="scrollSection(this, 1)" style="display: none; position: absolute; top: 50%; right: 10px; transform: translateY(-50%); z-index: 10; 
                           width: 35px; height: 35px; border-radius: 50%; background: white; 
                           box-shadow: 0 2px 5px rgba(0,0,0,0.1); border: 1px solid #eee; 
                           cursor: pointer; align-items: center; justify-content: center;">
                        <i class="fas fa-chevron-right" style="color: black; font-size: 14px;"></i>
                    </button>
                </div>

                <!-- Pagination Dots  -->
                <div class="gallery-dots">
                    <span class="dot active"></span>
                    <?php if (!empty($gallery)): ?>
                        <?php foreach ($gallery as $g): ?>
                            <span class="dot"></span>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <!-- Description -->
                <div class="pd-description d-none d-lg-block">
                    <?= nl2br(htmlspecialchars($product['description'])) ?>


                </div>
            </div>

            <!-- Info Section -->
            <div class="product-info-container">

                <!-- Breadcrumb / Category -->
                <div class="pd-breadcrumb">
                    <?php
                    $catName = htmlspecialchars($product['category_name'] ?? '');
                    $parentName = htmlspecialchars($product['parent_category_name'] ?? '');
                    echo (!empty($parentName) ? $parentName . ' | ' : '') . $catName;
                    ?>
                </div>

                <!-- Title -->
                <h1 class="pd-title" style="text-align: left;">
                    <?= htmlspecialchars($product['title']) ?>
                </h1>
                <?php if (!empty($product['free_shipping'])): ?>
                    <div class="free-shipping-badge" style="margin: 0 0 10px 0; width: fit-content;">Free Shipping</div>
                <?php endif; ?>

                <!-- Price & Guide Row -->
                <div class="pd-price-row" style="justify-content: flex-start; gap: 20px;">
                    <div class="pd-prices" id="productPriceBox" style="font-weight: 700;">
                        <?php
                        if (!empty($product['sale_price']) && $product['sale_price'] < $product['price']):
                            ?>
                            <span class="pd-old-price" id="productOldPrice" style="font-weight: 400;">
                                <?= $currency ?>
                                <?= number_format($product['price'], 0) ?>
                            </span>
                            <span class="pd-sale-price" id="productCurrentPrice" style="font-weight: 800; color: #000;">
                                <?= $currency ?>
                                <?= number_format($product['sale_price'], 0) ?>
                            </span>
                        <?php else: ?>
                            <span class="pd-sale-price" id="productCurrentPrice" style="font-weight: 800; color: #000;">
                                <?= $currency ?>
                                <?= number_format($product['price'], 0) ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <?php
                    $sizeGuideImage = ImageHelper::uploadUrl($product['size_guide_image'] ?? '', '');
                    if ($sizeGuideImage):
                        ?>
                        <button class="btn-size-guide" onclick="openSizeGuide()">Size Guide</button>
                    <?php endif; ?>
                </div>

                <!-- Variations -->
                <?php if (!empty($variations)): ?>
                    <?php foreach ($variations as $varName => $values): ?>
                        <div class="var-section">
                            <span class="var-label">
                                <?= htmlspecialchars(ucfirst($varName)) ?>
                            </span>
                            <div class="var-pills">
                                <?php foreach ($values as $val): ?>
                                    <div class="var-pill"
                                        data-variation-id="<?= (int) ($val['variation_id'] ?? 0) ?>"
                                        data-variation-name="<?= htmlspecialchars($varName, ENT_QUOTES) ?>"
                                        data-value-id="<?= (int) $val['id'] ?>"
                                        data-value-label="<?= htmlspecialchars($val['value'], ENT_QUOTES) ?>"
                                        onclick="selectVariation(this)">
                                        <?= htmlspecialchars($val['value']) ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="stock-filter-actions">
                        <button type="button" id="clearStockFilterBtn" class="stock-clear-btn" onclick="clearVariationSelection()" style="display:none;">
                            Clear Filter
                        </button>
                    </div>
                <?php endif; ?>
                <div id="productStockNotice" style="display:none; margin: 0 0 18px; padding: 12px 14px; border-radius: 12px; font-size: 13px; font-weight: 700;"></div>


                <!-- Quantity Selector  -->
                <div class="pd-quantity"
                    style="margin-top: 20px; margin-bottom: 20px; display: flex; align-items: center; gap: 20px;">
                    <span style="font-weight: 600; font-size: 15px; color: #000;">Quantity :</span>
                    <div
                        style="display: flex; align-items: center; border: 1px solid #000; border-radius: 5px; background: #fff; height: 35px;">
                        <button type="button" onclick="updateQty(-1)"
                            style="border:none; border-right: 1px solid #000; background:transparent; width: 35px; height: 100%; font-size: 16px; cursor: pointer; color: #000; display: flex; align-items: center; justify-content: center;">-</button>
                        <input type="number" id="qtyInput" value="1" min="1" readonly
                            style="width: 40px; height: 100%; text-align: center; border: none; font-weight: 700; font-size: 14px; outline: none; color: #000; padding: 0;">
                        <button type="button" onclick="updateQty(1)"
                            style="border:none; border-left: 1px solid #000; background:transparent; width: 35px; height: 100%; font-size: 16px; cursor: pointer; color: #000; display: flex; align-items: center; justify-content: center;">+</button>
                    </div>
                </div>
                <!-- Mobile Only Description (Moved Here) -->
                <div class="pd-description d-lg-none" style="margin-top: 20px; margin-bottom: 20px;">
                    <?= nl2br(htmlspecialchars($product['description'])) ?>
                </div>






                <!-- Bottom Actions -->
                <div class="pd-bottom-actions">
                    <?php
                    $codEnabled = !empty($settings['cod_enabled']);
                    $shopWhatsappTarget = preg_replace('/[^0-9]/', '', (string) ($settings['shop_whatsapp'] ?? ''));
                    if ($shopWhatsappTarget === '') {
                        $shopWhatsappTarget = preg_replace('/[^0-9]/', '', (string) ($settings['social_whatsapp'] ?? ''));
                    }
                    $whatsappEnabled = !empty($settings['whatsapp_ordering_enabled']) && $shopWhatsappTarget !== '';
                    ?>
                    <button class="btn-action btn-order-now" onclick="openPaymentMethodSheet()">
                        <i class="fas fa-bag-shopping"></i>
                        <span class="btn-action-label">Order Now</span>
                    </button>

                    <button class="btn-action btn-cart" onclick="addToCartFromProductPage()">
                        <i class="fas fa-cart-plus"></i>
                        <span class="btn-action-label">Add to cart</span>
                    </button>
                </div>

            </div>
        </div>

        <!-- You May Also Like Section -->
        <?php if (!empty($relatedProducts)): ?>
            <div style="margin-top: 50px; border-top: 1px solid #eee; padding-top: 30px;">
                <h3 style="margin-bottom: 20px;">You May Also Like...</h3>
                <div style="position: relative;">
                    <button class="scroll-btn left d-lg-flex" onclick="scrollSection(this, -1)" style="display: none; position: absolute; top: 50%; left: -15px; transform: translateY(-50%); z-index: 10; 
                       width: 35px; height: 35px; border-radius: 50%; background: white; 
                       box-shadow: 0 2px 5px rgba(0,0,0,0.1); border: 1px solid #eee; 
                       cursor: pointer; align-items: center; justify-content: center;">
                        <i class="fas fa-chevron-left" style="color: black; font-size: 14px;"></i>
                    </button>
                    <div class="products-scroll" style="display:flex; overflow-x:auto; gap:15px; padding-bottom:10px;">
                        <?php foreach ($relatedProducts as $prod): ?>
                            <?php include 'views/customer/partials/product_card.php'; ?>
                        <?php endforeach; ?>
                    </div>
                    <button class="scroll-btn right d-lg-flex" onclick="scrollSection(this, 1)" style="display: none; position: absolute; top: 50%; right: -15px; transform: translateY(-50%); z-index: 10; 
                       width: 35px; height: 35px; border-radius: 50%; background: white; 
                       box-shadow: 0 2px 5px rgba(0,0,0,0.1); border: 1px solid #eee; 
                       cursor: pointer; align-items: center; justify-content: center;">
                        <i class="fas fa-chevron-right" style="color: black; font-size: 14px;"></i>
                    </button>
                </div>
            </div>
        <?php endif; ?>

    </main>
</div>
<!-- End Wrappers -->

<!-- Size Guide Modal (Basic) -->
<?php
$sgImg = ImageHelper::uploadUrl($product['size_guide_image'] ?? '', '');
if ($sgImg):
    ?>
    <div id="sgModal" class="modal-overlay" onclick="closeSizeGuide()" style="display: none;">
        <div class="modal-content" onclick="event.stopPropagation()" style="position: relative; padding: 0;">
            <div onclick="closeSizeGuide()"
                style="position: absolute; top: 10px; right: 10px; cursor: pointer; z-index: 100; background: rgba(255,255,255,0.7); border-radius: 50%; padding: 5px; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;">
                <img src="<?= BASE_URL ?>assets/icons/delete.png" alt="Close" style="width: 15px; height: 15px;">
            </div>
            <?= ImageHelper::renderResponsivePicture(
                $product['size_guide_image'] ?? '',
                $sgImg,
                [
                    'alt' => 'Size guide',
                    'loading' => 'lazy',
                    'decoding' => 'async',
                    'fetchpriority' => 'low',
                    'style' => 'width:100%; border-radius:10px; display: block;'
                ],
                'product_gallery'
            ) ?>
        </div>
    </div>
    </script>
<?php endif; ?>

<!-- Image Lightbox Modal (Refined: Corner Button) -->
<div id="imgModal" class="modal-overlay" onclick="closeImageModal()"
    style="display: none; align-items: center; justify-content: center; z-index: 3000;">

    <!-- Image Wrapper (Relative for button positioning) -->
    <div onclick="event.stopPropagation()" style="position: relative; display: inline-block; width: min(92vw, 720px);">

        <!-- Close Button (Absolute Top-Right of Image) -->
        <div onclick="closeImageModal()" style="position: absolute; top: -15px; right: -15px; cursor: pointer; z-index: 3001; 
                   background: white; border-radius: 50%; width: 35px; height: 35px; 
                   display: flex; align-items: center; justify-content: center; 
                   box-shadow: 0 2px 10px rgba(0,0,0,0.2); border: 1px solid #eee;">
            <i class="fas fa-times" style="color: black; font-size: 16px;"></i>
        </div>

        <button type="button" onclick="moveImageModal(-1)" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); z-index:3001; width:38px; height:38px; border:none; border-radius:50%; background:rgba(255,255,255,0.92); box-shadow:0 2px 10px rgba(0,0,0,0.18); cursor:pointer; display:flex; align-items:center; justify-content:center;">
            <i class="fas fa-chevron-left" style="color:#111; font-size:14px;"></i>
        </button>

        <div id="imgModalSlider" class="lightbox-slider"></div>

        <button type="button" onclick="moveImageModal(1)" style="position:absolute; right:12px; top:50%; transform:translateY(-50%); z-index:3001; width:38px; height:38px; border:none; border-radius:50%; background:rgba(255,255,255,0.92); box-shadow:0 2px 10px rgba(0,0,0,0.18); cursor:pointer; display:flex; align-items:center; justify-content:center;">
            <i class="fas fa-chevron-right" style="color:#111; font-size:14px;"></i>
        </button>
    </div>
</div>

<script>
    let modalImageIndex = 0;

    function getGalleryImages() {
        return Array.from(document.querySelectorAll('.gallery-slider .gallery-img')).map(function (img) {
            return img.getAttribute('src');
        }).filter(Boolean);
    }

    function renderImageModalSlides() {
        const slider = document.getElementById('imgModalSlider');
        if (!slider) {
            return;
        }

        const images = getGalleryImages();
        slider.innerHTML = images.map(function (src, index) {
            return '<div class="lightbox-slide"><img src="' + src + '" alt="Product image ' + (index + 1) + '"></div>';
        }).join('');
    }

    function syncModalImagePosition(index, behavior) {
        const slider = document.getElementById('imgModalSlider');
        const images = getGalleryImages();
        if (!slider || !images.length) {
            return;
        }

        modalImageIndex = Math.max(0, Math.min(index, images.length - 1));
        slider.scrollTo({
            left: slider.clientWidth * modalImageIndex,
            behavior: behavior || 'smooth'
        });
    }

    function openImageModal(index) {
        renderImageModalSlides();
        document.getElementById('imgModal').style.display = 'flex';
        syncModalImagePosition(Number(index || 0), 'auto');
    }

    function closeImageModal() {
        document.getElementById('imgModal').style.display = 'none';
    }

    function moveImageModal(direction) {
        syncModalImagePosition(modalImageIndex + direction, 'smooth');
    }
    // Size Guide Modal Logic (Fix Task 2)
    function openSizeGuide() {
        document.getElementById('sgModal').style.display = 'flex';
    }
    function closeSizeGuide() {
        document.getElementById('sgModal').style.display = 'none';
    }
    // Carousel Pagination Logic 
    document.addEventListener('DOMContentLoaded', () => {
        const slider = document.querySelector('.gallery-slider');
        const dots = document.querySelectorAll('.gallery-dots .dot');
        const modalSlider = document.getElementById('imgModalSlider');

        if (slider && dots.length > 0) {
            slider.addEventListener('scroll', () => {
                const scrollLeft = slider.scrollLeft;
                const width = slider.offsetWidth;
                // Calculate index: round(scroll / width)
                const index = Math.round(scrollLeft / width);

                // Update active class
                dots.forEach((dot, i) => {
                    if (i === index) dot.classList.add('active');
                    else dot.classList.remove('active');
                });
            });
        }

        if (slider) {
            slider.style.webkitOverflowScrolling = 'touch';
            slider.style.touchAction = 'auto';
        }

        if (modalSlider) {
            modalSlider.addEventListener('scroll', function () {
                const width = modalSlider.offsetWidth || 1;
                modalImageIndex = Math.round(modalSlider.scrollLeft / width);
            });
        }
    });

</script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const sliders = document.querySelectorAll('.gallery-slider, .products-scroll');

        sliders.forEach(slider => {
            const wrapper = slider.parentElement;
            const btnLeft = wrapper.querySelector('.scroll-btn.left');
            const btnRight = wrapper.querySelector('.scroll-btn.right');
            const isGallerySlider = slider.classList.contains('gallery-slider');

            // --- 1. Smart Buttons Visibility (Desktop Only) ---
            const updateButtons = () => {
                // Determine if we are on Desktop (approx > 1024px)
                if (window.innerWidth < 1024) return;

                const tolerance = 5;

                // Left Button
                if (slider.scrollLeft <= tolerance) {
                    btnLeft.style.setProperty('display', 'none', 'important');
                } else {
                    btnLeft.style.removeProperty('display'); // Revert to CSS (d-lg-flex)
                }

                // Right Button
                if (slider.scrollLeft + slider.clientWidth >= slider.scrollWidth - tolerance) {
                    btnRight.style.setProperty('display', 'none', 'important');
                } else {
                    btnRight.style.removeProperty('display');
                }
            };

            // Init & Listen
            updateButtons();
            slider.addEventListener('scroll', updateButtons);
            window.addEventListener('resize', updateButtons);


            // --- 2. Mouse Wheel Horizontal Scroll ---
            slider.addEventListener('wheel', (evt) => {
                if (window.innerWidth >= 1024 && !isGallerySlider) {
                    evt.preventDefault();
                    slider.scrollLeft += evt.deltaY;
                } else if (window.innerWidth >= 1024 && isGallerySlider) {
                    const horizontalDelta = Math.abs(evt.deltaX) > Math.abs(evt.deltaY) ? evt.deltaX : 0;

                    if (evt.shiftKey || horizontalDelta !== 0) {
                        evt.preventDefault();
                        slider.scrollLeft += horizontalDelta !== 0 ? horizontalDelta : evt.deltaY;
                    }
                }
            });


            // --- 3. Drag to Scroll (Mouse Grab) ---
            let isDown = false;
            let startX;
            let scrollLeft;

            slider.addEventListener('mousedown', (e) => {
                if (window.innerWidth < 1024) return;
                isDown = true;
                slider.style.cursor = 'grabbing';
                startX = e.pageX - slider.offsetLeft;
                scrollLeft = slider.scrollLeft;
            });

            slider.addEventListener('mouseleave', () => {
                isDown = false;
                slider.style.cursor = 'grab';
            });

            slider.addEventListener('mouseup', () => {
                isDown = false;
                slider.style.cursor = 'grab';
            });

            slider.addEventListener('mousemove', (e) => {
                if (!isDown) return;
                e.preventDefault();
                const x = e.pageX - slider.offsetLeft;
                const walk = (x - startX) * 2;
                slider.scrollLeft = scrollLeft - walk;
            });

            // Set initial cursor
            if (window.innerWidth >= 1024) {
                slider.style.cursor = 'grab';
            }
        });
    });

    // Button Click Helper
    function scrollSection(btn, direction) {
        var container = btn.parentElement.querySelector('.categories-scroll, .products-scroll, .gallery-slider');
        if (container) {
            container.scrollBy({
                left: direction * 300,
                behavior: 'smooth'
            });
        }
    }
</script>

<div id="paymentMethodSheet" class="payment-sheet-overlay" style="display: none;" onclick="closePaymentMethodSheet()">
    <div class="payment-sheet" onclick="event.stopPropagation()">
        <div class="payment-sheet-handle"></div>
        <div class="payment-sheet-header">
            <div>
                <div class="payment-sheet-eyebrow">Choose Payment Method</div>
                <h3>Select how you want to order</h3>
            </div>
            <button type="button" class="payment-sheet-close" onclick="closePaymentMethodSheet()">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="payment-sheet-options">
            <?php if ($whatsappEnabled): ?>
                <button type="button" class="payment-method-card method-whatsapp" onclick="choosePaymentMethod('whatsapp')">
                    <span class="payment-method-icon"><i class="fab fa-whatsapp"></i></span>
                    <span class="payment-method-copy">
                        <strong>WhatsApp Order</strong>
                        <small>Send your order details directly to the shop on WhatsApp.</small>
                    </span>
                    <span class="payment-method-arrow"><i class="fas fa-chevron-right"></i></span>
                </button>
            <?php endif; ?>

            <?php if ($codEnabled): ?>
                <button type="button" class="payment-method-card method-cod" onclick="choosePaymentMethod('cod')">
                    <span class="payment-method-icon">
                        <img src="<?= BASE_URL ?>assets/icons/payment-gateways/buttons/cod.png" alt="Cash on Delivery" class="payment-method-logo">
                    </span>
                    <span class="payment-method-copy">
                        <strong>Cash on Delivery</strong>
                        <small>Place the order now and pay when it is delivered.</small>
                    </span>
                    <span class="payment-method-arrow"><i class="fas fa-chevron-right"></i></span>
                </button>
            <?php endif; ?>

            <?php if (!empty($settings['payhere_enabled'])): ?>
                <button type="button" class="payment-method-card method-payhere" onclick="choosePaymentMethod('payhere')">
                    <span class="payment-method-icon">
                        <img src="<?= BASE_URL ?>assets/icons/payment-gateways/buttons/payhere.png" alt="PayHere" class="payment-method-logo">
                    </span>
                    <span class="payment-method-copy">
                        <strong>Card Payment</strong>
                        <small>Pay online securely before your order is confirmed.</small>
                    </span>
                    <span class="payment-method-arrow"><i class="fas fa-chevron-right"></i></span>
                </button>
            <?php endif; ?>

            <?php if (!empty($settings['koko_enabled'])): ?>
                <button type="button" class="payment-method-card method-koko" onclick="choosePaymentMethod('koko')">
                    <span class="payment-method-icon">
                        <img src="<?= BASE_URL ?>assets/icons/payment-gateways/buttons/koko.png" alt="KOKO" class="payment-method-logo">
                    </span>
                    <span class="payment-method-copy">
                        <strong>KOKO Pay in 3</strong>
                        <small>Split your payment into 3 interest-free installments.</small>
                    </span>
                    <span class="payment-method-arrow"><i class="fas fa-chevron-right"></i></span>
                </button>
            <?php endif; ?>

            <?php if (!empty($settings['bank_transfer_enabled'])): ?>
                <button type="button" class="payment-method-card method-bank" onclick="choosePaymentMethod('bank_transfer')">
                    <span class="payment-method-icon">
                        <img src="<?= BASE_URL ?>assets/icons/payment-gateways/buttons/bank.png" alt="Bank Transfer" class="payment-method-logo">
                    </span>
                    <span class="payment-method-copy">
                        <strong>Bank Transfer</strong>
                        <small>Place the order now and send the payment using the bank details provided.</small>
                    </span>
                    <span class="payment-method-arrow"><i class="fas fa-chevron-right"></i></span>
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>


<!-- Order Form Modal -->
<div id="orderModal" class="modal-overlay" style="display: none;">
    <div class="modal-content"
        style="max-width: 500px; width: 90%; max-height: 90vh; overflow-y: auto; padding: 25px; border-radius: 15px;">
        <h3 style="margin-top: 0; font-size: 20px; font-weight: 800; text-align: center; margin-bottom: 20px;">Complete
            Your Order</h3>

        <form onsubmit="event.preventDefault(); submitOrder();">
            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 5px;">Full Name <span
                        style="color:red">*</span></label>
                <input type="text" id="ordName" class="form-control" required
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px;">
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 5px;">Email Address <span
                        style="color:red">*</span></label>
                <input type="email" id="ordEmail" class="form-control" required
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px;">
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 5px;">Address <span
                        style="color:red">*</span></label>
                <textarea id="ordAddress" class="form-control" required
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; height: 60px;"></textarea>
            </div>

            <div style="display: flex; gap: 10px;">
                <div class="form-group" style="margin-bottom: 15px; flex: 1;">
                    <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 5px;">City <span
                            style="color:red">*</span></label>
                    <input type="text" id="ordCity" class="form-control" required
                        style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px;">
                </div>
                <div class="form-group" style="margin-bottom: 15px; flex: 1;">
                    <label
                        style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 5px;">District <span style="color:red">*</span></label>
                    <select id="ordDistrict" class="form-control"
                        style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; background:#fff;">
                        <option value="">Select district</option>
                        <?php foreach (($deliveryDistricts ?? []) as $districtName): ?>
                            <option value="<?= htmlspecialchars($districtName) ?>"><?= htmlspecialchars($districtName) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 5px;">Phone Number 01
                    <span style="color:red">*</span></label>
                <input type="tel" id="ordPhone1" class="form-control" required
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px;">
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 5px;">Phone Number
                    02</label>
                <input type="tel" id="ordPhone2" class="form-control"
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px;">
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 5px;">Special
                    Note</label>
                <textarea id="ordNote" class="form-control"
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; height: 60px;"></textarea>
            </div>

            <?php if (!empty($settings['bank_transfer_enabled']) && !empty($settings['bank_transfer_details'])): ?>
                <div id="bankTransferDetailsBox" style="display:none; background:#f4f8ff; border:1px solid #d8e4ff; border-radius:12px; padding:14px; margin-bottom:20px;">
                    <div style="font-size:13px; font-weight:800; color:#123b7a; margin-bottom:6px;">Bank Transfer Details</div>
                    <div style="font-size:12px; color:#345; line-height:1.7; white-space:pre-wrap;"><?= nl2br(htmlspecialchars($settings['bank_transfer_details'])) ?></div>
                </div>
            <?php endif; ?>

            <div style="background:#fafafa; border:1px solid #ededed; border-radius:12px; padding:14px; margin-bottom:20px;">
                <div style="display:flex; justify-content:space-between; gap:12px; margin-bottom:8px;">
                    <span style="font-size:13px; color:#777; font-weight:600;">Subtotal</span>
                    <span id="modalSubTotalDisplay" style="font-size:13px; color:#222; font-weight:700;"><?= htmlspecialchars($currency) ?> <?= number_format($productUnitPrice, 0) ?></span>
                </div>
                <div style="display:flex; justify-content:space-between; gap:12px; margin-bottom:8px;">
                    <span style="font-size:13px; color:#777; font-weight:600;">Shipping Fee</span>
                    <span id="modalShippingDisplay" style="font-size:13px; color:#222; font-weight:700;">Select district</span>
                </div>
                <div id="modalHandlingFeeRow" style="display:none; justify-content:space-between; gap:12px; margin-bottom:8px;">
                    <span style="font-size:13px; color:#777; font-weight:600;">Handling Fee</span>
                    <span id="modalHandlingFeeDisplay" style="font-size:13px; color:#222; font-weight:700;"><?= htmlspecialchars($currency) ?> 0</span>
                </div>
                <div style="display:flex; justify-content:space-between; gap:12px; padding-top:8px; border-top:1px dashed #e1e1e1;">
                    <span style="font-size:14px; color:#111; font-weight:800;">Order Total</span>
                    <span id="modalGrandTotalDisplay" style="font-size:16px; color:#111; font-weight:800;"><?= htmlspecialchars($currency) ?> <?= number_format($productUnitPrice, 0) ?></span>
                </div>
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="button" onclick="closeOrderModal()"
                    style="flex: 1; padding: 12px; border: 1px solid #ddd; background: #f5f5f5; border-radius: 8px; font-weight: 600; cursor: pointer;">Cancel</button>
                <button type="submit" id="orderSubmitButton"
                    style="flex: 2; padding: 12px; border: none; background: #6AD07F; color: white; border-radius: 8px; font-weight: 600; cursor: pointer;">Send
                    via WhatsApp</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Quantity Logic
    function updateQty(change) {
        const input = document.getElementById('qtyInput');
        let val = parseInt(input.value);
        val += change;
        if (val < 1) val = 1;
        input.value = val;
        updateSingleOrderTotals();
    }

    // Variation Selection Logic
    let selectedVariations = {};
    let orderMode = 'cod';
    const shopWhatsappNumber = '<?= htmlspecialchars($shopWhatsappTarget, ENT_QUOTES) ?>';
    const currencySymbol = <?= json_encode($currency) ?>;
    const productStockSnapshot = <?= json_encode($stock_snapshot ?? [], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
    const variantStockRows = <?= json_encode($variant_stock_rows ?? [], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
    const deliveryDistricts = <?= json_encode(array_values($deliveryDistricts ?? [])) ?>;
    const deliveryRates = <?= json_encode($deliveryRatesMap ?? new stdClass()) ?>;
    const deliverySettings = {
        applyAll: <?= !empty($settings['delivery_apply_all_districts']) ? 'true' : 'false' ?>,
        firstKg: <?= json_encode((float) ($settings['delivery_all_first_kg'] ?? 0)) ?>,
        additionalKg: <?= json_encode((float) ($settings['delivery_all_additional_kg'] ?? 0)) ?>
    };
    const kokoHandlingFeePercentage = <?= json_encode((float) ($settings['koko_handling_fee_percentage'] ?? 0)) ?>;
    const baseProductPrice = <?= json_encode((float) $productUnitPrice) ?>;
    const baseProductRegularPrice = <?= json_encode((float) ($product['price'] ?? 0)) ?>;
    const baseProductSalePrice = <?= json_encode((!empty($product['sale_price']) && (float) $product['sale_price'] < (float) $product['price']) ? (float) $product['sale_price'] : null) ?>;
    const baseProductWeight = <?= json_encode((int) ($product['weight_grams'] ?? 0)) ?>;
    const productFreeShipping = <?= !empty($product['free_shipping']) ? 'true' : 'false' ?>;
    const defaultProductImageUrl = <?= json_encode($mainImg) ?>;

    function formatMoney(amount) {
        const numericAmount = Number(amount || 0);
        const hasDecimals = Math.abs(numericAmount - Math.round(numericAmount)) > 0.001;
        return currencySymbol + ' ' + numericAmount.toLocaleString(undefined, {
            minimumFractionDigits: hasDecimals ? 2 : 0,
            maximumFractionDigits: 2
        });
    }

    function normalizeDistrict(value) {
        const needle = (value || '').trim().toLowerCase();
        if (!needle) {
            return '';
        }

        for (const district of deliveryDistricts) {
            if (district.toLowerCase() === needle) {
                return district;
            }
        }

        return '';
    }

    function calculateKokoHandlingFee(baseTotal) {
        if (kokoHandlingFeePercentage <= 0) {
            return 0;
        }

        return Number((((Number(baseTotal) || 0) * kokoHandlingFeePercentage) / 100).toFixed(2));
    }

    function calculateSingleShippingQuote(districtValue) {
        const qty = parseInt(document.getElementById('qtyInput').value, 10) || 1;
        const district = normalizeDistrict(districtValue);
        const subtotal = getCurrentUnitPrice() * qty;
        const chargeableWeight = productFreeShipping ? 0 : (Math.max(0, getCurrentWeight()) * qty);
        let firstKg = Number(deliverySettings.firstKg || 0);
        let additionalKg = Number(deliverySettings.additionalKg || 0);
        let hasRate = true;

        if (!deliverySettings.applyAll) {
            if (!district || !deliveryRates[district]) {
                hasRate = false;
            } else {
                firstKg = Number(deliveryRates[district].first_kg_price || 0);
                additionalKg = Number(deliveryRates[district].additional_kg_price || 0);
            }
        }

        let shipping = 0;
        if (chargeableWeight > 0 && hasRate) {
            shipping = firstKg;
            if (chargeableWeight > 1000) {
                shipping += Math.ceil((chargeableWeight - 1000) / 1000) * additionalKg;
            }
        }

        return {
            subtotal,
            shipping,
            total: subtotal + shipping,
            chargeableWeight,
            hasRate,
            district
        };
    }

    function updateSingleOrderTotals() {
        const subtotalEl = document.getElementById('modalSubTotalDisplay');
        const shippingEl = document.getElementById('modalShippingDisplay');
        const totalEl = document.getElementById('modalGrandTotalDisplay');
        const handlingFeeRowEl = document.getElementById('modalHandlingFeeRow');
        const handlingFeeEl = document.getElementById('modalHandlingFeeDisplay');
        if (!subtotalEl || !shippingEl || !totalEl) {
            return calculateSingleShippingQuote('');
        }

        const districtInput = document.getElementById('ordDistrict');
        const quote = calculateSingleShippingQuote(districtInput ? districtInput.value : (localStorage.getItem('cus_district') || ''));
        subtotalEl.textContent = formatMoney(quote.subtotal);
        shippingEl.textContent = quote.chargeableWeight === 0 ? 'Free' : (quote.hasRate ? formatMoney(quote.shipping) : 'Select district');
        const baseTotal = quote.hasRate || quote.chargeableWeight === 0 ? quote.total : quote.subtotal;
        const handlingFee = orderMode === 'koko' ? calculateKokoHandlingFee(baseTotal) : 0;
        if (handlingFeeRowEl && handlingFeeEl) {
            handlingFeeRowEl.style.display = handlingFee > 0 ? 'flex' : 'none';
            handlingFeeEl.textContent = formatMoney(handlingFee);
        }
        totalEl.textContent = formatMoney(baseTotal + handlingFee);
        return quote;
    }

    function getSelectedVariantKey() {
        const pairs = Object.values(selectedVariations)
            .map(item => `${item.variation_id}:${item.variation_value_id}`)
            .sort();
        return pairs.join('|');
    }

    function getVariantText() {
        return Object.values(selectedVariations)
            .sort((a, b) => String(a.variation_name).localeCompare(String(b.variation_name)))
            .map(item => item.variation_name + ': ' + item.variation_value)
            .join(', ');
    }

    function getRequiredVariationCount() {
        return document.querySelectorAll('.var-section').length;
    }

    function hasCompletedVariationSelection() {
        const requiredCount = getRequiredVariationCount();
        if (!requiredCount) {
            return true;
        }

        return Object.keys(selectedVariations).length >= requiredCount;
    }

    function getActiveVariantRow() {
        const variantKey = getSelectedVariantKey();
        return variantStockRows.find(row => row.combination_key === variantKey && Number(row.is_active)) || null;
    }

    function getCurrentUnitPrice() {
        const activeVariant = getActiveVariantRow();
        if (activeVariant && activeVariant.variant_sale_price !== null && activeVariant.variant_sale_price !== undefined && activeVariant.variant_sale_price !== '') {
            return Number(activeVariant.variant_sale_price || 0);
        }
        if (activeVariant && activeVariant.variant_price !== null && activeVariant.variant_price !== undefined && activeVariant.variant_price !== '') {
            return Number(activeVariant.variant_price || 0);
        }
        return Number(baseProductPrice || 0);
    }

    function getCurrentRegularPrice() {
        const activeVariant = getActiveVariantRow();
        if (activeVariant && activeVariant.variant_price !== null && activeVariant.variant_price !== undefined && activeVariant.variant_price !== '') {
            return Number(activeVariant.variant_price || 0);
        }
        return Number(baseProductRegularPrice || 0);
    }

    function getCurrentSalePrice() {
        const activeVariant = getActiveVariantRow();
        if (activeVariant && activeVariant.variant_sale_price !== null && activeVariant.variant_sale_price !== undefined && activeVariant.variant_sale_price !== '') {
            return Number(activeVariant.variant_sale_price || 0);
        }
        return baseProductSalePrice !== null ? Number(baseProductSalePrice || 0) : null;
    }

    function getCurrentWeight() {
        const activeVariant = getActiveVariantRow();
        if (activeVariant && activeVariant.variant_weight_grams !== undefined && activeVariant.variant_weight_grams !== null) {
            return Number(activeVariant.variant_weight_grams || 0);
        }
        return Number(baseProductWeight || 0);
    }

    function getCurrentImage() {
        const activeVariant = getActiveVariantRow();
        if (activeVariant && activeVariant.image_url) {
            return activeVariant.image_url;
        }
        return defaultProductImageUrl;
    }

    function updateDisplayedPrice() {
        const currentPriceEl = document.getElementById('productCurrentPrice');
        const oldPriceEl = document.getElementById('productOldPrice');
        if (!currentPriceEl) {
            return;
        }

        const currentPrice = getCurrentUnitPrice();
        const regularPrice = getCurrentRegularPrice();
        const salePrice = getCurrentSalePrice();
        currentPriceEl.textContent = formatMoney(currentPrice);
        if (oldPriceEl) {
            if (salePrice !== null && salePrice < regularPrice) {
                oldPriceEl.style.display = '';
                oldPriceEl.textContent = formatMoney(regularPrice);
            } else {
                oldPriceEl.style.display = 'none';
            }
        }
    }

    function updateDisplayedVariantImage() {
        const imageUrl = getCurrentImage();
        const mainImg = document.querySelector('.gallery-slider .gallery-img.current');
        if (!mainImg || !imageUrl) {
            return;
        }

        const picture = mainImg.closest('picture');
        if (picture) {
            picture.querySelectorAll('source').forEach(function (source) {
                source.setAttribute('srcset', imageUrl);
            });
        }
        mainImg.setAttribute('src', imageUrl);
    }

    function isVariantRowPurchasable(row) {
        if (!row || !Number(row.is_active)) {
            return false;
        }

        if (row.stock_mode === 'track_stock') {
            return Number(row.stock_qty || 0) > 0;
        }

        return true;
    }

    function rowMatchesSelection(row, pairs) {
        if (!row || !pairs.length) {
            return false;
        }

        const rowPairs = String(row.combination_key || '').split('|').filter(Boolean);
        return pairs.every(pair => rowPairs.includes(pair));
    }

    function buildSelectionForPill(pill) {
        const nextSelection = { ...selectedVariations };
        const variationName = pill.dataset.variationName;

        nextSelection[variationName] = {
            variation_id: Number(pill.dataset.variationId || 0),
            variation_name: variationName,
            variation_value_id: Number(pill.dataset.valueId || 0),
            variation_value: pill.dataset.valueLabel || pill.textContent.trim()
        };

        return Object.values(nextSelection)
            .map(item => `${item.variation_id}:${item.variation_value_id}`)
            .sort((a, b) => {
                const [aVariationId, aValueId] = a.split(':').map(Number);
                const [bVariationId, bValueId] = b.split(':').map(Number);
                return aVariationId - bVariationId || aValueId - bValueId;
            });
    }

    function updateProductStockNotice(message, type) {
        const notice = document.getElementById('productStockNotice');
        if (!notice) return;
        if (!message) {
            notice.style.display = 'none';
            notice.textContent = '';
            return;
        }
        notice.style.display = 'block';
        notice.textContent = message;
        if (type === 'error') {
            notice.style.background = '#fff1f0';
            notice.style.color = '#c54132';
        } else if (type === 'warning') {
            notice.style.background = '#fff7e6';
            notice.style.color = '#9a6a11';
        } else {
            notice.style.background = '#edf7f0';
            notice.style.color = '#1d7a40';
        }
    }

    function updateClearFilterButton() {
        const button = document.getElementById('clearStockFilterBtn');
        if (!button) {
            return;
        }

        button.style.display = Object.keys(selectedVariations).length ? 'inline-flex' : 'none';
    }

    function refreshVariantAvailability() {
        if (!Array.isArray(variantStockRows) || !variantStockRows.length) {
            updateClearFilterButton();
            if (productStockSnapshot && productStockSnapshot.status === 'out_of_stock') {
                updateProductStockNotice('Out of stock', 'error');
            } else {
                updateProductStockNotice('In stock', 'success');
            }
            return;
        }

        updateClearFilterButton();
        const pills = document.querySelectorAll('.var-pill');
        pills.forEach(pill => {
            const nextPairs = buildSelectionForPill(pill);
            const isPossible = variantStockRows.some(row => isVariantRowPurchasable(row) && rowMatchesSelection(row, nextPairs));

            pill.classList.toggle('is-disabled', !isPossible);
        });

        const activeVariant = getActiveVariantRow();
        if (activeVariant) {
            if (activeVariant.stock_mode === 'track_stock' && Number(activeVariant.stock_qty || 0) <= 0) {
                updateProductStockNotice('Out of stock', 'error');
            } else {
                updateProductStockNotice('In stock', 'success');
            }
        } else if (Object.keys(selectedVariations).length > 0 && hasCompletedVariationSelection()) {
            updateProductStockNotice('Out of stock', 'error');
        } else {
            updateProductStockNotice('', '');
        }

        updateDisplayedPrice();
        updateDisplayedVariantImage();
        updateSingleOrderTotals();
    }

    function validateCurrentSelection(qty) {
        qty = qty || (parseInt(document.getElementById('qtyInput').value) || 1);
        const requiredVariationCount = getRequiredVariationCount();
        if (requiredVariationCount && Object.keys(selectedVariations).length < requiredVariationCount) {
            return { ok: false, message: 'Please choose all required product variations.' };
        }

        if (Array.isArray(variantStockRows) && variantStockRows.length) {
            if (!getSelectedVariantKey()) {
                return { ok: false, message: 'Please choose a valid stock option.' };
            }

            const activeVariant = getActiveVariantRow();
            if (!activeVariant) {
                return { ok: false, message: 'This stock option is out of stock.' };
            }

            if (activeVariant.stock_mode === 'track_stock' && Number(activeVariant.stock_qty || 0) < qty) {
                return { ok: false, message: 'Selected stock is out of stock.' };
            }
        } else if (productStockSnapshot && productStockSnapshot.stock_mode === 'track_stock' && Number(productStockSnapshot.stock_qty || 0) < qty) {
            return { ok: false, message: 'Out of stock.' };
        } else if (productStockSnapshot && productStockSnapshot.status === 'out_of_stock') {
            return { ok: false, message: 'Out of stock.' };
        }

        return { ok: true };
    }

    function selectVariation(el) {
        if (el.classList.contains('is-disabled')) {
            return;
        }
        let siblings = el.parentElement.querySelectorAll('.var-pill');
        siblings.forEach(s => s.classList.remove('active'));
        el.classList.add('active');

        const variationName = el.dataset.variationName;
        selectedVariations[variationName] = {
            variation_id: Number(el.dataset.variationId || 0),
            variation_name: variationName,
            variation_value_id: Number(el.dataset.valueId || 0),
            variation_value: el.dataset.valueLabel || el.textContent.trim()
        };
        refreshVariantAvailability();
    }

    function clearVariationSelection() {
        selectedVariations = {};
        document.querySelectorAll('.var-pill.active').forEach(function (pill) {
            pill.classList.remove('active');
        });
        refreshVariantAvailability();
    }

    function openPaymentMethodSheet() {
        const selectionCheck = validateCurrentSelection();
        if (!selectionCheck.ok) {
            alert(selectionCheck.message);
            return;
        }
        trackAnalyticsEvent('begin_checkout', {
            currency: window.APP_CURRENCY,
            value: Number(getCurrentUnitPrice() || 0) * Number(parseInt(document.getElementById('qtyInput').value, 10) || 1),
            items: [buildAnalyticsItem({
                id: <?= (int) $product['id'] ?>,
                title: "<?= addslashes($product['title']) ?>",
                variant: getVariantText(),
                price: getCurrentUnitPrice(),
                quantity: parseInt(document.getElementById('qtyInput').value, 10) || 1
            })]
        }, 'InitiateCheckout', {
            content_ids: ['<?= (int) $product['id'] ?>'],
            content_name: "<?= addslashes($product['title']) ?>",
            content_type: 'product',
            value: Number(getCurrentUnitPrice() || 0) * Number(parseInt(document.getElementById('qtyInput').value, 10) || 1),
            currency: window.APP_CURRENCY
        });
        document.getElementById('paymentMethodSheet').style.display = 'flex';
    }

    function closePaymentMethodSheet() {
        document.getElementById('paymentMethodSheet').style.display = 'none';
    }

    function choosePaymentMethod(mode) {
        closePaymentMethodSheet();
        openOrderModal(mode);
    }

    function openOrderModal(mode = 'cod') {
        orderMode = mode;
        // Load Saved Data
        if (localStorage.getItem('cus_name')) document.getElementById('ordName').value = localStorage.getItem('cus_name');
        if (localStorage.getItem('cus_email')) document.getElementById('ordEmail').value = localStorage.getItem('cus_email');
        if (localStorage.getItem('cus_address')) document.getElementById('ordAddress').value = localStorage.getItem('cus_address');
        if (localStorage.getItem('cus_city')) document.getElementById('ordCity').value = localStorage.getItem('cus_city');
        if (localStorage.getItem('cus_district')) document.getElementById('ordDistrict').value = localStorage.getItem('cus_district');
        if (localStorage.getItem('cus_phone1')) document.getElementById('ordPhone1').value = localStorage.getItem('cus_phone1');
        if (localStorage.getItem('cus_phone2')) document.getElementById('ordPhone2').value = localStorage.getItem('cus_phone2');
        updateSingleOrderTotals();

        const submitButton = document.getElementById('orderSubmitButton');
        if (orderMode === 'payhere') {
            submitButton.textContent = 'Continue to Card Payment';
            submitButton.classList.add('btn-payhere-submit');
            submitButton.style.background = '';
        } else if (orderMode === 'whatsapp') {
            submitButton.textContent = 'Continue to WhatsApp';
            submitButton.classList.remove('btn-payhere-submit');
            submitButton.style.background = '#25D366';
        } else if (orderMode === 'koko') {
            submitButton.textContent = 'Continue to KOKO';
            submitButton.classList.remove('btn-payhere-submit');
            submitButton.style.background = '#c48b11';
        } else if (orderMode === 'bank_transfer') {
            submitButton.textContent = 'Place Bank Transfer Order';
            submitButton.classList.remove('btn-payhere-submit');
            submitButton.style.background = '#1f5aa6';
        } else {
            submitButton.textContent = 'Place COD Order';
            submitButton.classList.remove('btn-payhere-submit');
            submitButton.style.background = '#111';
        }

        const bankDetailsBox = document.getElementById('bankTransferDetailsBox');
        if (bankDetailsBox) {
            bankDetailsBox.style.display = orderMode === 'bank_transfer' ? 'block' : 'none';
        }

        document.getElementById('orderModal').style.display = 'flex';
    }

    function closeOrderModal() {
        document.getElementById('orderModal').style.display = 'none';
    }

    function submitOrder() {
        const name = document.getElementById('ordName').value.trim();
        const email = document.getElementById('ordEmail').value.trim();
        const address = document.getElementById('ordAddress').value.trim();
        const city = document.getElementById('ordCity').value.trim();
        const district = normalizeDistrict(document.getElementById('ordDistrict').value);
        const phone1 = document.getElementById('ordPhone1').value.trim();
        const phone2 = document.getElementById('ordPhone2').value.trim();
        const note = document.getElementById('ordNote').value.trim();
        const selectionCheck = validateCurrentSelection();

        if (!name || !email || !address || !city || !phone1 || !district) {
            alert("Please fill in all required fields.");
            return;
        }
        if (!selectionCheck.ok) {
            alert(selectionCheck.message);
            return;
        }

        localStorage.setItem('cus_name', name);
        localStorage.setItem('cus_email', email);
        localStorage.setItem('cus_address', address);
        localStorage.setItem('cus_city', city);
        localStorage.setItem('cus_district', district);
        localStorage.setItem('cus_phone1', phone1);
        localStorage.setItem('cus_phone2', phone2);

        if (orderMode === 'payhere') {
            submitOrderToPayHere({
                name,
                email,
                address,
                city,
                district,
                phone1,
                phone2,
                note
            });
            return;
        }

        if (orderMode === 'koko') {
            submitOrderToKoko({
                name,
                email,
                address,
                city,
                district,
                phone1,
                phone2,
                note
            });
            return;
        }

        if (orderMode === 'bank_transfer') {
            submitOrderToBankTransfer({
                name,
                email,
                address,
                city,
                district,
                phone1,
                phone2,
                note
            });
            return;
        }

        if (orderMode === 'whatsapp') {
            submitOrderToWhatsApp({
                name,
                email,
                address,
                city,
                district,
                phone1,
                phone2,
                note
            });
            return;
        }

        submitOrderToCod({
            name,
            email,
            address,
            city,
            district,
            phone1,
            phone2,
            note
        });
    }

    function submitOrderToPayHere(data) {
        const qty = parseInt(document.getElementById('qtyInput').value) || 1;
        const variantStr = getVariantText();
        const variantKey = getSelectedVariantKey();

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= BASE_URL ?>order/startPayhereSingle';
        form.style.display = 'none';

        const fields = {
            product_id: '<?= (int) $product['id'] ?>',
            quantity: qty,
            variants: variantStr,
            variant_key: variantKey,
            customer_name: data.name,
            email: data.email,
            address: data.address,
            city: data.city,
            district: data.district,
            phone: data.phone1,
            phone_alt: data.phone2,
            note: data.note
        };

        Object.keys(fields).forEach(function (key) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = fields[key] || '';
            form.appendChild(input);
        });

        appendCsrfToken(form);
        trackAnalyticsEvent('add_payment_info', {
            currency: window.APP_CURRENCY,
            payment_type: 'payhere',
            value: Number(getCurrentUnitPrice() || 0) * Number(qty || 1)
        });
        document.body.appendChild(form);
        if (typeof showGlobalLoader === 'function') showGlobalLoader();
        form.submit();
    }

    function submitOrderToCod(data) {
        const qty = parseInt(document.getElementById('qtyInput').value) || 1;
        const variantStr = getVariantText();
        const variantKey = getSelectedVariantKey();

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= BASE_URL ?>order/startCodSingle';
        form.style.display = 'none';

        const fields = {
            product_id: '<?= (int) $product['id'] ?>',
            quantity: qty,
            variants: variantStr,
            variant_key: variantKey,
            customer_name: data.name,
            email: data.email,
            address: data.address,
            city: data.city,
            district: data.district,
            phone: data.phone1,
            phone_alt: data.phone2,
            note: data.note
        };

        Object.keys(fields).forEach(function (key) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = fields[key] || '';
            form.appendChild(input);
        });

        appendCsrfToken(form);
        trackAnalyticsEvent('add_payment_info', {
            currency: window.APP_CURRENCY,
            payment_type: 'cod',
            value: Number(getCurrentUnitPrice() || 0) * Number(qty || 1)
        });
        document.body.appendChild(form);
        if (typeof showGlobalLoader === 'function') showGlobalLoader();
        form.submit();
    }

    function submitOrderToKoko(data) {
        const qty = parseInt(document.getElementById('qtyInput').value) || 1;
        const variantStr = getVariantText();
        const variantKey = getSelectedVariantKey();

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= BASE_URL ?>order/startKokoSingle';
        form.style.display = 'none';

        const fields = {
            product_id: '<?= (int) $product['id'] ?>',
            quantity: qty,
            variants: variantStr,
            variant_key: variantKey,
            customer_name: data.name,
            email: data.email,
            address: data.address,
            city: data.city,
            district: data.district,
            phone: data.phone1,
            phone_alt: data.phone2,
            note: data.note
        };

        Object.keys(fields).forEach(function (key) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = fields[key] || '';
            form.appendChild(input);
        });

        appendCsrfToken(form);
        trackAnalyticsEvent('add_payment_info', {
            currency: window.APP_CURRENCY,
            payment_type: 'koko',
            value: Number(getCurrentUnitPrice() || 0) * Number(qty || 1)
        });
        document.body.appendChild(form);
        if (typeof showGlobalLoader === 'function') showGlobalLoader();
        form.submit();
    }

    function submitOrderToBankTransfer(data) {
        const qty = parseInt(document.getElementById('qtyInput').value) || 1;
        const variantStr = getVariantText();
        const variantKey = getSelectedVariantKey();

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= BASE_URL ?>order/startBankTransferSingle';
        form.style.display = 'none';

        const fields = {
            product_id: '<?= (int) $product['id'] ?>',
            quantity: qty,
            variants: variantStr,
            variant_key: variantKey,
            customer_name: data.name,
            email: data.email,
            address: data.address,
            city: data.city,
            district: data.district,
            phone: data.phone1,
            phone_alt: data.phone2,
            note: data.note
        };

        Object.keys(fields).forEach(function (key) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = fields[key] || '';
            form.appendChild(input);
        });

        appendCsrfToken(form);
        trackAnalyticsEvent('add_payment_info', {
            currency: window.APP_CURRENCY,
            payment_type: 'bank_transfer',
            value: Number(getCurrentUnitPrice() || 0) * Number(qty || 1)
        });
        document.body.appendChild(form);
        if (typeof showGlobalLoader === 'function') showGlobalLoader();
        form.submit();
    }

    function submitOrderToWhatsApp(data) {
        if (!shopWhatsappNumber) {
            alert('WhatsApp ordering is not configured for this shop.');
            return;
        }

        const qty = parseInt(document.getElementById('qtyInput').value) || 1;
        const variantStr = getVariantText();
        const lines = [
            '*New WhatsApp Order Request*',
            '',
            '*Product:* <?= addslashes($product['title']) ?>',
            '*Quantity:* ' + qty
        ];

        if (variantStr) {
            lines.push('*Variants:* ' + variantStr);
        }

        lines.push(
            '*Customer:* ' + data.name,
            '*Email:* ' + data.email,
            '*Phone:* ' + data.phone1
        );

        if (data.phone2) {
            lines.push('*Alt Phone:* ' + data.phone2);
        }

        lines.push(
            '*Address:* ' + data.address,
            '*City:* ' + data.city
        );

        if (data.district) {
            lines.push('*District:* ' + data.district);
        }

        if (data.note) {
            lines.push('*Note:* ' + data.note);
        }

        const quote = calculateSingleShippingQuote(data.district);
        lines.push(
            '*Subtotal:* ' + formatMoney(quote.subtotal),
            '*Shipping Fee:* ' + (quote.chargeableWeight === 0 ? 'Free' : formatMoney(quote.shipping)),
            '*Order Total:* ' + formatMoney(quote.total)
        );

        if (typeof showGlobalLoader === 'function') showGlobalLoader();
        window.location.href = 'https://wa.me/' + shopWhatsappNumber + '?text=' + encodeURIComponent(lines.join("\n"));
    }

    document.addEventListener('DOMContentLoaded', function () {
        const districtInput = document.getElementById('ordDistrict');
        if (districtInput) {
            districtInput.addEventListener('input', updateSingleOrderTotals);
            districtInput.addEventListener('change', function () {
                const normalized = normalizeDistrict(districtInput.value);
                if (normalized) {
                    districtInput.value = normalized;
                }
                updateSingleOrderTotals();
            });
        }

        updateSingleOrderTotals();

        trackAnalyticsEvent('view_item', {
            currency: window.APP_CURRENCY,
            value: Number(getCurrentUnitPrice() || 0),
            items: [buildAnalyticsItem({
                id: <?= (int) $product['id'] ?>,
                title: "<?= addslashes($product['title']) ?>",
                variant: getVariantText(),
                price: getCurrentUnitPrice(),
                quantity: 1
            })]
        }, 'ViewContent', {
            content_ids: ['<?= (int) $product['id'] ?>'],
            content_name: "<?= addslashes($product['title']) ?>",
            content_type: 'product',
            value: Number(getCurrentUnitPrice() || 0),
            currency: window.APP_CURRENCY
        });
    });


    // --- Add to Cart Logic (AJAX) ---
    function addToCartFromProductPage() {
        // Show Loader
        if (typeof showGlobalLoader === 'function') showGlobalLoader();

        //  Gather Details
        const id = <?= $product['id'] ?>;
        const title = "<?= addslashes($product['title']) ?>";
        const price = getCurrentUnitPrice();
        const qty = parseInt(document.getElementById('qtyInput').value) || 1;
        const selectionCheck = validateCurrentSelection(qty);
        if (!selectionCheck.ok) {
            if (typeof hideGlobalLoader === 'function') hideGlobalLoader();
            alert(selectionCheck.message);
            return;
        }

        const img = getCurrentImage();

        // Format Variations String
        let variantStr = getVariantText();
        let variantKey = getSelectedVariantKey();

        //  Prepare Data
        const payload = {
            id: id,
            title: title,
            price: price,
            quantity: qty,
            img: img,
            variants: variantStr,
            variant_key: variantKey
        };

        // Send AJAX Request
        fetch('<?= BASE_URL ?>cart/add', {
            method: 'POST',
            headers: csrfHeaders({
                'Content-Type': 'application/json',
            }),
            body: JSON.stringify(payload)
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    trackAnalyticsEvent('add_to_cart', {
                        currency: window.APP_CURRENCY,
                        value: Number(price || 0) * Number(qty || 1),
                        items: [buildAnalyticsItem({
                            id: id,
                            title: title,
                            variant: variantStr,
                            price: price,
                            quantity: qty
                        })]
                    }, 'AddToCart', {
                        content_ids: [String(id)],
                        content_name: title,
                        content_type: 'product',
                        value: Number(price || 0) * Number(qty || 1),
                        currency: window.APP_CURRENCY,
                        contents: [{
                            id: String(id),
                            quantity: Number(qty || 1),
                            item_price: Number(price || 0)
                        }]
                    });

                    if (typeof showCartToast === 'function') showCartToast();

                    const bubbleCount = document.querySelector('.floating-cart-count');
                    const headerCount = document.querySelector('.cart-badge-count');

                    if (data.count) {
                        if (bubbleCount) bubbleCount.innerText = data.count;
                        if (headerCount) {
                            headerCount.innerText = data.count;
                            headerCount.style.display = 'inline-block';
                        }
                        const floatingCart = document.querySelector('.floating-cart');
                        if (floatingCart) floatingCart.style.display = 'flex';
                    }
                } else {
                    alert('Failed to add to cart');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Something went wrong. Please try again.');
            })
            .finally(() => {
                //  Hide Loader Always
                if (typeof hideGlobalLoader === 'function') hideGlobalLoader();
            });
    }


    refreshVariantAvailability();
</script>

<?php require_once 'views/layouts/customer_footer.php'; ?>
