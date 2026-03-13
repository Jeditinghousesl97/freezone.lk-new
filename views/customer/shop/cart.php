<?php
// Hide Default Mobile Header (We implement custom "YOUR CART" header)
$hide_mobile_welcome = true;
require_once 'views/layouts/customer_header.php';
?>

<div class="home-layout">
    <!-- Sidebar (Desktop) -->
    <?php include 'views/customer/partials/sidebar.php'; ?>

    <main class="main-content" style="padding-bottom: 20px; align-self: flex-start; margin-top: 0;">

        <?php if (!empty($_SESSION['order_error'])): ?>
            <div style="margin: 0 20px 20px; padding: 14px 16px; border-radius: 14px; background: #fff1f1; color: #c62828; font-weight: 600;">
                <?= htmlspecialchars($_SESSION['order_error']) ?>
            </div>
            <?php unset($_SESSION['order_error']); ?>
        <?php endif; ?>

        <!-- --- MOBILE HEADER (Custom - Structurally Matched to Categories) --- -->
        <div class="mobile-header-custom d-lg-none" style="padding: 20px 20px 0 20px; margin-bottom: 20px;">
            <!-- Breadcrumb (Row 1) -->
            <div style="font-size: 11px; color: #888; margin-bottom: 15px;">Home > Cart</div>
            <!-- Title Row (Row 2 - Flex) -->
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <!-- Left: Back Btn + Title -->
                <div style="display: flex; align-items: center; gap: 15px;">
                    <!-- Back Button -->
                    <a href="javascript:history.back()" style="
                        width: 35px; height: 35px; background: #000; border-radius: 50%; 
                        display: flex; align-items: center; justify-content: center; 
                        text-decoration: none; color: white;">
                        <i class="fas fa-chevron-left" style="font-size: 14px;"></i>
                    </a>
                    <!-- Title Block -->
                    <div>
                        <h1 style="font-size: 20px; font-weight: 800; margin: 0; line-height: 1;">YOUR CART</h1>
                        <p style="font-size: 12px; color: #888; margin: 0;">Your Selections are amazing..!</p>
                    </div>
                </div>
                <!-- Right: Clear All Action (Replaces Search/Avatar) -->
                <button onclick="clearCart()" style="
                    background: none; border: none; color: #FF3B30; 
                    font-weight: 600; font-size: 13px; cursor: pointer;">
                    Clear All
                </button>
            </div>
        </div>

<!-- --- DESKTOP HEADER (My Cart) --- -->
<div class="d-none d-lg-flex"
    style="align-items: center; justify-content: space-between; margin-bottom: 30px; padding-bottom: 10px;">
    <div style="display: flex; align-items: baseline; gap: 15px;">
        <h1 style="font-size: 28px; font-weight: 800; color: #000; margin: 0;">My Cart</h1>
        <a href="javascript:void(0)" onclick="clearCart()" 
           style="font-size: 13px; text-decoration: underline; color: #FF3B30; font-weight: 600;">
           Clear All
        </a>
    </div>
</div>

