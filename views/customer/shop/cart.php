<?php
$hide_mobile_welcome = true;
require_once ROOT_PATH . 'helpers/ImageHelper.php';
require_once 'views/layouts/customer_header.php';
$currency = $settings['currency_symbol'] ?? 'LKR';
?>

<div class="home-layout">
    <?php include 'views/customer/partials/sidebar.php'; ?>

    <main class="main-content" style="padding-bottom: 20px; align-self: flex-start; margin-top: 0;">

        <?php if (!empty($_SESSION['order_error'])): ?>
            <div style="margin: 0 20px 18px; padding: 14px 16px; border-radius: 14px; background: #fff4f2; color: #c44c35; font-size: 13px; font-weight: 600;">
                <?= htmlspecialchars($_SESSION['order_error']) ?>
            </div>
            <?php unset($_SESSION['order_error']); ?>
        <?php endif; ?>

        <div class="mobile-header-custom d-lg-none" style="padding: 20px 20px 0 20px; margin-bottom: 20px;">
            <div style="font-size: 11px; color: #888; margin-bottom: 15px;">Home > Cart</div>
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <a href="javascript:history.back()" style="width: 35px; height: 35px; background: #000; border-radius: 50%; display: flex; align-items: center; justify-content: center; text-decoration: none; color: white;">
                        <i class="fas fa-chevron-left" style="font-size: 14px;"></i>
                    </a>
                    <div>
                        <h1 style="font-size: 20px; font-weight: 800; margin: 0; line-height: 1;">YOUR CART</h1>
                        <p style="font-size: 12px; color: #888; margin: 0;">Your selections are amazing..!</p>
                    </div>
                </div>
                <button onclick="clearCart()" style="background: none; border: none; color: #FF3B30; font-weight: 600; font-size: 13px; cursor: pointer;">
                    Clear All
                </button>
            </div>
        </div>

        <div class="d-none d-lg-flex" style="align-items: center; justify-content: space-between; margin-bottom: 30px; padding-bottom: 10px;">
            <div style="display: flex; align-items: baseline; gap: 15px;">
                <h1 style="font-size: 28px; font-weight: 800; color: #000; margin: 0;">My Cart</h1>
                <a href="javascript:void(0)" onclick="clearCart()" style="font-size: 13px; text-decoration: underline; color: #FF3B30; font-weight: 600;">
                    Clear All
                </a>
            </div>
        </div>

        <div class="cart-desktop-grid">
            <div id="cartItemsContainer" style="padding: 10px 20px; min-height: 300px;">
                <?php if (empty($cart)): ?>
                    <p style="text-align: center; color: #999; margin-top: 50px;">Your cart is empty.</p>
                <?php else: ?>
                    <?php
                    $subtotal = 0;
                    foreach ($cart as $index => $item):
                        $itemTotal = $item['price'] * $item['qty'];
                        $subtotal += $itemTotal;
                    ?>
                        <div class="cart-item" style="display: flex; align-items: center; gap: 15px; background: #fff; padding: 15px; border-radius: 20px; position: relative; margin-bottom: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.03);">
                            <?php
                            $cartImageName = basename((string) parse_url((string) ($item['img'] ?? ''), PHP_URL_PATH));
                            $cartFallback = (string) ($item['img'] ?? '');
                            ?>
                            <?= ImageHelper::renderResponsivePicture(
                                $cartImageName,
                                $cartFallback,
                                [
                                    'alt' => $item['title'] ?? 'Product',
                                    'loading' => 'lazy',
                                    'decoding' => 'async',
                                    'fetchpriority' => 'low',
                                    'style' => 'width: 70px; height: 70px; border-radius: 12px; object-fit: cover; background: #f0f0f0;'
                                ],
                                'admin_thumb'
                            ) ?>
                            <div style="flex: 1;">
                                <h4 style="font-size: 14px; font-weight: 700; margin: 0 0 5px 0;"><?= htmlspecialchars($item['title']) ?></h4>
                                <?php if (!empty($item['is_free_shipping'])): ?>
                                    <div class="free-shipping-badge" style="margin-bottom:6px;">Free Shipping</div>
                                <?php endif; ?>
                                <div style="font-size: 13px; font-weight: 700; color: #E4405F; margin-bottom: 3px;">
                                    <?= htmlspecialchars($currency) ?> <?= number_format($item['price'], 0) ?>
                                </div>
                                <div style="font-size: 11px; color: #666; font-weight: 500;"><?= htmlspecialchars($item['variants']) ?></div>
                                <div style="display: flex; align-items: center; gap: 8px; margin-top: 10px;">
                                    <button type="button" onclick="updateCartQty(<?= $index ?>, -1)" style="width: 28px; height: 28px; border: 1px solid #ddd; border-radius: 50%; background: #fff; color: #222; font-size: 16px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                                        -
                                    </button>
                                    <div style="min-width: 34px; text-align: center; font-size: 13px; color: #444; font-weight: 700;">
                                        <?= (int) $item['qty'] ?>
                                    </div>
                                    <button type="button" onclick="updateCartQty(<?= $index ?>, 1)" style="width: 28px; height: 28px; border: none; border-radius: 50%; background: #000; color: #fff; font-size: 16px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                                        +
                                    </button>
                                </div>
                            </div>

                            <button onclick="removeFromCart(<?= $index ?>)" class="btn-remove-item" style="width: 30px; height: 30px; border-radius: 50%; border: 1px solid #FF3B30; background: none; color: #FF3B30; display: flex; align-items: center; justify-content: center; cursor: pointer; flex-shrink: 0;">
                                <i class="fas fa-times" style="font-size: 14px;"></i>
                            </button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <?php if (!empty($cart)): ?>
                <div id="cartFooter" style="padding: 20px; border-top: 1px solid #f9f9f9;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <span style="font-size: 16px; font-weight: 700; color: #888;">Subtotal</span>
                        <span style="font-size: 20px; font-weight: 800; color: #444;" id="cartSubTotalDisplay">
                            <?= htmlspecialchars($currency) ?> <?= number_format($subtotal ?? 0, 0) ?>
                        </span>
                    </div>
                    <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:12px;">
                        <span style="font-size: 14px; font-weight: 700; color: #888;">Shipping Fee</span>
                        <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap; justify-content:flex-end;">
                            <button type="button" id="selectDistrictButton" onclick="openDistrictSelector()" style="border:1px solid #d97f2f; background:#ffefe1; color:#9a4d10; border-radius:999px; padding:6px 10px; font-size:11px; font-weight:700; cursor:pointer;">
                                Select District
                            </button>
                            <span style="font-size: 15px; font-weight: 700; color: #444;" id="cartShippingDisplay">Select district</span>
                        </div>
                    </div>
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; padding-top:12px; border-top:1px dashed #e3e3e3;">
                        <span style="font-size: 16px; font-weight: 800; color: #444;">Order Total</span>
                        <span style="font-size: 22px; font-weight: 800; color: #111;" id="cartGrandTotalDisplay">
                            <?= htmlspecialchars($currency) ?> <?= number_format($subtotal ?? 0, 0) ?>
                        </span>
                    </div>
                    <div style="font-size:12px; color:#777; margin-bottom:20px;">
                        Delivery fee updates when you choose your district in the order form.
                    </div>

                    <div class="payment-sheet-options">
                        <?php
                        $codEnabled = !empty($settings['cod_enabled']);
                        $shopWhatsappTarget = preg_replace('/[^0-9]/', '', (string) ($settings['shop_whatsapp'] ?? ''));
                        if ($shopWhatsappTarget === '') {
                            $shopWhatsappTarget = preg_replace('/[^0-9]/', '', (string) ($settings['social_whatsapp'] ?? ''));
                        }
                        $whatsappEnabled = !empty($settings['whatsapp_ordering_enabled']) && $shopWhatsappTarget !== '';
                        ?>
                        <?php if ($whatsappEnabled): ?>
                            <button type="button" onclick="openOrderModal('whatsapp')" class="payment-method-card method-whatsapp">
                                <span class="payment-method-icon"><i class="fab fa-whatsapp"></i></span>
                                <span class="payment-method-copy">
                                    <strong>WhatsApp Order</strong>
                                    <small>Send your order details directly to the shop on WhatsApp.</small>
                                </span>
                                <span class="payment-method-arrow"><i class="fas fa-chevron-right"></i></span>
                            </button>
                        <?php endif; ?>
                        <?php if ($codEnabled): ?>
                            <button type="button" onclick="openOrderModal('cod')" class="payment-method-card method-cod">
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
                            <button type="button" onclick="openOrderModal('payhere')" class="payment-method-card method-payhere">
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
                            <button type="button" onclick="openOrderModal('koko')" class="payment-method-card method-koko">
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
                            <button type="button" onclick="openOrderModal('bank_transfer')" class="payment-method-card method-bank">
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
            <?php endif; ?>
        </div>

    </main>
