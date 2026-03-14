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
        .header-bar {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-bottom: 20px;
        }

        .back-circle {
            background: #000;
            color: white;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            justify-content: center;
            align-items: center;
            text-decoration: none;
        }

        .section-label {
            font-weight: bold;
            color: #555;
            margin-top: 20px;
            margin-bottom: 5px;
            display: block;
        }

        .sub-label {
            font-size: 11px;
            color: #999;
            margin-bottom: 10px;
            display: block;
        }

        /* Image Upload Blocks */
        .images-container {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }

        .main-img-box {
            flex: 1;
            background-color: #ffeaea;
            /* Pinkish */
            border-radius: 12px;
            text-align: center;
            padding: 20px;
            cursor: pointer;
            position: relative;
            min-height: 120px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .gallery-box {
            flex: 1;
            background-color: #f0f0f0;
            /* Gray */
            border-radius: 12px;
            text-align: center;
            padding: 20px;
            cursor: pointer;
            position: relative;
            min-height: 120px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .preview-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 12px;
            position: absolute;
            top: 0;
            left: 0;
            display: none;
        }

        .input-box {
            background: #f0f0f0;
            border: none;
            border-radius: 8px;
            padding: 12px 15px;
            width: 100%;
            font-size: 14px;
            margin-bottom: 15px;
            box-sizing: border-box;
        }

        .price-row {
            display: flex;
            gap: 15px;
        }

        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 26px;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 20px;
            width: 20px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked+.slider {
            background-color: #2196F3;
        }

        input:checked+.slider:before {
            transform: translateX(24px);
        }

        .btn-yellow {
            background-color: #d4ac0d;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 8px;
            width: 48%;
            font-weight: bold;
            cursor: pointer;
        }

        .btn-blue {
            background-color: #007aff;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 8px;
            width: 48%;
            font-weight: bold;
            cursor: pointer;
            float: right;
        }

        /* Modal for Variation Selection */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            display: none;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: white;
            width: 90%;
            max-width: 400px;
            padding: 20px;
            border-radius: 15px;
        }

        .var-group {
            margin-bottom: 15px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }

        .var-title {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .var-opt {
            display: inline-block;
            padding: 5px 10px;
            background: #eee;
            border-radius: 5px;
            margin: 3px;
            cursor: pointer;
            user-select: none;
        }

        .var-opt.selected {
            background: #007aff;
            color: white;
        }

                /* Loading */
        .loading-screen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.9);
            z-index: 2000;
            display: none;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #007aff;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    
            /* Multi-Category List Styles */
        .dropdown-trigger {
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f0f0f0; /* Matches input-box */
            margin-bottom: 0 !important; /* Touch the list below */
            border-bottom-left-radius: 0;
            border-bottom-right-radius: 0;
        }

        .cat-list-box {
            background: #fff;
            border: 1px solid #ccc;
            border-top: none; /* Merge with trigger */
            border-radius: 0 0 8px 8px;
            padding: 10px;
            max-height: 200px;
            overflow-y: auto;
            margin-bottom: 20px;
            display: none; /* Hidden by default */
        }

        .cat-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 6px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .cat-item:last-child {
            border-bottom: none;
        }

        .cat-checkbox {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .cat-name {
            font-size: 14px;
            color: #333;
            cursor: pointer;
        }

        .sub-cat-indent {
            margin-left: 25px;
            border-left: 2px solid #eee;
            padding-left: 10px;
        }

        .stock-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }

        .stock-panel {
            background: #fff;
            border: 1px solid #e8e8e8;
            border-radius: 12px;
            padding: 14px;
            margin-top: 12px;
        }

        .stock-row {
            display: flex;
            gap: 12px;
            align-items: center;
            margin-bottom: 12px;
        }

        .stock-row > * {
            flex: 1;
        }

        .variant-stock-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
            font-size: 12px;
        }

        .variant-stock-table th,
        .variant-stock-table td {
            border-bottom: 1px solid #f0f0f0;
            padding: 10px 8px;
            text-align: left;
            vertical-align: top;
        }

        .variant-stock-table th {
            color: #777;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .variant-stock-table input,
        .variant-stock-table select {
            width: 100%;
            margin-bottom: 0;
            font-size: 12px;
            padding: 8px 10px;
            border-radius: 8px;
            border: 1px solid #ddd;
            background: #fff;
            box-sizing: border-box;
        }

        .variant-stock-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 12px;
        }

        .btn-soft {
            background: #f3f6ff;
            color: #1f5eff;
            border: 1px solid #d9e4ff;
            padding: 10px 12px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 700;
        }

        .btn-soft-danger {
            background: #fff1f0;
            color: #d83b31;
            border: 1px solid #ffd6d1;
        }
    </style>
