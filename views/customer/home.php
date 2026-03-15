<?php
require_once ROOT_PATH . 'helpers/ImageHelper.php';
require_once 'views/layouts/customer_header.php';
?>

<!-- Mobile Welcome Block (Moved here or kept in header, keeping here ensures it is part of flow) -->
<!-- Actually handled in Header for global presence, but specific to Home? 
     Design shows it at top. Header has it. Good. -->

<div class="home-layout">

    <?php
    // Organize Categories into Tree
    $categoryTree = [];
    // First pass: Main categories
    foreach ($categories as $cat) {
        if (empty($cat['parent_id'])) {
            $categoryTree[$cat['id']] = $cat;
            $categoryTree[$cat['id']]['children'] = [];
        }
    }
    // Second pass: Subcategories
    foreach ($categories as $cat) {
        if (!empty($cat['parent_id']) && isset($categoryTree[$cat['parent_id']])) {
            $categoryTree[$cat['parent_id']]['children'][] = $cat;
        }
    }

    $heroSlides = [];
    for ($i = 1; $i <= 3; $i++) {
        $imageKey = 'hero_slide_' . $i . '_image';
        $linkKey = 'hero_slide_' . $i . '_link';
        $imageUrl = ImageHelper::settingsImageUrl($settings[$imageKey] ?? '', '');

        if (!empty($imageUrl)) {
            $heroSlides[] = [
                'image' => $imageUrl,
                'image_name' => basename((string) parse_url($imageUrl, PHP_URL_PATH)),
                'alt' => 'Hero slide ' . $i,
                'link' => $settings[$linkKey] ?? ''
            ];
        }
    }

    if (empty($heroSlides)) {
        // Fallback to product images when custom slides are not configured yet.
        $heroSources = array_merge($featuredProducts ?? [], $latestProducts ?? [], $saleProducts ?? []);

        foreach ($heroSources as $heroProduct) {
            if (count($heroSlides) >= 5) {
                break;
            }

            $heroImage = $heroProduct['main_image'] ?? '';
            $heroPath = 'assets/uploads/' . $heroImage;

            if (empty($heroImage) || !file_exists(ROOT_PATH . $heroPath) || isset($heroSlides[$heroImage])) {
                continue;
            }

            $heroSlides[$heroImage] = [
                'image' => ImageHelper::uploadUrl($heroImage, ''),
                'image_name' => $heroImage,
                'alt' => $heroProduct['name'] ?? 'Hero slide',
                'link' => !empty($heroProduct['id']) ? (BASE_URL . 'shop/product/' . $heroProduct['id']) : ''
            ];
        }

        $heroSlides = array_values($heroSlides);
    }
    ?>

    <!-- DESKTOP SIDEBAR (Visible only on Desktop) -->
    <?php include 'views/customer/partials/sidebar.php'; ?>

    <!-- MAIN CONTENT AREA -->
    <main class="main-content">

        <?php if (!empty($heroSlides)): ?>
            <section class="hero-slider-section" aria-label="Featured highlights">
                <div class="hero-slider" data-hero-slider>
                    <?php foreach ($heroSlides as $index => $slide): ?>
                        <div class="hero-slide">
                            <?php if (!empty($slide['link'])): ?>
                                <a href="<?= htmlspecialchars($slide['link']) ?>" class="hero-slide-link" aria-label="<?= htmlspecialchars($slide['alt']) ?>">
                                    <?= ImageHelper::renderResponsivePicture(
                                        $slide['image_name'] ?? '',
                                        $slide['image'],
                                        [
                                            'alt' => $slide['alt'],
                                            'loading' => $index === 0 ? 'eager' : 'lazy',
                                            'decoding' => $index === 0 ? 'sync' : 'async',
                                            'fetchpriority' => $index === 0 ? 'high' : 'low'
                                        ],
                                        'hero'
                                    ) ?>
                                </a>
                            <?php else: ?>
                                <?= ImageHelper::renderResponsivePicture(
                                    $slide['image_name'] ?? '',
                                    $slide['image'],
                                    [
                                        'alt' => $slide['alt'],
                                        'loading' => $index === 0 ? 'eager' : 'lazy',
                                        'decoding' => $index === 0 ? 'sync' : 'async',
                                        'fetchpriority' => $index === 0 ? 'high' : 'low'
                                    ],
                                    'hero'
                                ) ?>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if (count($heroSlides) > 1): ?>
                    <div class="hero-slider-dots" data-hero-dots>
                        <?php foreach ($heroSlides as $index => $slide): ?>
                            <button
                                type="button"
                                class="hero-slider-dot <?= $index === 0 ? 'active' : '' ?>"
                                aria-label="Go to slide <?= $index + 1 ?>"
                                data-slide-to="<?= $index ?>"></button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        <?php endif; ?>

        <!-- Top Categories (Mobile Horizontal Scroll / Desktop Grid?) -->
        <div class="section-header">
            <h2 class="section-title">Top Categories</h2>
            <a href="<?= BASE_URL ?>shop/categories" class="view-all">View All</a>
        </div>

        <div style="position: relative;">
            <button class="scroll-btn left d-lg-flex" onclick="scrollSection(this, -1)" style="display: none; position: absolute; top: 50%; left: -15px; transform: translateY(-50%); z-index: 10; 
                       width: 35px; height: 35px; border-radius: 50%; background: white; 
                       box-shadow: 0 2px 5px rgba(0,0,0,0.1); border: 1px solid #eee; 
                       cursor: pointer; align-items: center; justify-content: center;">
                <i class="fas fa-chevron-left" style="color: black; font-size: 14px;"></i>
            </button>
            <div class="categories-scroll">
                <?php foreach ($categories as $cat): ?>
                    <a href="<?= BASE_URL ?>shop/category/<?= $cat['id'] ?>" class="cat-item"
                        style="text-decoration: none; color: inherit; display: block;">
                        <?php
                        $img = ImageHelper::uploadUrl(
                            $cat['image'] ?? '',
                            'https://via.placeholder.com/60?text=' . urlencode($cat['name'])
                        );
                        ?>
                        <?= ImageHelper::renderResponsivePicture(
                            $cat['image'] ?? '',
                            $img,
                            [
                                'class' => 'cat-img',
                                'alt' => $cat['name'] ?? 'Category',
                                'loading' => 'lazy',
                                'decoding' => 'async',
                                'fetchpriority' => 'low'
                            ],
                            'category_card'
                        ) ?>
                        <div class="cat-name">
                            <?= htmlspecialchars($cat['name']) ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
            <button class="scroll-btn right d-lg-flex" onclick="scrollSection(this, 1)" style="display: none; position: absolute; top: 50%; right: -15px; transform: translateY(-50%); z-index: 10; 
                       width: 35px; height: 35px; border-radius: 50%; background: white; 
                       box-shadow: 0 2px 5px rgba(0,0,0,0.1); border: 1px solid #eee; 
                       cursor: pointer; align-items: center; justify-content: center;">
                <i class="fas fa-chevron-right" style="color: black; font-size: 14px;"></i>
            </button>
        </div>

        <!-- Featured Products -->
        <?php if (!empty($featuredProducts)): ?>
            <div class="section-header">
                <h2 class="section-title">Featured Products <span class="tag special">SPECIAL</span></h2>
            </div>
            <div style="position: relative;">
                <button class="scroll-btn left d-lg-flex" onclick="scrollSection(this, -1)" style="display: none; position: absolute; top: 50%; left: -15px; transform: translateY(-50%); z-index: 10; 
                       width: 35px; height: 35px; border-radius: 50%; background: white; 
                       box-shadow: 0 2px 5px rgba(0,0,0,0.1); border: 1px solid #eee; 
                       cursor: pointer; align-items: center; justify-content: center;">
                    <i class="fas fa-chevron-left" style="color: black; font-size: 14px;"></i>
                </button>
                <div class="products-scroll">
                    <?php foreach ($featuredProducts as $prod): ?>
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
        <?php endif; ?>

        <!-- Latest Products -->
        <div class="section-header">
            <h2 class="section-title">Latest Products <span class="tag new">NEW</span></h2>
        </div>
        <div style="position: relative;">
            <button class="scroll-btn left d-lg-flex" onclick="scrollSection(this, -1)" style="display: none; position: absolute; top: 50%; left: -15px; transform: translateY(-50%); z-index: 10; 
                       width: 35px; height: 35px; border-radius: 50%; background: white; 
                       box-shadow: 0 2px 5px rgba(0,0,0,0.1); border: 1px solid #eee; 
                       cursor: pointer; align-items: center; justify-content: center;">
                <i class="fas fa-chevron-left" style="color: black; font-size: 14px;"></i>
            </button>
            <div class="products-scroll">
                <?php foreach ($latestProducts as $prod): ?>
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

        <!-- Sale Products -->
        <?php if (!empty($saleProducts)): ?>
            <div class="section-header">
                <h2 class="section-title">Sale Products <span class="tag sale">Sale..!</span></h2>
            </div>
            <div style="position: relative;">
                <button class="scroll-btn left d-lg-flex" onclick="scrollSection(this, -1)" style="display: none; position: absolute; top: 50%; left: -15px; transform: translateY(-50%); z-index: 10; 
                       width: 35px; height: 35px; border-radius: 50%; background: white; 
                       box-shadow: 0 2px 5px rgba(0,0,0,0.1); border: 1px solid #eee; 
                       cursor: pointer; align-items: center; justify-content: center;">
                    <i class="fas fa-chevron-left" style="color: black; font-size: 14px;"></i>
                </button>
                <div class="products-scroll">
                    <?php foreach ($saleProducts as $prod): ?>
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
        <?php endif; ?>

        <?php if (!empty($freeShippingProducts)): ?>
            <div class="section-header">
                <h2 class="section-title">Free Shipping Products</h2>
            </div>
            <div style="position: relative;">
                <button class="scroll-btn left d-lg-flex" onclick="scrollSection(this, -1)" style="display: none; position: absolute; top: 50%; left: -15px; transform: translateY(-50%); z-index: 10; 
                       width: 35px; height: 35px; border-radius: 50%; background: white; 
                       box-shadow: 0 2px 5px rgba(0,0,0,0.1); border: 1px solid #eee; 
                       cursor: pointer; align-items: center; justify-content: center;">
                    <i class="fas fa-chevron-left" style="color: black; font-size: 14px;"></i>
                </button>
                <div class="products-scroll">
                    <?php foreach ($freeShippingProducts as $prod): ?>
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
        <?php endif; ?>

    </main>