</div>

<div id="orderModal" class="modal-overlay" style="display: none;">
    <div class="modal-content" style="max-width: 500px; width: 90%; max-height: 90vh; overflow-y: auto; padding: 25px; border-radius: 15px;">
        <h3 style="margin-top: 0; font-size: 20px; font-weight: 800; text-align: center; margin-bottom: 20px;">Complete Your Order</h3>

        <form onsubmit="event.preventDefault(); submitOrder();">
            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 5px;">Full Name <span style="color:red">*</span></label>
                <input type="text" id="ordName" class="form-control" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px;">
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 5px;">Email Address <span style="color:red">*</span></label>
                <input type="email" id="ordEmail" class="form-control" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px;">
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 5px;">Address <span style="color:red">*</span></label>
                <textarea id="ordAddress" class="form-control" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; height: 60px;"></textarea>
            </div>

            <div style="display: flex; gap: 10px;">
                <div class="form-group" style="margin-bottom: 15px; flex: 1;">
                    <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 5px;">City <span style="color:red">*</span></label>
                    <input type="text" id="ordCity" class="form-control" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px;">
                </div>
                <div class="form-group" style="margin-bottom: 15px; flex: 1;">
                    <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 5px;">District <span style="color:red">*</span></label>
                    <select id="ordDistrict" class="form-control" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; background:#fff;">
                        <option value="">Select district</option>
                        <?php foreach (($deliveryDistricts ?? []) as $districtName): ?>
                            <option value="<?= htmlspecialchars($districtName) ?>"><?= htmlspecialchars($districtName) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 5px;">Phone Number 01 <span style="color:red">*</span></label>
                <input type="tel" id="ordPhone1" class="form-control" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px;">
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 5px;">Phone Number 02</label>
                <input type="tel" id="ordPhone2" class="form-control" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px;">
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 5px;">Special Note</label>
                <textarea id="ordNote" class="form-control" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; height: 60px;"></textarea>
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
                    <span id="modalSubTotalDisplay" style="font-size:13px; color:#222; font-weight:700;"><?= htmlspecialchars($currency) ?> <?= number_format($subtotal ?? 0, 0) ?></span>
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
                    <span id="modalGrandTotalDisplay" style="font-size:16px; color:#111; font-weight:800;"><?= htmlspecialchars($currency) ?> <?= number_format($subtotal ?? 0, 0) ?></span>
                </div>
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="button" onclick="closeOrderModal()" style="flex: 1; padding: 12px; border: 1px solid #ddd; background: #f5f5f5; border-radius: 8px; font-weight: 600; cursor: pointer;">Cancel</button>
                <button type="submit" id="orderSubmitButton" style="flex: 2; padding: 12px; border: none; background: #111; color: white; border-radius: 8px; font-weight: 600; cursor: pointer;">Place COD Order</button>
            </div>
        </form>

    </div>