</head>

<body>

    <!-- Global Loader Injection -->
    <?php include 'views/admin/partials/loader.php'; ?>


    <!-- Form -->
    <form action="<?= BASE_URL ?>product/<?= isset($mode) && $mode === 'edit' ? 'update' : 'store' ?>" method="POST"
        enctype="multipart/form-data" id="productForm">
        <div class="container" style="padding-bottom: 80px;">

            <div class="header-bar">
                <a href="<?= BASE_URL ?>product/index" class="back-circle">❮</a>
                <div>
                    <h2 style="margin:0;"><?= isset($mode) && $mode === 'edit' ? 'Edit Product' : 'Add Product' ?></h2>
                    <p style="margin:0; font-size:11px; color:#888;">List New Items in One Minute...</p>
                </div>
            </div>

            <?php if (isset($mode) && $mode === 'edit'): ?>
                <input type="hidden" name="id" value="<?= $product['id'] ?>">
                <input type="hidden" name="current_main_image" value="<?= $product['main_image'] ?>">
            <?php endif; ?>

            <!-- Images -->
            <span class="section-label">Product Images</span>
            <span class="sub-label">Maximum Size of each photo to upload: 800Kb</span>

            <div class="images-container">
                <!-- Main Image -->
                <div class="main-img-box" onclick="document.getElementById('mainImgInput').click()">
                    <?php if (isset($mode) && $mode === 'edit' && !empty($product['main_image'])): ?>
                        <img id="mainPreview" class="preview-img"
                            src="<?= BASE_URL ?>assets/uploads/<?= $product['main_image'] ?>" style="display:block;">
                        <div id="mainPlaceholder" style="display:none;">
                        <?php else: ?>
                            <img id="mainPreview" class="preview-img">
                            <div id="mainPlaceholder">
                            <?php endif; ?>
                            <div style="font-size:24px;">📷</div>
                            <p style="font-size:10px; color:#555;">Tap here to<br>upload a photo</p>
                        </div>
                        <input type="file" name="main_image" id="mainImgInput" style="display:none;" accept="image/*"
                            <?= (isset($mode) && $mode === 'edit' && !empty($product['main_image'])) ? '' : 'required' ?>>
                    </div>

                    <!-- Gallery -->
                    <div class="gallery-box" onclick="document.getElementById('galImgInput').click()">
                        <!-- Show count if selected -->
                        <div id="galPlaceholder">
                            <div style="font-size:24px;">📷 📷 📷</div>
                            <p style="font-size:10px; color:#555;">Tap here to upload photos<br>Max: 10 Photos</p>
                        </div>
                        <p id="galCount" style="display:none; font-weight:bold; color:#007aff;">0 Selected</p>
                        <input type="file" name="gallery_images[]" id="galImgInput" style="display:none;"
                            accept="image/*" multiple>
                    </div>
                </div>

                <!-- Category -->
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <span class="section-label">Select Categories <span style="color:red">*</span></span>
                    <a href="javascript:void(0)"
                        onclick="openIframeModal('<?= BASE_URL ?>category/index', 'Manage Categories')"
                        style="font-size:12px; color:#007aff; text-decoration:none; font-weight:600;">+ Add / Manage
                        Categories</a>
                </div>
                                <!-- Hidden Input for Backward Compatibility (Primary Category) -->
                <input type="hidden" name="category_id" id="primaryCatInput" required
                    value="<?= $product['category_id'] ?? '' ?>">

               
                    <!-- Multi-Check Dropdown Trigger -->
                <div class="input-box dropdown-trigger" onclick="toggleCatDropdown()">
                    <span id="catTriggerText">Select Categories...</span>
                    <span id="catArrow" style="font-size:12px; color:#999;">▼</span>
                </div>

                <!-- Multi-Check List (Hidden) -->
                <div class="cat-list-box" id="catListContainer">
                    <?php foreach ($categories as $cat): ?>
                        <?php if (!$cat['parent_id']): ?>
                            <!-- Main Category -->
                            <div class="cat-item">
                                <input type="checkbox" name="categories[]" value="<?= $cat['id'] ?>"
                                    class="cat-checkbox" onchange="updatePrimaryCat()"
                                    <?= ( (isset($product['category_id']) && $product['category_id'] == $cat['id']) || (isset($product['categories']) && in_array($cat['id'], $product['categories'])) ) ? 'checked' : '' ?>>
                                <span class="cat-name" onclick="this.previousElementSibling.click()">
                                    <?= htmlspecialchars($cat['name']) ?>
                                </span>
                            </div>

                            <!-- Sub Categories -->
                            <?php foreach ($categories as $sub): ?>
                                <?php if ($sub['parent_id'] == $cat['id']): ?>
                                    <div class="cat-item sub-cat-indent">
                                        <input type="checkbox" name="categories[]" value="<?= $sub['id'] ?>"
                                            class="cat-checkbox" onchange="updatePrimaryCat()"
                                            <?= ( (isset($product['category_id']) && $product['category_id'] == $sub['id']) || (isset($product['categories']) && in_array($sub['id'], $product['categories'])) ) ? 'checked' : '' ?>>
                                        <span class="cat-name" onclick="this.previousElementSibling.click()">
                                            <?= htmlspecialchars($sub['name']) ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>


                <!-- Info -->
                <span class="section-label">Product Title <span style="color:red">*</span></span>
                <input type="text" name="title" class="input-box" placeholder="Enter product name here..."
                    value="<?= htmlspecialchars($product['title'] ?? '') ?>" required>

                <span class="section-label">Price</span>
                <div class="price-row">
                    <input type="number" name="price" class="input-box" placeholder="Normal Price" step="0.01"
                        value="<?= $product['price'] ?? '' ?>" required>
                    <input type="number" name="sale_price" class="input-box" placeholder="Discounted Price" step="0.01"
                        style="background:#ffeaea;" value="<?= $product['sale_price'] ?? '' ?>">
                </div>

                <span class="section-label">Product Weight (g)</span>
                <input type="number" name="weight_grams" class="input-box" min="0" step="1"
                    placeholder="Enter product weight in grams"
                    value="<?= htmlspecialchars((string) ($product['weight_grams'] ?? '0')) ?>">

                <span class="section-label">Product Description</span>
                <textarea name="description" class="input-box" rows="4"
                    placeholder="You can use external links, emojis... 🌸"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>

                <!-- Size Guide -->
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <span class="section-label">Size Guide</span>
                    <a href="javascript:void(0)"
                        onclick="openIframeModal('<?= BASE_URL ?>sizeGuide/index', 'Manage Size Guides')"
                        style="font-size:12px; color:#007aff; text-decoration:none; font-weight:600;">+ Add / Manage
                        Guides</a>
                </div>
                <select name="size_guide_id" class="input-box">
                    <option value="">+ Click here to select Size Guides</option>
                    <?php foreach ($sizeGuides as $sg): ?>
                        <option value="<?= $sg['id'] ?>" <?= (isset($product['size_guide_id']) && $product['size_guide_id'] == $sg['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($sg['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <!-- SKU -->
                <span class="section-label">Product Code (SKU)</span>
                <input type="text" name="sku" class="input-box" placeholder="Enter product name here..."
                    value="<?= htmlspecialchars($product['sku'] ?? '') ?>">

                <!-- Featured -->
                <span class="section-label">Featured Product</span>
                <label class="toggle-switch">
                    <input type="checkbox" name="is_featured" <?= (isset($product['is_featured']) && $product['is_featured']) ? 'checked' : '' ?>>
                    <span class="slider"></span>
                </label>

                <span class="section-label" style="margin-top:20px;">Free Shipping</span>
                <label class="toggle-switch">
                    <input type="checkbox" name="free_shipping" <?= !empty($product['free_shipping']) ? 'checked' : '' ?>>
                    <span class="slider"></span>
                </label>

                <span class="section-label" style="margin-top:20px;">Stock Management</span>
                <div class="stock-grid">
                    <div>
                        <span class="sub-label">Choose how this product should be sold</span>
                        <select name="stock_mode" id="stockModeInput" class="input-box" onchange="toggleStockPanels()">
                            <?php $stockMode = $product['stock_mode'] ?? 'always_in_stock'; ?>
                            <option value="always_in_stock" <?= $stockMode === 'always_in_stock' ? 'selected' : '' ?>>Always in Stock</option>
                            <option value="track_stock" <?= $stockMode === 'track_stock' ? 'selected' : '' ?>>Track Product Stock</option>
                            <option value="manual_out_of_stock" <?= $stockMode === 'manual_out_of_stock' ? 'selected' : '' ?>>Manual In/Out of Stock</option>
                        </select>
                    </div>
                    <div>
                        <span class="sub-label">Manual status used when not tracking quantity</span>
                        <select name="manual_stock_status" id="manualStockStatusInput" class="input-box">
                            <?php $manualStockStatus = $product['manual_stock_status'] ?? 'in_stock'; ?>
                            <option value="in_stock" <?= $manualStockStatus === 'in_stock' ? 'selected' : '' ?>>In Stock</option>
                            <option value="out_of_stock" <?= $manualStockStatus === 'out_of_stock' ? 'selected' : '' ?>>Out of Stock</option>
                        </select>
                    </div>
                </div>

                <div id="simpleStockPanel" class="stock-panel">
                    <div class="stock-row">
                        <div>
                            <span class="sub-label">Available quantity</span>
                            <input type="number" name="stock_qty" class="input-box" min="0" step="1"
                                value="<?= htmlspecialchars((string) ($product['stock_qty'] ?? '0')) ?>">
                        </div>
                        <div>
                            <span class="sub-label">Low stock alert threshold</span>
                            <input type="number" name="low_stock_threshold" class="input-box" min="0" step="1"
                                value="<?= htmlspecialchars((string) ($product['low_stock_threshold'] ?? '5')) ?>">
                        </div>
                    </div>
                </div>

                <div id="variantStockPanel" class="stock-panel">
                    <div style="display:flex; justify-content:space-between; gap:12px; align-items:center;">
                        <div>
                            <strong style="display:block; margin-bottom:4px;">Variation Stock Matrix</strong>
                            <span class="sub-label" style="margin:0;">Create only the exact variation combinations you really sell.</span>
                        </div>
                    </div>
                    <div class="variant-stock-actions">
                        <button type="button" class="btn-soft" onclick="generateVariantCombinations()">Generate Selected Combinations</button>
                        <button type="button" class="btn-soft btn-soft-danger" onclick="clearVariantCombinations()">Clear Matrix</button>
                    </div>
                    <table class="variant-stock-table">
                        <thead>
                            <tr>
                                <th>Combination</th>
                                <th>SKU</th>
                                <th>Mode</th>
                                <th>Qty</th>
                                <th>Low Stock</th>
                                <th>Status</th>
                                <th>Active</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="variantStockTableBody">
                            <tr id="variantStockEmptyState">
                                <td colspan="8" style="color:#777;">No exact combinations yet. Select variation values, then generate the combinations you actually sell.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div style="margin-top: 30px;">
                    <button type="button" class="btn-yellow" onclick="openVarModal()">Add Variations</button>
                    <button type="submit" class="btn-blue" onclick="showGlobalLoader()">Publish</button>
                </div>

            </div>

            <!-- Variations Hidden Inputs container -->
            <div id="hiddenVars"></div>
            <input type="hidden" name="variant_stocks_json" id="variantStocksJson"
                value='<?= htmlspecialchars(json_encode($product["variant_stocks"] ?? [], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), ENT_QUOTES) ?>'>

            <!-- Variations Modal -->
            <div class="modal-overlay" id="varModal">
                <div class="modal-content">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <h3 style="margin:0;">Select Variations</h3>
                        <a href="javascript:void(0)" onclick="openIframeModal('<?= BASE_URL ?>variation/index', 'Manage Variations')"
                            style="font-size:12px; color:#007aff; text-decoration:none;">+ Manage Variations</a>
                    </div>
                    <p style="color:#666; font-size:12px;">Tap to select available options</p>

                    <div style="max-height: 300px; overflow-y: auto;" id="variationListContainer">
                        <?php foreach ($variations as $var): ?>
                            <div class="var-group">
                                <div class="var-title">
                                    <?= htmlspecialchars($var['name']) ?>
                                </div>
                                <div>
                                    <?php foreach ($var['values'] as $val): ?>
                                        <?php
                                        // Check if this value is selected in the product data
                                        $selected = '';
                                        if (isset($product['variations']) && is_array($product['variations'])) {
                                            // $product['variations'] is grouped: 'Color' => [[id=X, value=Y]]
                                            // We need to check if $val['id'] exists in any of the grouped arrays
                                            foreach ($product['variations'] as $group) {
                                                foreach ($group as $gItem) {
                                                    if ($gItem['id'] == $val['id']) {
                                                        $selected = 'selected';
                                                        break 2;
                                                    }
                                                }
                                            }
                                        }
                                        ?>
                                        <div class="var-opt <?= $selected ?>" data-id="<?= $var['id'] ?>_<?= $val['id'] ?>"
                                            onclick="toggleVar(this)">
                                            <?= htmlspecialchars($val['value']) ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div style="margin-top:20px; text-align:right;">
                        <button type="button" class="btn-blue" style="width:100%;"
                            onclick="closeVarModal()">Done</button>
                    </div>
                </div>
            </div>

            <!-- Universal Iframe Modal -->
            <div class="modal-overlay" id="universalModal" style="z-index: 1001;">
                <div class="modal-content"
                    style="width: 95%; max-width: 600px; height: 80vh; display:flex; flex-direction:column; padding:0;">
                    <div
                        style="padding: 15px; border-bottom: 1px solid #eee; display:flex; justify-content:space-between; align-items:center;">
                        <h3 style="margin:0;" id="universalModalTitle">Manage Items</h3>
                        <button type="button" onclick="closeIframeModal()"
                            style="border:none; background:none; font-size:20px; cursor:pointer;">&times;</button>
                    </div>
                    <iframe id="universalFrame" src="" style="flex:1; border:none; width:100%;"></iframe>
                </div>
            </div>

    </form>

    <script>
        const initialVariantStockRows = <?= json_encode($product['variant_stocks'] ?? [], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
        let variantStockRows = Array.isArray(initialVariantStockRows) ? initialVariantStockRows : [];

        function normalizeVariantKey(values) {
            return values
                .slice()
                .sort((a, b) => Number(a.variation_id) - Number(b.variation_id))
                .map(v => `${v.variation_id}:${v.variation_value_id}`)
                .join('|');
        }

        function renderVariantStockRows() {
            const tbody = document.getElementById('variantStockTableBody');
            if (!tbody) return;

            if (!variantStockRows.length) {
                tbody.innerHTML = `<tr id="variantStockEmptyState"><td colspan="8" style="color:#777;">No exact combinations yet. Select variation values, then generate the combinations you actually sell.</td></tr>`;
                syncVariantStocksJson();
                return;
            }

            tbody.innerHTML = variantStockRows.map((row, index) => `
                <tr>
                    <td>
                        <div style="font-weight:700; color:#111;">${row.combination_label || row.combination_key}</div>
                        <div style="font-size:11px; color:#888; margin-top:4px;">${row.combination_key}</div>
                    </td>
                    <td><input type="text" value="${escapeHtml(row.sku || '')}" onchange="updateVariantRow(${index}, 'sku', this.value)"></td>
                    <td>
                        <select onchange="updateVariantRow(${index}, 'stock_mode', this.value)">
                            <option value="always_in_stock" ${row.stock_mode === 'always_in_stock' ? 'selected' : ''}>Always In Stock</option>
                            <option value="track_stock" ${row.stock_mode === 'track_stock' ? 'selected' : ''}>Track Stock</option>
                            <option value="manual_out_of_stock" ${row.stock_mode === 'manual_out_of_stock' ? 'selected' : ''}>Manual Status</option>
                        </select>
                    </td>
                    <td><input type="number" min="0" step="1" value="${Number(row.stock_qty || 0)}" onchange="updateVariantRow(${index}, 'stock_qty', this.value)"></td>
                    <td><input type="number" min="0" step="1" value="${Number(row.low_stock_threshold || 5)}" onchange="updateVariantRow(${index}, 'low_stock_threshold', this.value)"></td>
                    <td>
                        <select onchange="updateVariantRow(${index}, 'manual_stock_status', this.value)">
                            <option value="in_stock" ${(row.manual_stock_status || 'in_stock') === 'in_stock' ? 'selected' : ''}>In Stock</option>
                            <option value="out_of_stock" ${(row.manual_stock_status || 'in_stock') === 'out_of_stock' ? 'selected' : ''}>Out of Stock</option>
                        </select>
                    </td>
                    <td style="text-align:center;">
                        <input type="checkbox" ${row.is_active ? 'checked' : ''} onchange="updateVariantRow(${index}, 'is_active', this.checked)">
                    </td>
                    <td>
                        <button type="button" class="btn-soft btn-soft-danger" onclick="removeVariantRow(${index})">Remove</button>
                    </td>
                </tr>
            `).join('');
            syncVariantStocksJson();
        }

        function escapeHtml(value) {
            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function updateVariantRow(index, key, value) {
            if (!variantStockRows[index]) return;
            variantStockRows[index][key] = (key === 'stock_qty' || key === 'low_stock_threshold') ? Number(value || 0) : value;
            if (key === 'is_active') {
                variantStockRows[index][key] = !!value;
            }
            syncVariantStocksJson();
        }

        function removeVariantRow(index) {
            variantStockRows.splice(index, 1);
            renderVariantStockRows();
        }

        function clearVariantCombinations() {
            variantStockRows = [];
            renderVariantStockRows();
        }

        function getSelectedVariationGroups() {
            const grouped = {};
            document.querySelectorAll('.var-opt.selected').forEach(el => {
                const [variationId, variationValueId] = (el.dataset.id || '').split('_');
                const variationName = el.closest('.var-group')?.querySelector('.var-title')?.textContent?.trim() || 'Variation';
                if (!grouped[variationId]) {
                    grouped[variationId] = {
                        variation_id: Number(variationId),
                        variation_name: variationName,
                        values: []
                    };
                }
                grouped[variationId].values.push({
                    variation_id: Number(variationId),
                    variation_value_id: Number(variationValueId),
                    variation_name: variationName,
                    variation_value: el.textContent.trim()
                });
            });

            return Object.values(grouped).filter(group => group.values.length > 0);
        }

        function cartesianProduct(groups, index = 0, current = [], result = []) {
            if (index >= groups.length) {
                result.push(current.slice());
                return result;
            }

            groups[index].values.forEach(value => {
                current.push(value);
                cartesianProduct(groups, index + 1, current, result);
                current.pop();
            });
            return result;
        }

        function generateVariantCombinations() {
            const groups = getSelectedVariationGroups();
            if (!groups.length) {
                alert('Select variation values first to generate exact combinations.');
                return;
            }

            const combos = cartesianProduct(groups);
            const existingKeys = new Set(variantStockRows.map(row => row.combination_key));

            combos.forEach(combo => {
                const combinationKey = normalizeVariantKey(combo);
                if (existingKeys.has(combinationKey)) {
                    return;
                }

                variantStockRows.push({
                    combination_key: combinationKey,
                    combination_label: combo.map(item => `${item.variation_name}: ${item.variation_value}`).join(' / '),
                    sku: '',
                    stock_mode: 'track_stock',
                    stock_qty: 0,
                    low_stock_threshold: 5,
                    manual_stock_status: 'in_stock',
                    is_active: true,
                    values: combo
                });
                existingKeys.add(combinationKey);
            });

            renderVariantStockRows();
        }

        function syncVariantStocksJson() {
            const input = document.getElementById('variantStocksJson');
            if (input) {
                input.value = JSON.stringify(variantStockRows);
            }
        }

        function toggleStockPanels() {
            const stockMode = document.getElementById('stockModeInput')?.value || 'always_in_stock';
            const simplePanel = document.getElementById('simpleStockPanel');
            const variantPanel = document.getElementById('variantStockPanel');
            const selectedGroups = getSelectedVariationGroups();
            const hasMultipleGroups = selectedGroups.length > 0;

            if (simplePanel) {
                simplePanel.style.display = stockMode === 'track_stock' ? 'block' : 'none';
            }
            if (variantPanel) {
                variantPanel.style.display = hasMultipleGroups ? 'block' : 'none';
            }
        }

                // Auto-Refresh Logic (Added for Shop Owner Auto Updates)
            window.refreshCategories = function() {
            fetch('<?= BASE_URL ?>category/get_json')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('catListContainer');
                    // Get currently checked IDs to preserve selection
                    const checkedIds = Array.from(document.querySelectorAll('input[name="categories[]"]:checked')).map(cb => cb.value);
                    
                    let html = '';
                    
                    // 1. Main Categories
                    data.filter(c => !c.parent_id).forEach(main => {
                        const isChecked = checkedIds.includes(String(main.id)) ? 'checked' : '';
                        html += `
                        <div class="cat-item">
                            <input type="checkbox" name="categories[]" value="${main.id}" class="cat-checkbox" onchange="updatePrimaryCat()" ${isChecked}>
                            <span class="cat-name" onclick="this.previousElementSibling.click()">${main.name}</span>
                        </div>`;
                        
                        // 2. Sub Categories
                        data.filter(sub => sub.parent_id == main.id).forEach(child => {
                            const isSubChecked = checkedIds.includes(String(child.id)) ? 'checked' : '';
                            html += `
                            <div class="cat-item sub-cat-indent">
                                <input type="checkbox" name="categories[]" value="${child.id}" class="cat-checkbox" onchange="updatePrimaryCat()" ${isSubChecked}>
                                <span class="cat-name" onclick="this.previousElementSibling.click()">${child.name}</span>
                            </div>`;
                        });
                    });
                    
                    container.innerHTML = html;
                });
        };

        // NEW: Sync Checkboxes with Hidden Primary Input AND Update Label
        function updatePrimaryCat() {
            const checkboxes = document.querySelectorAll('input[name="categories[]"]:checked');
            const primaryInput = document.getElementById('primaryCatInput');
            const label = document.getElementById('catTriggerText');
            
            if (checkboxes.length > 0) {
                primaryInput.value = checkboxes[0].value;
                // Update Label
                label.innerText = checkboxes.length + " Category Selected"; 
                if(checkboxes.length > 1) label.innerText = checkboxes.length + " Categories Selected";
                
                label.style.fontWeight = "bold";
                label.style.color = "#007aff";
            } else {
                primaryInput.value = "";
                label.innerText = "Select Categories...";
                label.style.fontWeight = "normal";
                label.style.color = "#333";
            }
        }

        function toggleCatDropdown() {
            const box = document.getElementById('catListContainer');
            const arrow = document.getElementById('catArrow');
            if (box.style.display === 'block') {
                box.style.display = 'none';
                arrow.innerText = '▼';
            } else {
                box.style.display = 'block';
                arrow.innerText = '▲';
            }
        }




        window.refreshSizeGuides = function() {
            fetch('<?= BASE_URL ?>sizeGuide/get_json')
                .then(response => response.json())
                .then(data => {
                    const select = document.querySelector('select[name="size_guide_id"]');
                    const currentValue = select.value;
                    let html = '<option value="">+ Click here to select Size Guides</option>';
                    
                    data.forEach(sg => {
                        html += `<option value="${sg.id}">${sg.name}</option>`;
                    });
                    
                    select.innerHTML = html;
                    select.value = currentValue;
                });
        };

        window.refreshVariations = function() {
             fetch('<?= BASE_URL ?>variation/get_json')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('variationListContainer');
                    let html = '';
                    
                    data.forEach(varItem => {
                        html += `
                        <div class="var-group">
                            <div class="var-title">${varItem.name}</div>
                            <div>`;
                            
                        if(varItem.values) {
                            varItem.values.forEach(val => {
                                // Note: We lose 'selected' state on refresh for new items, 
                                // but existing selection logic handled via hidden inputs won't be visually broken
                                // until re-opened. Major goal is to see NEW items.
                                html += `<div class="var-opt" data-id="${varItem.id}_${val.id}" onclick="toggleVar(this)">${val.value}</div>`;
                            });
                        }
                        
                        html += `</div></div>`;
                    });
                    
                    container.innerHTML = html;
                    
                    // Re-apply selections if needed (optional advanced step), 
                    // for now we just want to see the new options.
                });
        };

        // Image Preview Logic
        document.getElementById('mainImgInput').addEventListener('change', function (e) {
            if (e.target.files && e.target.files[0]) {
                let reader = new FileReader();
                reader.onload = function (evt) {
                    const img = document.getElementById('mainPreview');
                    img.src = evt.target.result;
                    img.style.display = 'block';
                    document.getElementById('mainPlaceholder').style.display = 'none';
                }
                reader.readAsDataURL(e.target.files[0]);
            }
        });

        document.getElementById('galImgInput').addEventListener('change', function (e) {
            const count = e.target.files.length;
            if (count > 0) {
                document.getElementById('galPlaceholder').style.display = 'none';
                const txt = document.getElementById('galCount');
                txt.style.display = 'block';
                txt.innerText = count + " Photos Selected";
            }
        });

        // Modal Logic
        function openVarModal() { document.getElementById('varModal').style.display = 'flex'; }

        function closeVarModal() {
            document.getElementById('varModal').style.display = 'none';
            populateHiddenVars();
        }

        function toggleVar(el) {
            el.classList.toggle('selected');
            toggleStockPanels();
        }

                // Universal Modal Logic (Fixed Glitch + Loader)
        function openIframeModal(url, title) {
            showGlobalLoader(); // Show loader immediately
            document.getElementById('universalModalTitle').innerText = title;
            const frame = document.getElementById('universalFrame');
            
            // Clear previous source to prevent "ghost" content
            frame.src = 'about:blank';
            
            frame.onload = function() {
                hideGlobalLoader(); // Hide when new content is ready
            };
            
            frame.src = url;
            document.getElementById('universalModal').style.display = 'flex';
        }

        function closeIframeModal() {
            document.getElementById('universalModal').style.display = 'none';
            document.getElementById('universalFrame').src = 'about:blank'; // Reset to blank
        }


        // Convert selections to hidden inputs for form submission
        function populateHiddenVars() {
            const container = document.getElementById('hiddenVars');
            container.innerHTML = '';
            const selected = document.querySelectorAll('.var-opt.selected');

            selected.forEach(el => {
                const val = el.getAttribute('data-id'); // varId_valId
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'selected_variations[]';
                input.value = val;
                container.appendChild(input);
            });

            // Update button text to show count
            const btn = document.querySelector('.btn-yellow');
            if (selected.length > 0) {
                btn.innerText = "Variations (" + selected.length + ")";
            } else {
                btn.innerText = "Add Variations";
            }
        }

                // Form Submit Loading (Global)
        document.getElementById('productForm').addEventListener('submit', function () {
            showGlobalLoader();
        });

        // Trigger Loader on Image Uploads (Visual Feedback)
        document.getElementById('mainImgInput').addEventListener('change', function() {
            if(this.files.length > 0) showGlobalLoader();
            // Loader hides automatically via timeout in preview logic or manually below if instant
            setTimeout(hideGlobalLoader, 1000); // Simulate network delay for effect
        });

        document.getElementById('galImgInput').addEventListener('change', function() {
            if(this.files.length > 0) showGlobalLoader();
            setTimeout(hideGlobalLoader, 1000);
        });

        window.addEventListener('load', function () {
            populateHiddenVars();
            updatePrimaryCat(); // Set initial label state
            renderVariantStockRows();
            toggleStockPanels();
        });
    </script>
</body>

</html>
