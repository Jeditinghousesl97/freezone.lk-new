<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?= $title ?>
    </title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin.css">
    <style>
        .page-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .header-title {
            font-size: 20px;
            font-weight: bold;
            color: #000;
        }

        .style-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }

        .card-header {
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 15px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }

        .control-group {
            margin-bottom: 15px;
        }

        .control-label {
            font-size: 13px;
            font-weight: bold;
            color: #555;
            margin-bottom: 5px;
            display: block;
        }

        .select-input,
        .text-input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            box-sizing: border-box;
            font-size: 14px;
        }

        .color-picker-row {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .color-input {
            width: 40px;
            height: 40px;
            padding: 0;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .color-text {
            flex: 1;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 6px;
            background: #f9f9f9;
            color: #666;
            font-size: 13px;
        }

        .range-row {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .range-slider {
            flex: 1;
        }

        .range-val {
            width: 40px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 5px;
            font-size: 12px;
        }
                .section-header-block {
            width: 100%;
            font-size: 18px;
            font-weight: 800;
            color: #333;
            border-bottom: 3px solid #007aff;
            padding-bottom: 8px;
            margin: 40px 0 20px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }


        .btn-save {
            background: #007aff;
            color: white;
            border: none;
            padding: 12px;
            width: 100%;
            border-radius: 8px;
            font-weight: bold;
            font-size: 16px;
            cursor: pointer;
            margin-bottom: 50px;
        }
    </style>
</head>

<body>

    <form action="<?= BASE_URL ?>settings/updateStyles" method="POST">
        <div class="container">

                        <!-- Header -->
            <div class="page-header">
                <a href="<?= BASE_URL ?>settings/edit" style="text-decoration:none; color:black; font-size:24px;">❮</a>
                <div class="header-title">Global Styles</div>
            </div>

            <!-- SECTION 1: COMMON STYLES    -->
            <div class="section-header-block">1. Common Styles (All Devices)</div>
            
            <div style="display:flex; flex-wrap:wrap; gap:20px;">
                <!-- Typography -->
                <div class="style-card" style="flex:1; min-width:280px;">
                    <div class="card-header">Typography</div>
                    <div class="control-group">
                        <label class="control-label">Font Family</label>
                        <select name="font_family" class="select-input">
                            <option value="Roboto" <?= ($styles['font_family'] ?? '') == 'Roboto' ? 'selected' : '' ?>>Roboto</option>
                            <option value="Open Sans" <?= ($styles['font_family'] ?? '') == 'Open Sans' ? 'selected' : '' ?>>Open Sans</option>
                            <option value="Montserrat" <?= ($styles['font_family'] ?? '') == 'Montserrat' ? 'selected' : '' ?>>Montserrat</option>
                            <option value="Inter" <?= ($styles['font_family'] ?? '') == 'Inter' ? 'selected' : '' ?>>Inter</option>
                            <option value="Lato" <?= ($styles['font_family'] ?? '') == 'Lato' ? 'selected' : '' ?>>Lato</option>
                            <option value="Poppins" <?= ($styles['font_family'] ?? '') == 'Poppins' ? 'selected' : '' ?>>Poppins</option>
                        </select>
                    </div>
                    <div class="control-group">
                        <label class="control-label">Body Size (px)</label>
                        <div class="range-row">
                            <input type="range" min="10" max="24" class="range-slider" value="<?= $styles['body_size'] ?? 14 ?>" oninput="this.nextElementSibling.value = this.value">
                            <input type="text" name="body_size" class="range-val" value="<?= $styles['body_size'] ?? 14 ?>">
                        </div>
                    </div>
                </div>

                <!-- Theme Colors -->
                <div class="style-card" style="flex:1; min-width:280px;">
                    <div class="card-header">Theme Colors</div>
                    <div class="control-group">
                        <label class="control-label">Primary Color</label>
                        <div class="color-picker-row">
                            <input type="color" name="primary_color" class="color-input" value="<?= $styles['primary_color'] ?? '#007aff' ?>">
                            <input type="text" class="color-text" value="<?= $styles['primary_color'] ?? '#007aff' ?>" readonly>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label">Background Color</label>
                        <div class="color-picker-row">
                            <input type="color" name="bg_color" class="color-input" value="<?= $styles['bg_color'] ?? '#ffffff' ?>">
                            <input type="text" class="color-text" value="<?= $styles['bg_color'] ?? '#ffffff' ?>" readonly>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Granular Button Controls (SAFE LIST ONLY) -->
            <div class="style-card">
                <div class="card-header">Button Styles (Granular Control)</div>
                <div style="display:flex; flex-wrap:wrap; gap:20px;">
                    
                    <!-- 1. Add to Cart -->
                    <div style="flex:1; min-width:200px;">
                        <h4 style="font-size:12px; color:#555; margin-bottom:5px;">Add to Cart Button</h4>
                        <div class="control-group">
                            <div class="color-picker-row">
                                <span style="font-size:10px; width:30px;">BG:</span>
                                <input type="color" name="btn_addcart_bg" class="color-input" value="<?= $styles['btn_addcart_bg'] ?? '#007aff' ?>">
                            </div>
                        </div>
                        <div class="control-group">
                            <div class="color-picker-row">
                                <span style="font-size:10px; width:30px;">Txt:</span>
                                <input type="color" name="btn_addcart_text" class="color-input" value="<?= $styles['btn_addcart_text'] ?? '#ffffff' ?>">
                            </div>
                        </div>
                    </div>

                    <!-- 2. Apply Filter -->
                    <div style="flex:1; min-width:200px;">
                        <h4 style="font-size:12px; color:#555; margin-bottom:5px;">Sidebar 'Apply'</h4>
                        <div class="control-group">
                            <div class="color-picker-row">
                                <span style="font-size:10px; width:30px;">BG:</span>
                                <input type="color" name="btn_apply_bg" class="color-input" value="<?= $styles['btn_apply_bg'] ?? '#4a148c' ?>">
                            </div>
                        </div>
                        <div class="control-group">
                            <div class="color-picker-row">
                                <span style="font-size:10px; width:30px;">Txt:</span>
                                <input type="color" name="btn_apply_text" class="color-input" value="<?= $styles['btn_apply_text'] ?? '#ffffff' ?>">
                            </div>
                        </div>
                    </div>

                    <!-- 3. Category/Nav -->
                    <div style="flex:1; min-width:200px;">
                        <h4 style="font-size:12px; color:#555; margin-bottom:5px;">Category Buttons</h4>
                        <div class="control-group">
                            <div class="color-picker-row">
                                <span style="font-size:10px; width:30px;">BG:</span>
                                <input type="color" name="btn_category_bg" class="color-input" value="<?= $styles['btn_category_bg'] ?? '#eeeeee' ?>">
                            </div>
                        </div>
                        <div class="control-group">
                            <div class="color-picker-row">
                                <span style="font-size:10px; width:30px;">Txt:</span>
                                <input type="color" name="btn_category_text" class="color-input" value="<?= $styles['btn_category_text'] ?? '#333333' ?>">
                            </div>
                        </div>
                    </div>

                    <!-- 4. Sale/Red -->
                    <div style="flex:1; min-width:200px;">
                        <h4 style="font-size:12px; color:#555; margin-bottom:5px;">Sale / Alert Buttons</h4>
                        <div class="control-group">
                            <div class="color-picker-row">
                                <span style="font-size:10px; width:30px;">BG:</span>
                                <input type="color" name="btn_sale_bg" class="color-input" value="<?= $styles['btn_sale_bg'] ?? '#ff0000' ?>">
                            </div>
                        </div>
                        <div class="control-group">
                            <div class="color-picker-row">
                                <span style="font-size:10px; width:30px;">Txt:</span>
                                <input type="color" name="btn_sale_text" class="color-input" value="<?= $styles['btn_sale_text'] ?? '#ffffff' ?>">
                            </div>
                        </div>
                    </div>
                    
                    <!-- 5. Review Link -->
                     <div style="flex:1; min-width:200px;">
                        <h4 style="font-size:12px; color:#555; margin-bottom:5px;">Review Link</h4>
                        <div class="control-group">
                            <div class="color-picker-row">
                                <span style="font-size:10px; width:30px;">BG:</span>
                                <input type="color" name="btn_review_bg" class="color-input" value="<?= $styles['btn_review_bg'] ?? '#ffffff' ?>">
                            </div>
                        </div>
                        <div class="control-group">
                            <div class="color-picker-row">
                                <span style="font-size:10px; width:30px;">Txt:</span>
                                <input type="color" name="btn_review_text" class="color-input" value="<?= $styles['btn_review_text'] ?? '#000000' ?>">
                            </div>
                        </div>
                    </div>

                     <!-- 6. Size Guide -->
                     <div style="flex:1; min-width:200px;">
                        <h4 style="font-size:12px; color:#555; margin-bottom:5px;">Size Guide Btn</h4>
                        <div class="control-group">
                            <div class="color-picker-row">
                                <span style="font-size:10px; width:30px;">BG:</span>
                                <input type="color" name="btn_sizeguide_bg" class="color-input" value="<?= $styles['btn_sizeguide_bg'] ?? '#dddddd' ?>">
                            </div>
                        </div>
                        <div class="control-group">
                            <div class="color-picker-row">
                                <span style="font-size:10px; width:30px;">Txt:</span>
                                <input type="color" name="btn_sizeguide_text" class="color-input" value="<?= $styles['btn_sizeguide_text'] ?? '#000000' ?>">
                            </div>
                        </div>
                    </div>

                    <!-- 7. Order Now -->
                    <div style="flex:1; min-width:200px;">
                        <h4 style="font-size:12px; color:#555; margin-bottom:5px;">Order Now Button</h4>
                        <div class="control-group">
                            <div class="color-picker-row">
                                <span style="font-size:10px; width:30px;">BG:</span>
                                <input type="color" name="btn_ordernow_bg" class="color-input" value="<?= $styles['btn_ordernow_bg'] ?? '#111111' ?>">
                            </div>
                        </div>
                        <div class="control-group">
                            <div class="color-picker-row">
                                <span style="font-size:10px; width:30px;">Txt:</span>
                                <input type="color" name="btn_ordernow_text" class="color-input" value="<?= $styles['btn_ordernow_text'] ?? '#ffffff' ?>">
                            </div>
                        </div>
                    </div>

                </div>
                <div style="margin-top:15px; font-size:11px; color:#777; font-style:italic;">
                    * Cart page payment buttons can be customized below.
                </div>
            </div>

            <div class="style-card">
                <div class="card-header">Cart Page Payment Buttons</div>
                <div style="display:flex; flex-wrap:wrap; gap:20px;">

                    <div style="flex:1; min-width:200px;">
                        <h4 style="font-size:12px; color:#555; margin-bottom:5px;">WhatsApp Button</h4>
                        <div class="control-group">
                            <div class="color-picker-row">
                                <span style="font-size:10px; width:30px;">BG:</span>
                                <input type="color" name="btn_cart_whatsapp_bg" class="color-input" value="<?= $styles['btn_cart_whatsapp_bg'] ?? '#25d366' ?>">
                            </div>
                        </div>
                        <div class="control-group">
                            <div class="color-picker-row">
                                <span style="font-size:10px; width:30px;">Txt:</span>
                                <input type="color" name="btn_cart_whatsapp_text" class="color-input" value="<?= $styles['btn_cart_whatsapp_text'] ?? '#ffffff' ?>">
                            </div>
                        </div>
                    </div>

                    <div style="flex:1; min-width:200px;">
                        <h4 style="font-size:12px; color:#555; margin-bottom:5px;">COD Button</h4>
                        <div class="control-group">
                            <div class="color-picker-row">
                                <span style="font-size:10px; width:30px;">BG:</span>
                                <input type="color" name="btn_cart_cod_bg" class="color-input" value="<?= $styles['btn_cart_cod_bg'] ?? '#111111' ?>">
                            </div>
                        </div>
                        <div class="control-group">
                            <div class="color-picker-row">
                                <span style="font-size:10px; width:30px;">Txt:</span>
                                <input type="color" name="btn_cart_cod_text" class="color-input" value="<?= $styles['btn_cart_cod_text'] ?? '#ffffff' ?>">
                            </div>
                        </div>
                    </div>

                    <div style="flex:1; min-width:200px;">
                        <h4 style="font-size:12px; color:#555; margin-bottom:5px;">PayHere Button</h4>
                        <div class="control-group">
                            <div class="color-picker-row">
                                <span style="font-size:10px; width:30px;">BG:</span>
                                <input type="color" name="btn_cart_payhere_bg" class="color-input" value="<?= $styles['btn_cart_payhere_bg'] ?? '#111111' ?>">
                            </div>
                        </div>
                        <div class="control-group">
                            <div class="color-picker-row">
                                <span style="font-size:10px; width:30px;">Txt:</span>
                                <input type="color" name="btn_cart_payhere_text" class="color-input" value="<?= $styles['btn_cart_payhere_text'] ?? '#ffffff' ?>">
                            </div>
                        </div>
                    </div>

                    <div style="flex:1; min-width:200px;">
                        <h4 style="font-size:12px; color:#555; margin-bottom:5px;">KOKO Button</h4>
                        <div class="control-group">
                            <div class="color-picker-row">
                                <span style="font-size:10px; width:30px;">BG:</span>
                                <input type="color" name="btn_cart_koko_bg" class="color-input" value="<?= $styles['btn_cart_koko_bg'] ?? '#fff3dc' ?>">
                            </div>
                        </div>
                        <div class="control-group">
                            <div class="color-picker-row">
                                <span style="font-size:10px; width:30px;">Txt:</span>
                                <input type="color" name="btn_cart_koko_text" class="color-input" value="<?= $styles['btn_cart_koko_text'] ?? '#111111' ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
<!-- Floating Elements -->
            <div class="style-card">
                <div class="card-header">Floating Elements</div>
                <div style="display:flex; flex-wrap:wrap; gap:20px;">
                    <div style="flex:1; min-width:200px;">
                        <h4 style="font-size:12px; color:#555; margin-bottom:5px;">Floating Cart Icon</h4>
                        <div class="control-group">
                            <div class="color-picker-row">
                                <span style="font-size:10px; width:30px;">BG:</span>
                                <input type="color" name="floating_cart_bg" class="color-input" value="<?= $styles['floating_cart_bg'] ?? '#7c4af0' ?>">
                            </div>
                        </div>
                        <div class="control-group">
                            <div class="color-picker-row">
                                <span style="font-size:10px; width:30px;">Icon:</span>
                                <input type="color" name="floating_cart_text" class="color-input" value="<?= $styles['floating_cart_text'] ?? '#ffffff' ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- SECTION 2: MOBILE STYLES    -->
            <div class="section-header-block">2. Mobile Styles</div>
            
            <div class="style-card">
                <div class="card-header">Mobile Navigation & Layout</div>
                <div style="display:flex; flex-wrap:wrap; gap:20px;">
                    <div style="flex:1;">
                        <div class="control-group">
                            <label class="control-label">Mobile Breakpoint (Max Width)</label>
                            <input type="number" name="bp_mobile" class="text-input" value="<?= $styles['bp_mobile'] ?? 480 ?>">
                        </div>
                         <div class="control-group">
                            <label class="control-label">Nav Background</label>
                            <div class="color-picker-row">
                                <input type="color" name="nav_mobile_bg" class="color-input" value="<?= $styles['nav_mobile_bg'] ?? '#ffffff' ?>">
                                <input type="text" class="color-text" value="<?= $styles['nav_mobile_bg'] ?? '#ffffff' ?>" readonly>
                            </div>
                        </div>
                    </div>
                    <div style="flex:1;">
                        <div class="control-group">
                            <label class="control-label">Nav Icon Color</label>
                             <div class="color-picker-row">
                                <input type="color" name="nav_mobile_icon_color" class="color-input" value="<?= $styles['nav_mobile_icon_color'] ?? '#999999' ?>">
                                <input type="text" class="color-text" value="<?= $styles['nav_mobile_icon_color'] ?? '#999999' ?>" readonly>
                            </div>
                        </div>
                        <div class="control-group">
                            <label class="control-label">Nav Active Color</label>
                             <div class="color-picker-row">
                                <input type="color" name="nav_mobile_active_color" class="color-input" value="<?= $styles['nav_mobile_active_color'] ?? '#555555' ?>">
                                <input type="text" class="color-text" value="<?= $styles['nav_mobile_active_color'] ?? '#555555' ?>" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="style-card">
                <div class="card-header">Mobile Search Bar</div>
                <div style="display:flex; flex-wrap:wrap; gap:20px;">
                    <div style="flex:1;">
                        <div class="control-group">
                            <label class="control-label">Search Background</label>
                            <div class="color-picker-row">
                                <input type="color" name="search_mobile_bg" class="color-input" value="<?= $styles['search_mobile_bg'] ?? '#ede7f6' ?>">
                                <input type="text" class="color-text" value="<?= $styles['search_mobile_bg'] ?? '#ede7f6' ?>" readonly>
                            </div>
                        </div>
                    </div>
                    <div style="flex:1;">
                        <div class="control-group">
                            <label class="control-label">Search Icon & Text</label>
                             <div class="color-picker-row">
                                <input type="color" name="search_mobile_icon" class="color-input" value="<?= $styles['search_mobile_icon'] ?? '#5e35b1' ?>">
                                <input type="text" class="color-text" value="<?= $styles['search_mobile_icon'] ?? '#5e35b1' ?>" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION 3: DESKTOP STYLES   -->
            <div class="section-header-block">3. Desktop Styles</div>

            <div class="style-card">
                <div class="card-header">Desktop Navigation & Grid</div>
                <div style="display:flex; flex-wrap:wrap; gap:20px;">
                     <div style="flex:1;">
                        <div class="control-group">
                            <label class="control-label">Desktop Breakpoint (Min Width)</label>
                            <input type="number" name="bp_desktop" class="text-input" value="<?= $styles['bp_desktop'] ?? 1024 ?>">
                        </div>
                         <div class="control-group">
                            <label class="control-label">Wrap Container Width (px)</label>
                            <input type="number" name="container_desktop" class="text-input" value="<?= $styles['container_desktop'] ?? 1200 ?>">
                        </div>
                    </div>
                     <div style="flex:1;">
                        <div class="control-group">
                            <label class="control-label">Nav Background</label>
                            <div class="color-picker-row">
                                <input type="color" name="nav_desktop_bg" class="color-input" value="<?= $styles['nav_desktop_bg'] ?? '#ffffff' ?>">
                                <input type="text" class="color-text" value="<?= $styles['nav_desktop_bg'] ?? '#ffffff' ?>" readonly>
                            </div>
                        </div>
                        <div class="control-group">
                             <label class="control-label">Nav Link Color</label>
                            <div class="color-picker-row">
                                <input type="color" name="nav_desktop_link_color" class="color-input" value="<?= $styles['nav_desktop_link_color'] ?? '#666666' ?>">
                                <input type="text" class="color-text" value="<?= $styles['nav_desktop_link_color'] ?? '#666666' ?>" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="style-card">
                <div class="card-header">Desktop Search Bar</div>
                <div style="display:flex; flex-wrap:wrap; gap:20px;">
                    <div style="flex:1;">
                        <div class="control-group">
                            <label class="control-label">Search Background</label>
                            <div class="color-picker-row">
                                <input type="color" name="search_desktop_bg" class="color-input" value="<?= $styles['search_desktop_bg'] ?? '#f5f5f5' ?>">
                                <input type="text" class="color-text" value="<?= $styles['search_desktop_bg'] ?? '#f5f5f5' ?>" readonly>
                            </div>
                        </div>
                    </div>
                    <div style="flex:1;">
                        <div class="control-group">
                            <label class="control-label">Search Icon & Text</label>
                             <div class="color-picker-row">
                                <input type="color" name="search_desktop_icon" class="color-input" value="<?= $styles['search_desktop_icon'] ?? '#333333' ?>">
                                <input type="text" class="color-text" value="<?= $styles['search_desktop_icon'] ?? '#333333' ?>" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Global Action -->
            <button type="submit" class="btn-save">Save All Global Styles</button>
        </div>
    </form>

    <script>
        // Simple helper to update text inputs when color picker changes
        document.querySelectorAll('input[type="color"]').forEach(input => {
            input.addEventListener('input', (e) => {
                e.target.nextElementSibling.value = e.target.value;
            });
        });
    </script>

</body>

</html>