</div>

<div id="districtEstimateModal" class="modal-overlay" style="display: none;">
    <div class="modal-content" style="max-width: 420px; width: 90%; padding: 22px; border-radius: 15px;">
        <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:16px;">
            <div>
                <h3 style="margin:0; font-size:18px; font-weight:800;">Check Shipping Fee</h3>
                <p style="margin:4px 0 0; font-size:12px; color:#777;">Choose a district to estimate delivery.</p>
            </div>
            <button type="button" onclick="closeDistrictSelector()" style="border:none; background:transparent; font-size:22px; line-height:1; cursor:pointer; color:#666;">&times;</button>
        </div>

        <div class="form-group" style="margin-bottom: 16px;">
            <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px;">District</label>
            <select id="estimateDistrictInput" class="form-control" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; background:#fff;">
                <option value="">Select district</option>
                <?php foreach (($deliveryDistricts ?? []) as $districtName): ?>
                    <option value="<?= htmlspecialchars($districtName) ?>"><?= htmlspecialchars($districtName) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="background:#fafafa; border:1px solid #ededed; border-radius:12px; padding:14px; margin-bottom:18px;">
            <div style="display:flex; justify-content:space-between; gap:12px; margin-bottom:8px;">
                <span style="font-size:13px; color:#777; font-weight:600;">Subtotal</span>
                <span id="estimateSubTotalDisplay" style="font-size:13px; color:#222; font-weight:700;"><?= htmlspecialchars($currency) ?> <?= number_format($subtotal ?? 0, 0) ?></span>
            </div>
            <div style="display:flex; justify-content:space-between; gap:12px; margin-bottom:8px;">
                <span style="font-size:13px; color:#777; font-weight:600;">Shipping Fee</span>
                <span id="estimateShippingDisplay" style="font-size:13px; color:#222; font-weight:700;">Select district</span>
            </div>
            <div style="display:flex; justify-content:space-between; gap:12px; padding-top:8px; border-top:1px dashed #e1e1e1;">
                <span style="font-size:14px; color:#111; font-weight:800;">Estimated Total</span>
                <span id="estimateGrandTotalDisplay" style="font-size:16px; color:#111; font-weight:800;"><?= htmlspecialchars($currency) ?> <?= number_format($subtotal ?? 0, 0) ?></span>
            </div>
        </div>

        <div style="display:flex; justify-content:flex-end;">
            <button type="button" onclick="closeDistrictSelector()" style="border:none; background:#111; color:#fff; padding:10px 16px; border-radius:999px; font-weight:700; cursor:pointer;">
                Done
            </button>
        </div>
    </div>
