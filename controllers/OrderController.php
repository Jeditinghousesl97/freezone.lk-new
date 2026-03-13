<?php
require_once 'models/Order.php';
require_once 'models/Setting.php';
require_once 'models/Product.php';
require_once 'helpers/SeoHelper.php';
require_once 'helpers/OrderEmailService.php';
require_once 'helpers/OrderSmsService.php';

class OrderController extends BaseController
{
    private $orderModel;
    private $settingModel;
    private $productModel;
    private $orderEmailService;
    private $orderSmsService;

    public function __construct()
    {
        $this->orderModel = new Order();
        $this->settingModel = new Setting();
        $this->productModel = new Product();
        $this->orderEmailService = new OrderEmailService();
        $this->orderSmsService = new OrderSmsService();
    }

    private function smsQueueToken()
    {
        return hash('sha256', DB_NAME . '|' . DB_USER . '|' . DB_PASS . '|' . ROOT_PATH);
    }

    private function dispatchSmsQueueAsync()
    {
        register_shutdown_function(function () {
            if (function_exists('session_write_close')) {
                @session_write_close();
            }

            ignore_user_abort(true);

            if (function_exists('fastcgi_finish_request')) {
                @fastcgi_finish_request();
            } else {
                @ob_end_flush();
                @flush();
            }

            $this->orderSmsService->processQueue(8);
        });
    }

    private function notifyCustomerOrderEvent(array $order, $eventKey)
    {
        $this->orderEmailService->sendForEvent($order, $eventKey);
        $this->orderSmsService->queueForEvent($order, $eventKey);
        $this->dispatchSmsQueueAsync();
    }

    public function processSmsQueue()
    {
        $token = (string) ($_GET['token'] ?? '');
        if (!hash_equals($this->smsQueueToken(), $token)) {
            http_response_code(403);
            echo 'FORBIDDEN';
            exit;
        }

        if (function_exists('session_write_close')) {
            @session_write_close();
        }

        ignore_user_abort(true);
        $this->orderSmsService->processQueue(8);
        echo 'OK';
        exit;
    }