<!-- --- CART CONTENT WRAPPER (Grid on Desktop) --- -->
<div class="cart-desktop-grid">

    <!-- --- CART ITEMS LIST --- -->
    <div id="cartItemsContainer" style="padding: 10px 20px; min-height: 300px;">
        <?php if (empty($cart)): ?>
            <p style="text-align: center; color: #999; margin-top: 50px;">Your cart is empty.</p>
        <?php else: ?>
            <?php
            $total = 0;
            foreach ($cart as $index => $item):
                $itemTotal = $item['price'] * $item['qty'];
                $total += $itemTotal;
                ?>
                <div class="cart-item" style="
                    display: flex; align-items: center; gap: 15px; 
                    background: #fff; padding: 15px; border-radius: 20px; 
                    position: relative;
                    margin-bottom: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.03);">
                    <!-- Image -->
                    <img src="<?= htmlspecialchars($item['img']) ?>" style="
                        width: 70px; height: 70px; border-radius: 12px; object-fit: cover; background: #f0f0f0;">
                    <!-- Info -->
                    <div style="flex: 1;">
                        <h4 style="font-size: 14px; font-weight: 700; margin: 0 0 5px 0;">
                            <?= htmlspecialchars($item['title']) ?>
                        </h4>
                        <div style="font-size: 13px; font-weight: 700; color: #E4405F; margin-bottom: 3px;">
                            LKR <?= number_format($item['price'], 0) ?>
                        </div>
                        <div style="font-size: 11px; color: #666; font-weight: 500;">
                            <?= htmlspecialchars($item['variants']) ?>
                        </div>
                        <div style="font-size: 11px; color: #444; font-weight: 600; margin-top: 2px;">
                            Qty: <?= $item['qty'] ?>
                        </div>
                    </div>
                    
                    <button onclick="removeFromCart(<?= $index ?>)"
                        class="btn-remove-item"
                        style="
                        width: 30px; height: 30px; border-radius: 50%; border: 1px solid #FF3B30; 
                        background: none; color: #FF3B30; display: flex; align-items: center; justify-content: center; cursor: pointer; flex-shrink: 0;">
                        <i class="fas fa-times" style="font-size: 14px;"></i>
                    </button>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- --- CART TOTAL & ORDER BUTTON --- -->
    <?php if (!empty($cart)): ?>
        <div id="cartFooter" style="padding: 20px; border-top: 1px solid #f9f9f9;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <span style="font-size: 16px; font-weight: 700; color: #888;">Cart Total</span>
                <span style="font-size: 20px; font-weight: 800; color: #444;" id="cartTotalDisplay">
                    LKR <?= number_format($total ?? 0, 0) ?>
                </span>
            </div>

            <button onclick="openOrderModal()" style="
                width: 100%; 
                background: #25d366; /* WhatsApp Green */
                color: white; 
                border: none; 
                padding: 15px; 
                border-radius: 30px; 
                font-size: 15px; 
                font-weight: 600; 
                display: flex; 
                align-items: center; 
                justify-content: center; 
                gap: 10px;
                cursor: pointer;
                box-shadow: 0 4px 10px rgba(37, 211, 102, 0.3);">
                <i class="fab fa-whatsapp" style="font-size: 18px;"></i>
                Order Now via Whatsapp
            </button>

            <?php if (!empty($settings['payhere_enabled']) && !empty($settings['payhere_merchant_id']) && !empty($settings['payhere_merchant_secret'])): ?>
                <button onclick="openOrderModal('payhere')" style="
                    width: 100%;
                    margin-top: 12px;
                    background: #4a148c;
                    color: white;
                    border: none;
                    padding: 15px;
                    border-radius: 30px;
                    font-size: 15px;
                    font-weight: 600;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    gap: 10px;
                    cursor: pointer;
                    box-shadow: 0 4px 10px rgba(74, 20, 140, 0.28);">
                    <i class="fas fa-credit-card" style="font-size: 18px;"></i>
                    Pay Online with PayHere
                </button>
            <?php endif; ?>
        </div>
    <?php endif; ?>

</div> <!-- End .cart-desktop-grid -->

</main>
</div>