</div>

<script>
    const cartData = <?= json_encode($cart ?? []) ?>;
    const currencySymbol = <?= json_encode($currency) ?>;
    const deliveryDistricts = <?= json_encode(array_values($deliveryDistricts ?? [])) ?>;
    const deliveryRates = <?= json_encode($deliveryRatesMap ?? new stdClass()) ?>;
    const deliverySettings = {
        applyAll: <?= !empty($settings['delivery_apply_all_districts']) ? 'true' : 'false' ?>,
        firstKg: <?= json_encode((float) ($settings['delivery_all_first_kg'] ?? 0)) ?>,
        additionalKg: <?= json_encode((float) ($settings['delivery_all_additional_kg'] ?? 0)) ?>
    };
    const kokoHandlingFeePercentage = <?= json_encode((float) ($settings['koko_handling_fee_percentage'] ?? 0)) ?>;
    let orderMode = 'cod';
    const shopWhatsappNumber = '<?= htmlspecialchars($shopWhatsappTarget ?? '', ENT_QUOTES) ?>';

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

    function calculateShippingQuote(items, districtValue) {
        const district = normalizeDistrict(districtValue);
        let subtotal = 0;
        let chargeableWeight = 0;

        items.forEach(function (item) {
            const qty = Math.max(1, parseInt(item.qty || 1, 10));
            const price = Number(item.price || 0);
            subtotal += (price * qty);

            if (!item.is_free_shipping) {
                const weight = Math.max(0, parseInt(item.weight_grams || 0, 10));
                chargeableWeight += (weight * qty);
            }
        });

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
            subtotal: subtotal,
            shipping: shipping,
            total: subtotal + shipping,
            chargeableWeight: chargeableWeight,
            hasRate: hasRate,
            district: district
        };
    }

    function updateShippingDisplays() {
        const cartSubtotalEl = document.getElementById('cartSubTotalDisplay');
        const modalSubtotalEl = document.getElementById('modalSubTotalDisplay');
        const cartShippingEl = document.getElementById('cartShippingDisplay');
        const modalShippingEl = document.getElementById('modalShippingDisplay');
        const cartGrandTotalEl = document.getElementById('cartGrandTotalDisplay');
        const modalGrandTotalEl = document.getElementById('modalGrandTotalDisplay');
        const modalHandlingFeeRowEl = document.getElementById('modalHandlingFeeRow');
        const modalHandlingFeeEl = document.getElementById('modalHandlingFeeDisplay');
        const estimateSubTotalEl = document.getElementById('estimateSubTotalDisplay');
        const estimateShippingEl = document.getElementById('estimateShippingDisplay');
        const estimateGrandTotalEl = document.getElementById('estimateGrandTotalDisplay');

        if (!cartSubtotalEl || !modalSubtotalEl || !cartShippingEl || !modalShippingEl || !cartGrandTotalEl || !modalGrandTotalEl) {
            return calculateShippingQuote(cartData, '');
        }

        const districtInput = document.getElementById('ordDistrict');
        const estimateInput = document.getElementById('estimateDistrictInput');
        const activeDistrict = districtInput && districtInput.value
            ? districtInput.value
            : (estimateInput && estimateInput.value ? estimateInput.value : (localStorage.getItem('shipping_estimate_district') || localStorage.getItem('cus_district') || ''));
        const quote = calculateShippingQuote(cartData, activeDistrict);

        cartSubtotalEl.textContent = formatMoney(quote.subtotal);
        modalSubtotalEl.textContent = formatMoney(quote.subtotal);

        const shippingText = quote.chargeableWeight === 0
            ? 'Free'
            : (quote.hasRate ? formatMoney(quote.shipping) : 'Select district');

        cartShippingEl.textContent = shippingText;
        modalShippingEl.textContent = shippingText;
        cartGrandTotalEl.textContent = formatMoney(quote.hasRate || quote.chargeableWeight === 0 ? quote.total : quote.subtotal);
        const modalBaseTotal = quote.hasRate || quote.chargeableWeight === 0 ? quote.total : quote.subtotal;
        const modalHandlingFee = orderMode === 'koko' ? calculateKokoHandlingFee(modalBaseTotal) : 0;
        if (modalHandlingFeeRowEl && modalHandlingFeeEl) {
            modalHandlingFeeRowEl.style.display = modalHandlingFee > 0 ? 'flex' : 'none';
            modalHandlingFeeEl.textContent = formatMoney(modalHandlingFee);
        }
        modalGrandTotalEl.textContent = formatMoney(modalBaseTotal + modalHandlingFee);
        if (estimateSubTotalEl && estimateShippingEl && estimateGrandTotalEl) {
            estimateSubTotalEl.textContent = formatMoney(quote.subtotal);
            estimateShippingEl.textContent = shippingText;
            estimateGrandTotalEl.textContent = formatMoney(quote.hasRate || quote.chargeableWeight === 0 ? quote.total : quote.subtotal);
        }

        const selectDistrictButton = document.getElementById('selectDistrictButton');
        if (selectDistrictButton) {
            selectDistrictButton.style.display = quote.chargeableWeight === 0 ? 'none' : 'inline-flex';
        }

        return quote;
    }

    function removeFromCart(index) {
        fetch('<?= BASE_URL ?>cart/remove', {
            method: 'POST',
            headers: csrfHeaders({ 'Content-Type': 'application/json' }),
            body: JSON.stringify({ index: index })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            }
        });
    }

    function updateCartQty(index, change) {
        const item = cartData[index];
        if (!item) {
            return;
        }

        const nextQty = Math.max(1, (parseInt(item.qty || 1, 10) + change));

        fetch('<?= BASE_URL ?>cart/updateQty', {
            method: 'POST',
            headers: csrfHeaders({ 'Content-Type': 'application/json' }),
            body: JSON.stringify({ index: index, qty: nextQty })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else if (data.message) {
                alert(data.message);
            }
        });
    }

    function clearCart() {
        if (!confirm('Clear all items?')) {
            return;
        }

        fetch('<?= BASE_URL ?>cart/clear', {
            method: 'POST',
            headers: csrfHeaders({ 'Content-Type': 'application/json' })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            }
        });
    }

    function openOrderModal(mode) {
        if (cartData.length === 0) {
            alert('Your cart is empty!');
            return;
        }

        orderMode = mode || 'cod';
        const previewQuote = updateShippingDisplays();
        trackAnalyticsEvent('begin_checkout', {
            currency: window.APP_CURRENCY,
            value: Number((previewQuote && previewQuote.total) || 0),
            items: cartData.map(function (item) {
                return buildAnalyticsItem(item);
            })
        }, 'InitiateCheckout', {
            content_ids: cartData.map(function (item) { return String(item.id || ''); }),
            content_type: 'product',
            value: Number((previewQuote && previewQuote.total) || 0),
            currency: window.APP_CURRENCY
        });

        if (localStorage.getItem('cus_name')) document.getElementById('ordName').value = localStorage.getItem('cus_name');
        if (localStorage.getItem('cus_email')) document.getElementById('ordEmail').value = localStorage.getItem('cus_email');
        if (localStorage.getItem('cus_address')) document.getElementById('ordAddress').value = localStorage.getItem('cus_address');
        if (localStorage.getItem('cus_city')) document.getElementById('ordCity').value = localStorage.getItem('cus_city');
        if (localStorage.getItem('cus_district')) document.getElementById('ordDistrict').value = localStorage.getItem('cus_district');
        if (localStorage.getItem('cus_phone1')) document.getElementById('ordPhone1').value = localStorage.getItem('cus_phone1');
        if (localStorage.getItem('cus_phone2')) document.getElementById('ordPhone2').value = localStorage.getItem('cus_phone2');
        updateShippingDisplays();

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

    function openDistrictSelector() {
        const estimateInput = document.getElementById('estimateDistrictInput');
        const currentDistrict = normalizeDistrict(localStorage.getItem('shipping_estimate_district') || localStorage.getItem('cus_district') || '');
        if (estimateInput) {
            estimateInput.value = currentDistrict;
        }
        document.getElementById('districtEstimateModal').style.display = 'flex';
        updateShippingDisplays();
        if (estimateInput) estimateInput.focus();
    }

    function closeDistrictSelector() {
        document.getElementById('districtEstimateModal').style.display = 'none';
    }

    function saveCustomerDetails(data) {
        localStorage.setItem('cus_name', data.name);
        localStorage.setItem('cus_email', data.email);
        localStorage.setItem('cus_address', data.address);
        localStorage.setItem('cus_city', data.city);
        localStorage.setItem('cus_district', data.district);
        localStorage.setItem('cus_phone1', data.phone1);
        localStorage.setItem('cus_phone2', data.phone2);
    }

    function submitOrder() {
        const data = {
            name: document.getElementById('ordName').value.trim(),
            email: document.getElementById('ordEmail').value.trim(),
            address: document.getElementById('ordAddress').value.trim(),
            city: document.getElementById('ordCity').value.trim(),
            district: normalizeDistrict(document.getElementById('ordDistrict').value),
            phone1: document.getElementById('ordPhone1').value.trim(),
            phone2: document.getElementById('ordPhone2').value.trim(),
            note: document.getElementById('ordNote').value.trim()
        };

        if (!data.name || !data.email || !data.address || !data.city || !data.phone1 || !data.district) {
            alert('Please fill in required fields.');
            return;
        }

        saveCustomerDetails(data);

        if (orderMode === 'payhere') {
            submitOrderToPayHere(data);
            return;
        }

        if (orderMode === 'koko') {
            submitOrderToKoko(data);
            return;
        }

        if (orderMode === 'bank_transfer') {
            submitOrderToBankTransfer(data);
            return;
        }

        if (orderMode === 'whatsapp') {
            submitOrderToWhatsApp(data);
            return;
        }

        submitOrderToCod(data);
    }

    function submitOrderToPayHere(data) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= BASE_URL ?>order/startPayhere';
        form.style.display = 'none';

        const fields = {
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
            payment_type: 'payhere'
        });
        document.body.appendChild(form);
        if (typeof showGlobalLoader === 'function') showGlobalLoader();
        form.submit();
    }

    function submitOrderToCod(data) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= BASE_URL ?>order/startCod';
        form.style.display = 'none';

        const fields = {
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
            payment_type: 'cod'
        });
        document.body.appendChild(form);
        if (typeof showGlobalLoader === 'function') showGlobalLoader();
        form.submit();
    }

    function submitOrderToKoko(data) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= BASE_URL ?>order/startKoko';
        form.style.display = 'none';

        const fields = {
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
            payment_type: 'koko'
        });
        document.body.appendChild(form);
        if (typeof showGlobalLoader === 'function') showGlobalLoader();
        form.submit();
    }

    function submitOrderToBankTransfer(data) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= BASE_URL ?>order/startBankTransfer';
        form.style.display = 'none';

        const fields = {
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
            payment_type: 'bank_transfer'
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

        const lines = [
            '*New WhatsApp Order Request*',
            ''
        ];

        cartData.forEach(function(item, index) {
            lines.push((index + 1) + '. ' + item.title + ' x ' + item.qty);
            if (item.variants) {
                lines.push('   Variants: ' + item.variants);
            }
            lines.push('   Price: LKR ' + Number(item.price || 0).toLocaleString());
        });

        lines.push(
            '',
            '*Customer:* ' + data.name,
            '*Email:* ' + data.email,
            '*Phone:* ' + data.phone1
        );

        const quote = calculateShippingQuote(cartData, data.district);

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

        lines.push(
            '',
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
            districtInput.addEventListener('input', updateShippingDisplays);
            districtInput.addEventListener('change', function () {
                const normalized = normalizeDistrict(districtInput.value);
                if (normalized) {
                    districtInput.value = normalized;
                }
                updateShippingDisplays();
            });
        }
        const estimateInput = document.getElementById('estimateDistrictInput');
        if (estimateInput) {
            estimateInput.addEventListener('input', function () {
                localStorage.setItem('shipping_estimate_district', estimateInput.value.trim());
                updateShippingDisplays();
            });
            estimateInput.addEventListener('change', function () {
                const normalized = normalizeDistrict(estimateInput.value);
                if (normalized) {
                    estimateInput.value = normalized;
                    localStorage.setItem('shipping_estimate_district', normalized);
                }
                updateShippingDisplays();
            });
        }

        updateShippingDisplays();
    });
</script>

<?php require_once 'views/layouts/customer_footer.php'; ?>