    private function logPayhereEvent($event, array $context = [])
    {
        $logDir = ROOT_PATH . 'storage/logs/';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0775, true);
        }

        $entry = [
            'time' => date('c'),
            'event' => $event,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
            'context' => $context
        ];

        file_put_contents(
            $logDir . 'payhere.log',
            json_encode($entry, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL,
            FILE_APPEND | LOCK_EX
        );
    }

    private function splitName($fullName)
    {
        $fullName = trim($fullName);
        $parts = preg_split('/\s+/', $fullName);
        $firstName = $parts[0] ?? 'Customer';
        array_shift($parts);
        $lastName = !empty($parts) ? implode(' ', $parts) : '-';

        return [$firstName, $lastName];
    }

    private function buildCustomerFromRequest()
    {
        $customerName = trim((string) ($_POST['customer_name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $phone = trim((string) ($_POST['phone'] ?? ''));
        $address = trim((string) ($_POST['address'] ?? ''));
        $city = trim((string) ($_POST['city'] ?? ''));

        if ($customerName === '' || $email === '' || $phone === '' || $address === '' || $city === '') {
            return null;
        }

        [$firstName, $lastName] = $this->splitName($customerName);

        return [
            'customer_name' => $customerName,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'phone' => $phone,
            'phone_alt' => trim((string) ($_POST['phone_alt'] ?? '')),
            'address' => $address,
            'city' => $city,
            'district' => trim((string) ($_POST['district'] ?? '')),
            'postal_code' => '',
            'country' => 'Sri Lanka',
            'note' => trim((string) ($_POST['note'] ?? ''))
        ];
    }

    private function requireAdminSession()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('auth/login');
        }
    }

    public function manage()
    {
        $this->requireAdminSession();

        $settings = $this->settingModel->getAllPairs();
        $filters = [
            'search' => trim($_GET['search'] ?? ''),
            'payment_status' => trim($_GET['payment_status'] ?? ''),
            'payment_method' => trim($_GET['payment_method'] ?? ''),
            'order_status' => trim($_GET['order_status'] ?? ''),
            'date_from' => trim($_GET['date_from'] ?? ''),
            'date_to' => trim($_GET['date_to'] ?? ''),
            'only_new' => !empty($_GET['only_new']) ? '1' : ''
        ];
        $orders = $this->orderModel->getFiltered($filters, 150);
        $summary = $this->orderModel->getSummaryCounts($filters);

        $this->view('admin/orders/index', [
            'title' => 'Orders',
            'settings' => $settings,
            'orders' => $orders,
            'filters' => $filters,
            'summary' => $summary
        ]);
    }

    public function reports()
    {
        $this->requireAdminSession();

        $settings = $this->settingModel->getAllPairs();
        $filters = [
            'search' => trim($_GET['search'] ?? ''),
            'payment_status' => trim($_GET['payment_status'] ?? ''),
            'payment_method' => trim($_GET['payment_method'] ?? ''),
            'order_status' => trim($_GET['order_status'] ?? ''),
            'date_from' => trim($_GET['date_from'] ?? ''),
            'date_to' => trim($_GET['date_to'] ?? ''),
            'only_new' => ''
        ];

        $summary = $this->orderModel->getSummaryCounts($filters);
        $finance = $this->orderModel->getFinanceSummary($filters);
        $reportRows = $this->orderModel->getReportRows($filters, 30);

        $this->view('admin/orders/reports', [
            'title' => 'Accounting & Reporting',
            'settings' => $settings,
            'filters' => $filters,
            'summary' => $summary,
            'finance' => $finance,
            'reportRows' => $reportRows
        ]);
    }

    public function details($orderNumber = null)
    {
        $this->requireAdminSession();

        $settings = $this->settingModel->getAllPairs();
        $this->orderModel->markSeen($orderNumber);
        $order = $this->orderModel->getByOrderNumberWithItems($orderNumber);

        if (!$order) {
            $this->redirect('order/manage');
        }

        $this->view('admin/orders/view', [
            'title' => 'Order Details',
            'settings' => $settings,
            'order' => $order
        ]);
    }

    public function export()
    {
        $this->requireAdminSession();

        $filters = [
            'search' => trim($_GET['search'] ?? ''),
            'payment_status' => trim($_GET['payment_status'] ?? ''),
            'payment_method' => trim($_GET['payment_method'] ?? ''),
            'order_status' => trim($_GET['order_status'] ?? ''),
            'date_from' => trim($_GET['date_from'] ?? ''),
            'date_to' => trim($_GET['date_to'] ?? ''),
            'only_new' => !empty($_GET['only_new']) ? '1' : ''
        ];

        $orders = $this->orderModel->getFilteredForExport($filters);

        $filename = 'orders_export_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($output, [
            'Order Number',
            'Created At',
            'Customer Name',
            'Email',
            'Phone',
            'Alt Phone',
            'Address',
            'City',
            'District',
            'Order Type',
            'Payment Gateway',
            'Payment Status',
            'Order Status',
            'Amount',
            'Currency',
            'Payment ID',
            'Message',
            'New Order'
        ]);

        foreach ($orders as $order) {
            fputcsv($output, [
                $order['order_number'] ?? '',
                $order['created_at'] ?? '',
                $order['customer_name'] ?? '',
                $order['email'] ?? '',
                $order['phone'] ?? '',
                $order['phone_alt'] ?? '',
                preg_replace('/\s+/', ' ', trim((string) ($order['address'] ?? ''))),
                $order['city'] ?? '',
                $order['district'] ?? '',
                strtoupper((string) ($order['payment_method'] ?? '')),
                strtoupper((string) ($order['payment_gateway'] ?? '')),
                ucfirst(str_replace('_', ' ', (string) ($order['payment_status'] ?? ''))),
                ucfirst(str_replace('_', ' ', (string) ($order['order_status'] ?? ''))),
                number_format((float) ($order['total_amount'] ?? 0), 2, '.', ''),
                $order['currency'] ?? '',
                $order['gateway_payment_id'] ?? '',
                $order['gateway_message'] ?? '',
                empty($order['admin_seen_at']) ? 'Yes' : 'No'
            ]);
        }

        fclose($output);
        exit;
    }

    public function markCompleted($orderNumber = null)
    {
        $this->requireAdminSession();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($orderNumber)) {
            $this->redirect('order/manage');
        }

        $order = $this->orderModel->getByOrderNumber($orderNumber);
        if ($order) {
            $courierService = trim((string) ($_POST['courier_service'] ?? ''));
            $trackingNumber = trim((string) ($_POST['tracking_number'] ?? ''));
            $this->orderModel->updateCompletionDetails($orderNumber, $courierService, $trackingNumber);
            $this->orderModel->updateOrderStatus($orderNumber, 'completed');
            $updatedOrder = $this->orderModel->getByOrderNumberWithItems($orderNumber);
            if ($updatedOrder) {
                $this->notifyCustomerOrderEvent($updatedOrder, 'order_completed');
            }
        }

        $this->redirect('order/details/' . urlencode($orderNumber));
    }

    public function markPaymentReceived($orderNumber = null)
    {
        $this->requireAdminSession();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($orderNumber)) {
            $this->redirect('order/manage');
        }

        $order = $this->orderModel->getByOrderNumber($orderNumber);
        if ($order && ($order['payment_method'] ?? '') === 'cod' && ($order['payment_status'] ?? 'pending') !== 'paid') {
            $this->orderModel->updatePaymentStatus($orderNumber, 'paid', $order['gateway_payment_id'] ?? null, 'COD_RECEIVED', 'Cash on delivery payment received.');
            $this->orderModel->recordTransaction(
                (int) $order['id'],
                'cod',
                'payment_received',
                null,
                'COD_RECEIVED',
                (float) ($order['total_amount'] ?? 0),
                $order['currency'] ?? 'LKR',
                ['marked_by' => 'shop_owner']
            );

            if (($order['order_status'] ?? 'pending') === 'pending') {
                $this->orderModel->updateOrderStatus($orderNumber, 'processing');
            }

            $updatedOrder = $this->orderModel->getByOrderNumberWithItems($orderNumber);
            if ($updatedOrder) {
                $this->notifyCustomerOrderEvent($updatedOrder, 'payment_received');
            }
        }

        $this->redirect('order/details/' . urlencode($orderNumber));
    }

    public function cancel($orderNumber = null)
    {
        $this->requireAdminSession();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($orderNumber)) {
            $this->redirect('order/manage');
        }

        $order = $this->orderModel->getByOrderNumber($orderNumber);
        if ($order) {
            $this->orderModel->updateOrderStatus($orderNumber, 'cancelled');
            $updatedOrder = $this->orderModel->getByOrderNumberWithItems($orderNumber);
            if ($updatedOrder) {
                $this->notifyCustomerOrderEvent($updatedOrder, 'order_cancelled');
            }
        }

        $this->redirect('order/details/' . urlencode($orderNumber));
    }

    public function delete($orderNumber = null)
    {
        $this->requireAdminSession();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($orderNumber)) {
            $this->redirect('order/manage');
        }

        $order = $this->orderModel->getByOrderNumber($orderNumber);
        if ($order) {
            $this->orderModel->deleteByOrderNumber($orderNumber);
        }

        $this->redirect('order/manage');
    }

    public function startPayhere()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('cart');
        }

        $settings = $this->settingModel->getAllPairs();
        $cart = $_SESSION['cart'] ?? [];

        if (empty($cart)) {
            $_SESSION['order_error'] = 'Your cart is empty.';
            $this->redirect('cart');
        }

        if (empty($settings['payhere_enabled']) || empty($settings['payhere_merchant_id']) || empty($settings['payhere_merchant_secret'])) {
            $_SESSION['order_error'] = 'PayHere is not configured for this shop yet.';
            $this->redirect('cart');
        }

        $customer = $this->buildCustomerFromRequest();
        if (!$customer) {
            $_SESSION['order_error'] = 'Please fill in all required payment fields.';
            $this->redirect('cart');
        }

        $order = $this->orderModel->createFromCart($customer, $cart, $settings);
        if (!$order) {
            $_SESSION['order_error'] = 'Unable to create your order right now.';
            $this->redirect('cart');
        }

        $_SESSION['pending_order_number'] = $order['order_number'];
        $fullOrder = $this->orderModel->getByOrderNumberWithItems($order['order_number']);
        if ($fullOrder) {
            $this->notifyCustomerOrderEvent($fullOrder, 'order_placed');
        }

        $merchantId = trim($settings['payhere_merchant_id']);
        $merchantSecret = trim($settings['payhere_merchant_secret']);
        $currency = trim($order['currency'] ?: 'LKR');
        $amount = number_format((float) $order['total_amount'], 2, '.', '');
        $hash = strtoupper(md5($merchantId . $order['order_number'] . $amount . $currency . strtoupper(md5($merchantSecret))));
        $endpoint = !empty($settings['payhere_sandbox'])
            ? 'https://sandbox.payhere.lk/pay/checkout'
            : 'https://www.payhere.lk/pay/checkout';

        $returnUrl = SeoHelper::absoluteUrl(BASE_URL . 'order/payhereReturn?order=' . urlencode($order['order_number']));
        $cancelUrl = SeoHelper::absoluteUrl(BASE_URL . 'order/payhereCancel?order=' . urlencode($order['order_number']));
        $notifyUrl = SeoHelper::absoluteUrl(BASE_URL . 'order/payhereNotify');

        $payherePayload = [
            'merchant_id' => $merchantId,
            'return_url' => $returnUrl,
            'cancel_url' => $cancelUrl,
            'notify_url' => $notifyUrl,
            'first_name' => $order['first_name'],
            'last_name' => $order['last_name'],
            'email' => $order['email'],
            'phone' => $order['phone'],
            'address' => $order['address'],
            'city' => $order['city'],
            'country' => $order['country'],
            'order_id' => $order['order_number'],
            'items' => SeoHelper::shopName($settings) . ' Order',
            'currency' => $currency,
            'amount' => $amount,
            'hash' => $hash,
            'custom_1' => (string) $order['id'],
            'custom_2' => 'cart_checkout'
        ];

        require 'views/customer/payhere_redirect.php';
    }

    public function myOrders()
    {
        $settings = $this->settingModel->getAllPairs();
        $email = trim((string) ($_GET['email'] ?? ''));
        $phone = trim((string) ($_GET['phone'] ?? ''));
        $orderNumber = trim((string) ($_GET['order_number'] ?? ''));
        $orders = [];
        $lookupAttempted = ($email !== '' || $phone !== '' || $orderNumber !== '');
        $lookupError = '';

        if ($lookupAttempted) {
            if ($email === '' || $phone === '') {
                $lookupError = 'Please enter both your email address and phone number to view your orders.';
            } else {
                $orders = $this->orderModel->findCustomerOrders($email, $phone, $orderNumber);
            }
        }

        $seo = SeoHelper::defaultSeo($settings, [
            'seo_title' => 'My Orders | ' . SeoHelper::shopName($settings),
            'seo_description' => 'Track your orders without creating an account.',
            'seo_canonical' => SeoHelper::absoluteUrl(BASE_URL . 'order/myOrders'),
            'seo_robots' => 'noindex,nofollow'
        ]);

        $this->view('customer/orders_lookup', [
            'title' => 'My Orders',
            'settings' => $settings,
            'orders' => $orders,
            'lookup_email' => $email,
            'lookup_phone' => $phone,
            'lookup_order_number' => $orderNumber,
            'lookup_attempted' => $lookupAttempted,
            'lookup_error' => $lookupError,
            'seo_title' => $seo['seo_title'],
            'seo_description' => $seo['seo_description'],
            'seo_canonical' => $seo['seo_canonical'],
            'seo_image' => $seo['seo_image'],
            'seo_type' => $seo['seo_type'],
            'seo_robots' => $seo['seo_robots'],
            'seo_json_ld' => $seo['seo_json_ld']
        ]);
    }

    public function startCod()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('cart');
        }

        $settings = $this->settingModel->getAllPairs();
        $cart = $_SESSION['cart'] ?? [];

        if (empty($cart)) {
            $_SESSION['order_error'] = 'Your cart is empty.';
            $this->redirect('cart');
        }

        if (isset($settings['cod_enabled']) && empty($settings['cod_enabled'])) {
            $_SESSION['order_error'] = 'Cash on Delivery is not enabled for this shop.';
            $this->redirect('cart');
        }

        $customer = $this->buildCustomerFromRequest();
        if (!$customer) {
            $_SESSION['order_error'] = 'Please fill in all required order fields.';
            $this->redirect('cart');
        }

        $order = $this->orderModel->createFromCart($customer, $cart, $settings, [
            'payment_method' => 'cod',
            'payment_gateway' => 'cod',
            'payment_status' => 'pending',
            'order_status' => 'pending',
            'transaction_type' => 'cod_order_placed',
            'transaction_status_code' => 'PENDING',
            'transaction_payload' => [
                'customer' => $customer,
                'items_count' => count($cart),
                'source' => 'cart_cod'
            ]
        ]);

        if (!$order) {
            $_SESSION['order_error'] = 'Unable to place your order right now.';
            $this->redirect('cart');
        }

        $_SESSION['cod_order_number'] = $order['order_number'];
        $_SESSION['cart'] = [];
        $fullOrder = $this->orderModel->getByOrderNumberWithItems($order['order_number']);
        if ($fullOrder) {
            $this->notifyCustomerOrderEvent($fullOrder, 'order_placed');
        }
        $this->redirect('order/codSuccess?order=' . urlencode($order['order_number']));
    }

    public function startPayhereSingle()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('cart');
        }

        $settings = $this->settingModel->getAllPairs();

        if (empty($settings['payhere_enabled']) || empty($settings['payhere_merchant_id']) || empty($settings['payhere_merchant_secret'])) {
            $_SESSION['order_error'] = 'PayHere is not configured for this shop yet.';
            $this->redirect('cart');
        }

        $productId = (int) ($_POST['product_id'] ?? 0);
        $qty = max(1, (int) ($_POST['quantity'] ?? 1));
        $variantText = trim((string) ($_POST['variants'] ?? ''));

        $product = $this->productModel->getById($productId);
        if (!$product) {
            $_SESSION['order_error'] = 'The selected product could not be found.';
            $this->redirect('cart');
        }

        $customer = $this->buildCustomerFromRequest();
        if (!$customer) {
            $_SESSION['order_error'] = 'Please fill in all required payment fields.';
            $this->redirect('shop/product/' . $productId);
        }

        $unitPrice = (!empty($product['sale_price']) && (float) $product['sale_price'] < (float) $product['price'])
            ? (float) $product['sale_price']
            : (float) $product['price'];

        $imageUrl = '';
        if (!empty($product['main_image'])) {
            $imagePath = 'assets/uploads/' . $product['main_image'];
            if (file_exists(ROOT_PATH . $imagePath)) {
                $imageUrl = BASE_URL . $imagePath;
            }
        }

        $items = [[
            'id' => (int) $product['id'],
            'title' => $product['title'] ?? 'Product',
            'price' => $unitPrice,
            'qty' => $qty,
            'img' => $imageUrl,
            'variants' => $variantText
        ]];

        $order = $this->orderModel->createFromItems($customer, $items, $settings);
        if (!$order) {
            $_SESSION['order_error'] = 'Unable to create your order right now.';
            $this->redirect('shop/product/' . $productId);
        }

        $_SESSION['pending_order_number'] = $order['order_number'];
        $fullOrder = $this->orderModel->getByOrderNumberWithItems($order['order_number']);
        if ($fullOrder) {
            $this->notifyCustomerOrderEvent($fullOrder, 'order_placed');
        }

        $merchantId = trim($settings['payhere_merchant_id']);
        $merchantSecret = trim($settings['payhere_merchant_secret']);
        $currency = trim($order['currency'] ?: 'LKR');
        $amount = number_format((float) $order['total_amount'], 2, '.', '');
        $hash = strtoupper(md5($merchantId . $order['order_number'] . $amount . $currency . strtoupper(md5($merchantSecret))));
        $endpoint = !empty($settings['payhere_sandbox'])
            ? 'https://sandbox.payhere.lk/pay/checkout'
            : 'https://www.payhere.lk/pay/checkout';

        $returnUrl = SeoHelper::absoluteUrl(BASE_URL . 'order/payhereReturn?order=' . urlencode($order['order_number']));
        $cancelUrl = SeoHelper::absoluteUrl(BASE_URL . 'order/payhereCancel?order=' . urlencode($order['order_number']));
        $notifyUrl = SeoHelper::absoluteUrl(BASE_URL . 'order/payhereNotify');

        $payherePayload = [
            'merchant_id' => $merchantId,
            'return_url' => $returnUrl,
            'cancel_url' => $cancelUrl,
            'notify_url' => $notifyUrl,
            'first_name' => $order['first_name'],
            'last_name' => $order['last_name'],
            'email' => $order['email'],
            'phone' => $order['phone'],
            'address' => $order['address'],
            'city' => $order['city'],
            'country' => $order['country'],
            'order_id' => $order['order_number'],
            'items' => $product['title'] ?? (SeoHelper::shopName($settings) . ' Order'),
            'currency' => $currency,
            'amount' => $amount,
            'hash' => $hash,
            'custom_1' => (string) $order['id'],
            'custom_2' => 'single_product_checkout'
        ];

        require 'views/customer/payhere_redirect.php';
    }

    public function startCodSingle()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('cart');
        }

        $settings = $this->settingModel->getAllPairs();
        if (isset($settings['cod_enabled']) && empty($settings['cod_enabled'])) {
            $_SESSION['order_error'] = 'Cash on Delivery is not enabled for this shop.';
            $this->redirect('cart');
        }

        $productId = (int) ($_POST['product_id'] ?? 0);
        $qty = max(1, (int) ($_POST['quantity'] ?? 1));
        $variantText = trim((string) ($_POST['variants'] ?? ''));

        $product = $this->productModel->getById($productId);
        if (!$product) {
            $_SESSION['order_error'] = 'The selected product could not be found.';
            $this->redirect('cart');
        }

        $customer = $this->buildCustomerFromRequest();
        if (!$customer) {
            $_SESSION['order_error'] = 'Please fill in all required order fields.';
            $this->redirect('shop/product/' . $productId);
        }

        $unitPrice = (!empty($product['sale_price']) && (float) $product['sale_price'] < (float) $product['price'])
            ? (float) $product['sale_price']
            : (float) $product['price'];

        $imageUrl = '';
        if (!empty($product['main_image'])) {
            $imagePath = 'assets/uploads/' . $product['main_image'];
            if (file_exists(ROOT_PATH . $imagePath)) {
                $imageUrl = BASE_URL . $imagePath;
            }
        }

        $items = [[
            'id' => (int) $product['id'],
            'title' => $product['title'] ?? 'Product',
            'price' => $unitPrice,
            'qty' => $qty,
            'img' => $imageUrl,
            'variants' => $variantText
        ]];

        $order = $this->orderModel->createFromItems($customer, $items, $settings, [
            'payment_method' => 'cod',
            'payment_gateway' => 'cod',
            'payment_status' => 'pending',
            'order_status' => 'pending',
            'transaction_type' => 'cod_order_placed',
            'transaction_status_code' => 'PENDING',
            'transaction_payload' => [
                'customer' => $customer,
                'items_count' => count($items),
                'source' => 'single_cod'
            ]
        ]);

        if (!$order) {
            $_SESSION['order_error'] = 'Unable to place your order right now.';
            $this->redirect('shop/product/' . $productId);
        }

        $_SESSION['cod_order_number'] = $order['order_number'];
        $fullOrder = $this->orderModel->getByOrderNumberWithItems($order['order_number']);
        if ($fullOrder) {
            $this->notifyCustomerOrderEvent($fullOrder, 'order_placed');
        }
        $this->redirect('order/codSuccess?order=' . urlencode($order['order_number']));
    }

    public function payhereNotify()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'METHOD_NOT_ALLOWED';
            exit;
        }

        $settings = $this->settingModel->getAllPairs();
        $merchantId = trim((string) ($_POST['merchant_id'] ?? ''));
        $orderNumber = trim((string) ($_POST['order_id'] ?? ''));
        $paymentId = trim((string) ($_POST['payment_id'] ?? ''));
        $payhereAmount = trim((string) ($_POST['payhere_amount'] ?? ''));
        $payhereCurrency = trim((string) ($_POST['payhere_currency'] ?? ''));
        $statusCode = trim((string) ($_POST['status_code'] ?? ''));
        $md5sig = strtoupper(trim((string) ($_POST['md5sig'] ?? '')));
        $statusMessage = trim((string) ($_POST['status_message'] ?? ''));
        $this->logPayhereEvent('notify_received', [
            'order_number' => $orderNumber,
            'payment_id' => $paymentId,
            'status_code' => $statusCode
        ]);

        $order = $this->orderModel->getByOrderNumber($orderNumber);
        if (!$order || $merchantId !== trim((string) ($settings['payhere_merchant_id'] ?? ''))) {
            $this->logPayhereEvent('notify_rejected_order_or_merchant', [
                'order_number' => $orderNumber,
                'merchant_id' => $merchantId
            ]);
            http_response_code(400);
            echo 'INVALID';
            exit;
        }

        $merchantSecret = trim((string) ($settings['payhere_merchant_secret'] ?? ''));
        $localMd5Sig = strtoupper(md5(
            $merchantId .
            $orderNumber .
            $payhereAmount .
            $payhereCurrency .
            $statusCode .
            strtoupper(md5($merchantSecret))
        ));

        $this->orderModel->recordTransaction(
            (int) $order['id'],
            'payhere',
            'notify',
            $paymentId,
            $statusCode,
            $payhereAmount,
            $payhereCurrency,
            $_POST
        );

        if ($localMd5Sig !== $md5sig) {
            $this->orderModel->updatePaymentStatus($orderNumber, 'verification_failed', $paymentId, $statusCode, 'Checksum verification failed');
            $this->logPayhereEvent('notify_checksum_failed', [
                'order_number' => $orderNumber,
                'payment_id' => $paymentId,
                'status_code' => $statusCode
            ]);
            http_response_code(400);
            echo 'INVALID';
            exit;
        }

        $expectedAmount = number_format((float) ($order['total_amount'] ?? 0), 2, '.', '');
        $expectedCurrency = trim((string) ($order['currency'] ?? 'LKR'));
        if ($payhereAmount !== $expectedAmount || strtoupper($payhereCurrency) !== strtoupper($expectedCurrency)) {
            $this->orderModel->updatePaymentStatus($orderNumber, 'verification_failed', $paymentId, $statusCode, 'Payment amount or currency mismatch');
            $this->logPayhereEvent('notify_amount_mismatch', [
                'order_number' => $orderNumber,
                'payment_id' => $paymentId,
                'expected_amount' => $expectedAmount,
                'received_amount' => $payhereAmount,
                'expected_currency' => $expectedCurrency,
                'received_currency' => $payhereCurrency
            ]);
            http_response_code(400);
            echo 'INVALID';
            exit;
        }

        $status = 'pending';
        $message = $statusMessage !== '' ? $statusMessage : 'Payment is pending.';

        if ($statusCode === '2') {
            $status = 'paid';
            $message = $statusMessage !== '' ? $statusMessage : 'Payment completed successfully.';
        } elseif ($statusCode === '-1') {
            $status = 'cancelled';
            $message = $statusMessage !== '' ? $statusMessage : 'Payment cancelled by customer.';
        } elseif ($statusCode === '-2') {
            $status = 'failed';
            $message = $statusMessage !== '' ? $statusMessage : 'Payment failed.';
        } elseif ($statusCode === '-3') {
            $status = 'chargedback';
            $message = $statusMessage !== '' ? $statusMessage : 'Payment charged back.';
        }

        $this->orderModel->updatePaymentStatus($orderNumber, $status, $paymentId, $statusCode, $message);
        if ($status === 'paid' && (($order['order_status'] ?? 'pending') === 'pending')) {
            $this->orderModel->updateOrderStatus($orderNumber, 'processing');
        }

        $updatedOrder = $this->orderModel->getByOrderNumberWithItems($orderNumber);
        if ($updatedOrder) {
            if ($status === 'paid') {
                $this->notifyCustomerOrderEvent($updatedOrder, 'payment_completed');
            } elseif ($status === 'cancelled') {
                $this->notifyCustomerOrderEvent($updatedOrder, 'payment_cancelled');
            } elseif ($status === 'failed' || $status === 'verification_failed' || $status === 'chargedback') {
                $this->notifyCustomerOrderEvent($updatedOrder, 'payment_failed');
            }
        }

        $this->logPayhereEvent('notify_processed', [
            'order_number' => $orderNumber,
            'payment_id' => $paymentId,
            'payment_status' => $status,
            'status_code' => $statusCode
        ]);

        echo 'OK';
        exit;
    }

    public function payhereReturn()
    {
        $settings = $this->settingModel->getAllPairs();
        $orderNumber = $_GET['order'] ?? ($_SESSION['pending_order_number'] ?? '');
        $order = $this->orderModel->getByOrderNumber($orderNumber);

        if (!$order) {
            $this->redirect('cart');
        }

        if (!empty($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        unset($_SESSION['pending_order_number']);

        $seo = SeoHelper::defaultSeo($settings, [
            'seo_title' => 'Payment Status | ' . SeoHelper::shopName($settings),
            'seo_description' => 'Check the latest status of your payment order.',
            'seo_canonical' => SeoHelper::absoluteUrl(BASE_URL . 'order/payhereReturn?order=' . urlencode($orderNumber)),
            'seo_robots' => 'noindex,nofollow'
        ]);

        $this->view('customer/payment_status', [
            'title' => 'Payment Status',
            'settings' => $settings,
            'order' => $order,
            'status_type' => 'return',
            'seo_title' => $seo['seo_title'],
            'seo_description' => $seo['seo_description'],
            'seo_canonical' => $seo['seo_canonical'],
            'seo_image' => $seo['seo_image'],
            'seo_type' => $seo['seo_type'],
            'seo_robots' => $seo['seo_robots'],
            'seo_json_ld' => $seo['seo_json_ld']
        ]);
    }

    public function payhereCancel()
    {
        $settings = $this->settingModel->getAllPairs();
        $orderNumber = $_GET['order'] ?? ($_SESSION['pending_order_number'] ?? '');
        $order = $this->orderModel->getByOrderNumber($orderNumber);

        if ($order && ($order['payment_status'] ?? 'pending') === 'pending') {
            $this->orderModel->updatePaymentStatus($orderNumber, 'cancelled', $order['gateway_payment_id'] ?? null, $order['gateway_status_code'] ?? null, 'Payment cancelled before completion.');
            $updatedOrder = $this->orderModel->getByOrderNumberWithItems($orderNumber);
            if ($updatedOrder) {
                $this->notifyCustomerOrderEvent($updatedOrder, 'payment_cancelled');
                $order = $updatedOrder;
            } else {
                $order = $this->orderModel->getByOrderNumber($orderNumber);
            }
        }

        unset($_SESSION['pending_order_number']);

        $seo = SeoHelper::defaultSeo($settings, [
            'seo_title' => 'Payment Cancelled | ' . SeoHelper::shopName($settings),
            'seo_description' => 'The payment was cancelled before completion.',
            'seo_canonical' => SeoHelper::absoluteUrl(BASE_URL . 'order/payhereCancel?order=' . urlencode($orderNumber)),
            'seo_robots' => 'noindex,nofollow'
        ]);

        $this->view('customer/payment_status', [
            'title' => 'Payment Cancelled',
            'settings' => $settings,
            'order' => $order,
            'status_type' => 'cancel',
            'seo_title' => $seo['seo_title'],
            'seo_description' => $seo['seo_description'],
            'seo_canonical' => $seo['seo_canonical'],
            'seo_image' => $seo['seo_image'],
            'seo_type' => $seo['seo_type'],
            'seo_robots' => $seo['seo_robots'],
            'seo_json_ld' => $seo['seo_json_ld']
        ]);
    }

    public function codSuccess()
    {
        $settings = $this->settingModel->getAllPairs();
        $orderNumber = $_GET['order'] ?? ($_SESSION['cod_order_number'] ?? '');
        $order = $this->orderModel->getByOrderNumber($orderNumber);

        if (!$order || ($order['payment_gateway'] ?? '') !== 'cod') {
            $this->redirect('cart');
        }

        unset($_SESSION['cod_order_number']);

        $seo = SeoHelper::defaultSeo($settings, [
            'seo_title' => 'Order Placed | ' . SeoHelper::shopName($settings),
            'seo_description' => 'Your cash on delivery order has been placed successfully.',
            'seo_canonical' => SeoHelper::absoluteUrl(BASE_URL . 'order/codSuccess?order=' . urlencode($orderNumber)),
            'seo_robots' => 'noindex,nofollow'
        ]);

        $this->view('customer/order_confirmation', [
            'title' => 'Order Placed',
            'settings' => $settings,
            'order' => $order,
            'seo_title' => $seo['seo_title'],
            'seo_description' => $seo['seo_description'],
            'seo_canonical' => $seo['seo_canonical'],
            'seo_image' => $seo['seo_image'],
            'seo_type' => $seo['seo_type'],
            'seo_robots' => $seo['seo_robots'],
            'seo_json_ld' => $seo['seo_json_ld']
        ]);
    }
}