<!-- --- ORDER MODAL (Same as Product Page) --- -->
<div id="orderModal" class="modal-overlay" style="display: none;">
    <div class="modal-content"
        style="max-width: 500px; width: 90%; max-height: 90vh; overflow-y: auto; padding: 25px; border-radius: 15px;">
        <h3 style="margin-top: 0; font-size: 20px; font-weight: 800; text-align: center; margin-bottom: 20px;">Complete
            Your Order</h3>

        <form onsubmit="event.preventDefault(); submitOrderToWhatsApp();">
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
                        style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 5px;">District</label>
                    <input type="text" id="ordDistrict" class="form-control"
                        style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px;">
                </div>
            </div>
            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 5px;">Postal
                    Code</label>
                <input type="text" id="ordPostal" class="form-control"
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px;">
            </div>
            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 5px;">Country <span
                        style="color:red">*</span></label>
                <input type="text" id="ordCountry" class="form-control" value="Sri Lanka" required
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px;">
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
            <div style="display: flex; gap: 10px;">
                <button type="button" onclick="closeOrderModal()"
                    style="flex: 1; padding: 12px; border: 1px solid #ddd; background: #f5f5f5; border-radius: 8px; font-weight: 600; cursor: pointer;">Cancel</button>
                <button type="submit"
                    style="flex: 2; padding: 12px; border: none; background: #25d366; color: white; border-radius: 8px; font-weight: 600; cursor: pointer;">Send
                    via WhatsApp</button>
            </div>
            <?php if (!empty($settings['payhere_enabled']) && !empty($settings['payhere_merchant_id']) && !empty($settings['payhere_merchant_secret'])): ?>
                <button type="button" onclick="submitOrderToPayHere()" style="margin-top: 12px; width: 100%; padding: 12px; border: none; background: #4a148c; color: white; border-radius: 8px; font-weight: 600; cursor: pointer;">
                    Pay Online with PayHere
                </button>
            <?php endif; ?>
        </form>
    </div>
</div>