</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const sliders = document.querySelectorAll('.categories-scroll, .products-scroll');
        const heroSlider = document.querySelector('[data-hero-slider]');
        const heroDots = document.querySelectorAll('[data-slide-to]');

        sliders.forEach(slider => {
            const wrapper = slider.parentElement;
            const btnLeft = wrapper.querySelector('.scroll-btn.left');
            const btnRight = wrapper.querySelector('.scroll-btn.right');

            // --- 1. Smart Buttons Visibility (Desktop Only) ---
            const updateButtons = () => {
                // Determine if we are on Desktop (approx > 1024px)
                // We rely on 'd-lg-flex' CSS for base visibility, but we override here
                if (window.innerWidth < 1024) return;

                // Threshold to hide
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

            // --- 2. Drag to Scroll (Mouse Grab) ---
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
                const walk = (x - startX) * 2; // Scroll-fast
                slider.scrollLeft = scrollLeft - walk;
            });

            // Set initial cursor
            if (window.innerWidth >= 1024) {
                slider.style.cursor = 'grab';
            }
        });

        if (heroSlider && heroDots.length > 0) {
            let currentSlide = 0;
            let autoPlay;

            const goToSlide = (index) => {
                currentSlide = index;
                heroSlider.scrollTo({
                    left: heroSlider.clientWidth * index,
                    behavior: 'smooth'
                });

                heroDots.forEach((dot, dotIndex) => {
                    dot.classList.toggle('active', dotIndex === index);
                });
            };

            const startAutoPlay = () => {
                if (heroDots.length < 2) return;

                clearInterval(autoPlay);
                autoPlay = setInterval(() => {
                    const nextSlide = (currentSlide + 1) % heroDots.length;
                    goToSlide(nextSlide);
                }, 3500);
            };

            heroDots.forEach(dot => {
                dot.addEventListener('click', () => {
                    goToSlide(Number(dot.dataset.slideTo));
                    startAutoPlay();
                });
            });

            heroSlider.addEventListener('scroll', () => {
                const nextIndex = Math.round(heroSlider.scrollLeft / heroSlider.clientWidth);

                if (nextIndex !== currentSlide) {
                    currentSlide = nextIndex;
                    heroDots.forEach((dot, dotIndex) => {
                        dot.classList.toggle('active', dotIndex === currentSlide);
                    });
                }
            });

            heroSlider.addEventListener('touchstart', () => clearInterval(autoPlay), { passive: true });
            heroSlider.addEventListener('touchend', startAutoPlay, { passive: true });
            window.addEventListener('resize', () => goToSlide(currentSlide));

            startAutoPlay();
        }
    });

    // Button Click Helper
    function scrollSection(btn, direction) {
        var container = btn.parentElement.querySelector('.categories-scroll, .products-scroll');
        if (container) {
            container.scrollBy({
                left: direction * 300,
                behavior: 'smooth'
            });
        }
    }
</script>

<?php require_once 'views/layouts/customer_footer.php'; ?>