<script>
    // --- Safe Data Handoff  ---
    const cartData = <?= json_encode($cart ?? []) ?>;

    // --- ACTIONS (AJAX + Reload) ---
    function removeFromCart(index) {
        fetch('<?= BASE_URL ?>cart/remove', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ index: index })
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                }
            });
    }

    function clearCart() {
        if (confirm('Clear all items?')) {
            fetch('<?= BASE_URL ?>cart/clear', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' }
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    }
                });
        }
    }

        function openOrderModal() {
        if (cartData.length === 0) {
            alert("Your cart is empty!");
            return;
        }
        
        // Load Saved Data
        if(localStorage.getItem('cus_name')) document.getElementById('ordName').value = localStorage.getItem('cus_name');
        if(localStorage.getItem('cus_email')) document.getElementById('ordEmail').value = localStorage.getItem('cus_email');
        if(localStorage.getItem('cus_address')) document.getElementById('ordAddress').value = localStorage.getItem('cus_address');
        if(localStorage.getItem('cus_city')) document.getElementById('ordCity').value = localStorage.getItem('cus_city');
        if(localStorage.getItem('cus_district')) document.getElementById('ordDistrict').value = localStorage.getItem('cus_district');
        if(localStorage.getItem('cus_postal')) document.getElementById('ordPostal').value = localStorage.getItem('cus_postal');
        if(localStorage.getItem('cus_country')) document.getElementById('ordCountry').value = localStorage.getItem('cus_country');
        if(localStorage.getItem('cus_phone1')) document.getElementById('ordPhone1').value = localStorage.getItem('cus_phone1');
        if(localStorage.getItem('cus_phone2')) document.getElementById('ordPhone2').value = localStorage.getItem('cus_phone2');

        document.getElementById('orderModal').style.display = 'flex';
    }

    function closeOrderModal() {
        document.getElementById('orderModal').style.display = 'none';
    }

    function submitOrderToWhatsApp() {
        const name = document.getElementById('ordName').value.trim();
        const email = document.getElementById('ordEmail').value.trim();
        const address = document.getElementById('ordAddress').value.trim();
        const city = document.getElementById('ordCity').value.trim();
        const district = document.getElementById('ordDistrict').value.trim();
        const postal = document.getElementById('ordPostal').value.trim();
        const country = document.getElementById('ordCountry').value.trim();
        const phone1 = document.getElementById('ordPhone1').value.trim();
        const phone2 = document.getElementById('ordPhone2').value.trim();
        const note = document.getElementById('ordNote').value.trim();

        if (!name || !email || !address || !city || !country || !phone1) {
            alert("Please fill in required fields.");
            return;
        }

        let total = 0;
        let msg = "*NEW CART ORDER* 🛒\n\n";

        // Cart Items from PHP Session Data
        msg += "*Items:*\n";
        cartData.forEach((item, i) => {
            msg += `${i + 1}. ${item.title}\n`;
            msg += `   ${item.variants}\n`;
            msg += `   Price: LKR ${item.price}\n`;
            msg += `   Qty: ${item.qty || 1}\n\n`;
            total += (item.price * (item.qty || 1));
        });

        msg += `*Total Amount: LKR ${total.toLocaleString()}*\n`;
        msg += `----------------------------\n`;

        // Customer Details
        msg += "*Customer Details:*\n";
        msg += "Name: " + name + "\n";
        msg += "Address: " + address + "\n";
        msg += "City: " + city + "\n";
        msg += "District: " + district + "\n";
        msg += "Postal: " + postal + "\n";
        msg += "Phone 01: " + phone1 + "\n";
        msg += "Phone 02: " + phone2 + "\n";
        if (note) msg += "Note: " + note + "\n";

        // --- Save Data for Next Time ---
        localStorage.setItem('cus_name', name);
        localStorage.setItem('cus_email', email);
        localStorage.setItem('cus_address', address);
        localStorage.setItem('cus_city', city);
        localStorage.setItem('cus_district', district);
        localStorage.setItem('cus_postal', postal);
        localStorage.setItem('cus_country', country);
        localStorage.setItem('cus_phone1', phone1);
        localStorage.setItem('cus_phone2', phone2);

        // 4. Redirect
        const shopPhone = "<?= str_replace(['+', ' '], '', $settings['shop_whatsapp'] ?? '') ?>";
        const url = "https://wa.me/" + shopPhone + "?text=" + encodeURIComponent(msg);
        
        // [Phase 3.3] Show Loader & Delay
        if (typeof showGlobalLoader === 'function') showGlobalLoader();

        setTimeout(() => {
            window.open(url, '_blank');
            if (typeof hideGlobalLoader === 'function') hideGlobalLoader();
            closeOrderModal();
        }, 1000); // 1s delay to prevent double-clicks
    }

    function submitOrderToPayHere() {
        const name = document.getElementById('ordName').value.trim();
        const email = document.getElementById('ordEmail').value.trim();
        const address = document.getElementById('ordAddress').value.trim();
        const city = document.getElementById('ordCity').value.trim();
        const district = document.getElementById('ordDistrict').value.trim();
        const postal = document.getElementById('ordPostal').value.trim();
        const country = document.getElementById('ordCountry').value.trim();
        const phone1 = document.getElementById('ordPhone1').value.trim();
        const phone2 = document.getElementById('ordPhone2').value.trim();
        const note = document.getElementById('ordNote').value.trim();

        if (!name || !email || !address || !city || !country || !phone1) {
            alert("Please fill in required fields for PayHere.");
            return;
        }

        localStorage.setItem('cus_name', name);
        localStorage.setItem('cus_email', email);
        localStorage.setItem('cus_address', address);
        localStorage.setItem('cus_city', city);
        localStorage.setItem('cus_district', district);
        localStorage.setItem('cus_postal', postal);
        localStorage.setItem('cus_country', country);
        localStorage.setItem('cus_phone1', phone1);
        localStorage.setItem('cus_phone2', phone2);

        if (typeof showGlobalLoader === 'function') showGlobalLoader();

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= BASE_URL ?>order/startPayhere';

        const fields = {
            customer_name: name,
            email: email,
            address: address,
            city: city,
            district: district,
            postal_code: postal,
            country: country,
            phone: phone1,
            phone_alt: phone2,
            note: note
        };

        Object.keys(fields).forEach((key) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = fields[key];
            form.appendChild(input);
        });

        document.body.appendChild(form);
        form.submit();
    }


</script>

<?php require_once 'views/layouts/customer_footer.php'; ?>
