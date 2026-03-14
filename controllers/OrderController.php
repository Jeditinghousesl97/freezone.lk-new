<?php
require_once 'models/Order.php';
require_once 'models/Setting.php';
require_once 'models/Product.php';
require_once 'models/DeliverySetting.php';
require_once 'helpers/DeliveryHelper.php';
require_once 'helpers/SeoHelper.php';
require_once 'helpers/OrderEmailService.php';
require_once 'helpers/OrderSmsService.php';
require_once 'helpers/KokoGateway.php';

class OrderController extends BaseController
{
    private $orderModel;
    private $settingModel;
    private $productModel;
    private $deliverySettingModel;
    private $orderEmailService;
    private $orderSmsService;

    public function __construct()
    {
        $this->orderModel = new Order();
        $this->settingModel = new Setting();
        $this->productModel = new Product();
        $this->deliverySettingModel = new DeliverySetting();
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
            $this->logSmsWorkerEvent('shutdown_worker_started');

            if (function_exists('fastcgi_finish_request')) {
                @fastcgi_finish_request();
            } else {
                @ob_end_flush();
                @flush();
            }

            try {
                $this->logSmsWorkerEvent('shutdown_worker_finished_noop');
            } catch (Throwable $e) {
                $this->logSmsWorkerEvent('shutdown_worker_failed', [
                    'message' => $e->getMessage()
                ]);
            }
        });
    }

    private function dispatchSmsSendAsync(array $order, $eventKey)
    {
        register_shutdown_function(function () use ($order, $eventKey) {
            if (function_exists('session_write_close')) {
                @session_write_close();
            }

            ignore_user_abort(true);
            $this->logSmsWorkerEvent('direct_sms_started', [
                'event' => $eventKey,
                'order_id' => $order['id'] ?? null
            ]);

            if (function_exists('fastcgi_finish_request')) {
                @fastcgi_finish_request();
            } else {
                @ob_end_flush();
                @flush();
            }

            try {
                $this->orderSmsService->sendDirectForEvent($order, $eventKey);
                $this->logSmsWorkerEvent('direct_sms_finished', [
                    'event' => $eventKey,
                    'order_id' => $order['id'] ?? null
                ]);
            } catch (Throwable $e) {
                $this->logSmsWorkerEvent('direct_sms_failed', [
                    'event' => $eventKey,
                    'order_id' => $order['id'] ?? null,
                    'message' => $e->getMessage()
                ]);
            }
        });
    }

    private function dispatchSmsQueueAsyncLegacy()
    {
        $workerUrl = SeoHelper::absoluteUrl(BASE_URL . 'order/processSmsQueue?token=' . urlencode($this->smsQueueToken()));
        register_shutdown_function(function () use ($workerUrl) {
            try {
                $this->fireAndForgetUrl($workerUrl);
                $this->logSmsWorkerEvent('shutdown_worker_finished');
            } catch (Throwable $e) {
                $this->logSmsWorkerEvent('shutdown_worker_failed', [
                    'message' => $e->getMessage()
                ]);
            }
        });
    }

    private function fireAndForgetUrl($url)
    {
        $parts = parse_url($url);
        if (empty($parts['host'])) {
            return;
        }

        $scheme = $parts['scheme'] ?? 'https';
        $host = $parts['host'];
        $port = isset($parts['port']) ? (int) $parts['port'] : ($scheme === 'https' ? 443 : 80);
        $path = ($parts['path'] ?? '/') . (isset($parts['query']) ? '?' . $parts['query'] : '');
        $transport = ($scheme === 'https' ? 'ssl://' : '') . $host;

        $fp = @fsockopen($transport, $port, $errno, $errstr, 1.0);
        if (!$fp) {
            $this->logSmsWorkerEvent('socket_worker_skipped', [
                'message' => $errstr,
                'code' => $errno
            ]);
            return;
        }

        stream_set_timeout($fp, 0, 300000);
        fwrite($fp, "GET {$path} HTTP/1.1\r\n");
        fwrite($fp, "Host: {$host}\r\n");
        fwrite($fp, "Connection: Close\r\n\r\n");
        fclose($fp);
        $this->logSmsWorkerEvent('socket_worker_dispatched', [
            'url' => $url
        ]);
    }

    private function notifyCustomerOrderEvent(array $order, $eventKey)
    {
        $this->orderEmailService->sendForEvent($order, $eventKey);
        $this->dispatchSmsSendAsync($order, $eventKey);
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
        $this->logSmsWorkerEvent('manual_worker_started');
        $this->orderSmsService->processQueue(8);
        $this->logSmsWorkerEvent('manual_worker_finished');
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

    private function logSmsWorkerEvent($event, array $context = [])
    {
        $logDir = ROOT_PATH . 'storage/logs/';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0775, true);
        }

        file_put_contents(
            $logDir . 'sms_worker.log',
            json_encode([
                'time' => date('c'),
                'event' => $event,
                'context' => $context
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL,
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
        $district = trim((string) ($_POST['district'] ?? ''));

        if ($customerName === '' || $email === '' || $phone === '' || $address === '' || $city === '' || $district === '') {
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
            'district' => $district,
            'postal_code' => '',
            'country' => 'Sri Lanka',
            'note' => trim((string) ($_POST['note'] ?? ''))
        ];
    }

    private function buildCartItemsWithDeliveryData(array $cart)
    {
        foreach ($cart as &$item) {
            $productId = (int) ($item['id'] ?? 0);
            if ($productId <= 0) {
                continue;
            }

            $product = $this->productModel->getById($productId);
            if (!$product) {
                continue;
            }

            $item['title'] = $product['title'] ?? ($item['title'] ?? 'Product');
            $item['price'] = (!empty($product['sale_price']) && (float) $product['sale_price'] < (float) $product['price'])
                ? (float) $product['sale_price']
                : (float) $product['price'];
            $item['weight_grams'] = max(0, (int) ($product['weight_grams'] ?? 0));
            $item['is_free_shipping'] = !empty($product['free_shipping']) ? 1 : 0;
            $item['variant_key'] = trim((string) ($item['variant_key'] ?? ''));

            if (empty($item['img']) && !empty($product['main_image'])) {
                $imagePath = 'assets/uploads/' . $product['main_image'];
                if (file_exists(ROOT_PATH . $imagePath)) {
                    $item['img'] = BASE_URL . $imagePath;
                }
            }
        }

        return $cart;
    }

    private function validateCartStockOrRedirect(array $cart, $redirectTarget = 'cart')
    {
        foreach ($cart as $item) {
            $validation = $this->productModel->validatePurchase(
                (int) ($item['id'] ?? 0),
                max(1, (int) ($item['qty'] ?? 1)),
                trim((string) ($item['variant_key'] ?? ''))
            );

            if (empty($validation['ok'])) {
                $_SESSION['order_error'] = $validation['message'] ?? 'Some items are no longer available.';
                $this->redirect($redirectTarget);
            }
        }
    }

    private function deductStockForItems(array $items)
    {
        foreach ($items as $item) {
            $this->productModel->reduceStockForLineItem(
                (int) ($item['id'] ?? 0),
                max(1, (int) ($item['qty'] ?? 1)),
                trim((string) ($item['variant_key'] ?? ''))
            );
        }
    }

    private function buildSingleProductItem(array $product, $qty, $variantText, $variantKey = '')
    {
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

        return [[
            'id' => (int) $product['id'],
            'title' => $product['title'] ?? 'Product',
            'price' => $unitPrice,
            'qty' => max(1, (int) $qty),
            'img' => $imageUrl,
            'variants' => $variantText,
            'variant_key' => trim((string) $variantKey),
            'weight_grams' => max(0, (int) ($product['weight_grams'] ?? 0)),
            'is_free_shipping' => !empty($product['free_shipping']) ? 1 : 0
        ]];
    }

    private function buildShippingQuote(array $items, array $settings, $district)
    {
        return DeliveryHelper::calculateShipping(
            $items,
            $district,
            $settings,
            $this->deliverySettingModel->getRatesMap()
        );
    }

    private function requireAdminSession()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('auth/login');
        }
    }

    private function appendOptionalSecret($url, $secret)
    {
        $secret = trim((string) $secret);
        if ($secret === '') {
            return $url;
        }

        $separator = strpos($url, '?') === false ? '?' : '&';
        return $url . $separator . 'secret=' . urlencode($secret);
    }

    private function buildSingleProductItems($productId, $qty, $variantText, $variantKey = '')
    {
        $product = $this->productModel->getById($productId);
        if (!$product) {
            return [null, null];
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

        return [$product, [[
            'id' => (int) $product['id'],
            'title' => $product['title'] ?? 'Product',
            'price' => $unitPrice,
            'qty' => $qty,
            'img' => $imageUrl,
            'variants' => $variantText,
            'variant_key' => trim((string) $variantKey),
            'weight_grams' => max(0, (int) ($product['weight_grams'] ?? 0)),
            'is_free_shipping' => !empty($product['free_shipping']) ? 1 : 0
        ]]];
    }

    private function renderKokoRedirect(array $order, array $settings, $description)
    {
        $callbackUrl = SeoHelper::absoluteUrl(BASE_URL . 'order/kokoCallback');
        $callbackUrl = $this->appendOptionalSecret($callbackUrl, $settings['koko_callback_secret'] ?? '');
        $kokoPayload = KokoGateway::buildPayload($order, $settings, $description, $callbackUrl, 'woocommerce', '2.0.11');
        $kokoEndpoint = KokoGateway::checkoutUrl($settings);

        require 'views/customer/koko_redirect.php';
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

    public function markGatewayPaymentRecorded($orderNumber = null)
    {
        $this->requireAdminSession();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($orderNumber)) {
            $this->redirect('order/manage');
        }

        $order = $this->orderModel->getByOrderNumber($orderNumber);
        $paymentMethod = strtolower((string) ($order['payment_method'] ?? ''));
        $paymentStatus = strtolower((string) ($order['payment_status'] ?? 'pending'));

        if ($order && in_array($paymentMethod, ['payhere', 'koko'], true) && $paymentStatus !== 'paid') {
            $gatewayLabel = strtoupper((string) ($order['payment_gateway'] ?: $paymentMethod));
            $manualMessage = $gatewayLabel . ' payment recorded manually by shop owner.';

            $this->orderModel->updatePaymentStatus(
                $orderNumber,
                'paid',
                $order['gateway_payment_id'] ?? null,
                'MANUAL_CONFIRMED',
                $manualMessage
            );

            $this->orderModel->recordTransaction(
                (int) $order['id'],
                $paymentMethod,
                'manual_payment_recorded',
                $order['gateway_payment_id'] ?? null,
                'MANUAL_CONFIRMED',
                (float) ($order['total_amount'] ?? 0),
                $order['currency'] ?? 'LKR',
                ['marked_by' => 'shop_owner']
            );

            if (($order['order_status'] ?? 'pending') === 'pending') {
                $this->orderModel->updateOrderStatus($orderNumber, 'processing');
            }

            $updatedOrder = $this->orderModel->getByOrderNumberWithItems($orderNumber);
            if ($updatedOrder) {
                $this->notifyCustomerOrderEvent($updatedOrder, 'payment_completed');
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
        $cart = $this->buildCartItemsWithDeliveryData($_SESSION['cart'] ?? []);

        if (empty($cart)) {
            $_SESSION['order_error'] = 'Your cart is empty.';
            $this->redirect('cart');
        }
        $this->validateCartStockOrRedirect($cart, 'cart');
        $this->validateCartStockOrRedirect($cart, 'cart');
        $this->validateCartStockOrRedirect($cart, 'cart');

        if (empty($settings['payhere_enabled']) || empty($settings['payhere_merchant_id']) || empty($settings['payhere_merchant_secret'])) {
            $_SESSION['order_error'] = 'PayHere is not configured for this shop yet.';
            $this->redirect('cart');
        }

        $customer = $this->buildCustomerFromRequest();
        if (!$customer) {
            $_SESSION['order_error'] = 'Please fill in all required payment fields.';
            $this->redirect('cart');
        }

        $shippingQuote = $this->buildShippingQuote($cart, $settings, $customer['district']);
        if (!$shippingQuote['has_rate']) {
            $_SESSION['order_error'] = 'Please select a valid district to calculate delivery.';
            $this->redirect('cart');
        }

        $order = $this->orderModel->createFromCart($customer, $cart, $settings, [
            'subtotal_amount' => $shippingQuote['subtotal'],
            'shipping_fee' => $shippingQuote['shipping_fee'],
            'chargeable_weight_grams' => $shippingQuote['chargeable_weight_grams']
        ]);
        if (!$order) {
            $_SESSION['order_error'] = 'Unable to create your order right now.';
            $this->redirect('cart');
        }
        $this->deductStockForItems($cart);
        $this->deductStockForItems($cart);

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

    public function startKoko()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('cart');
        }

        $settings = $this->settingModel->getAllPairs();
        $cart = $this->buildCartItemsWithDeliveryData($_SESSION['cart'] ?? []);

        if (empty($cart)) {
            $_SESSION['order_error'] = 'Your cart is empty.';
            $this->redirect('cart');
        }

        if (!KokoGateway::isConfigured($settings)) {
            $_SESSION['order_error'] = 'KOKO is not configured for this shop yet.';
            $this->redirect('cart');
        }

        $customer = $this->buildCustomerFromRequest();
        if (!$customer) {
            $_SESSION['order_error'] = 'Please fill in all required payment fields.';
            $this->redirect('cart');
        }

        $shippingQuote = $this->buildShippingQuote($cart, $settings, $customer['district']);
        if (!$shippingQuote['has_rate']) {
            $_SESSION['order_error'] = 'Please select a valid district to calculate delivery.';
            $this->redirect('cart');
        }

        $order = $this->orderModel->createFromCart($customer, $cart, $settings, [
            'subtotal_amount' => $shippingQuote['subtotal'],
            'shipping_fee' => $shippingQuote['shipping_fee'],
            'chargeable_weight_grams' => $shippingQuote['chargeable_weight_grams'],
            'payment_method' => 'koko',
            'payment_gateway' => 'koko',
            'payment_status' => 'pending',
            'order_status' => 'pending',
            'transaction_type' => 'koko_order_created',
            'transaction_status_code' => 'PENDING',
            'transaction_payload' => [
                'customer' => $customer,
                'items_count' => count($cart),
                'source' => 'cart_koko'
            ]
        ]);

        if (!$order) {
            $_SESSION['order_error'] = 'Unable to create your order right now.';
            $this->redirect('cart');
        }

        $_SESSION['pending_order_number'] = $order['order_number'];
        $fullOrder = $this->orderModel->getByOrderNumberWithItems($order['order_number']);
        if ($fullOrder) {
            $this->notifyCustomerOrderEvent($fullOrder, 'order_placed');
            $order = $fullOrder;
        }

        try {
            $this->renderKokoRedirect($order, $settings, SeoHelper::shopName($settings) . ' Order');
        } catch (Exception $e) {
            $_SESSION['order_error'] = 'KOKO checkout is not ready right now. Please contact the shop owner.';
            $this->redirect('cart');
        }
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
        $cart = $this->buildCartItemsWithDeliveryData($_SESSION['cart'] ?? []);

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

        $shippingQuote = $this->buildShippingQuote($cart, $settings, $customer['district']);
        if (!$shippingQuote['has_rate']) {
            $_SESSION['order_error'] = 'Please select a valid district to calculate delivery.';
            $this->redirect('cart');
        }

        $order = $this->orderModel->createFromCart($customer, $cart, $settings, [
            'subtotal_amount' => $shippingQuote['subtotal'],
            'shipping_fee' => $shippingQuote['shipping_fee'],
            'chargeable_weight_grams' => $shippingQuote['chargeable_weight_grams'],
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
        $this->deductStockForItems($cart);

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
        $variantKey = trim((string) ($_POST['variant_key'] ?? ''));

        $product = $this->productModel->getById($productId);
        if (!$product) {
            $_SESSION['order_error'] = 'The selected product could not be found.';
            $this->redirect('cart');
        }
        $validation = $this->productModel->validatePurchase($productId, $qty, $variantKey);
        if (empty($validation['ok'])) {
            $_SESSION['order_error'] = $validation['message'] ?? 'This product is not available.';
            $this->redirect('shop/product/' . $productId);
        }

        $customer = $this->buildCustomerFromRequest();
        if (!$customer) {
            $_SESSION['order_error'] = 'Please fill in all required payment fields.';
            $this->redirect('shop/product/' . $productId);
        }

        $items = $this->buildSingleProductItem($product, $qty, $variantText, $variantKey);
        $shippingQuote = $this->buildShippingQuote($items, $settings, $customer['district']);
        if (!$shippingQuote['has_rate']) {
            $_SESSION['order_error'] = 'Please select a valid district to calculate delivery.';
            $this->redirect('shop/product/' . $productId);
        }

        $order = $this->orderModel->createFromItems($customer, $items, $settings, [
            'subtotal_amount' => $shippingQuote['subtotal'],
            'shipping_fee' => $shippingQuote['shipping_fee'],
            'chargeable_weight_grams' => $shippingQuote['chargeable_weight_grams']
        ]);
        if (!$order) {
            $_SESSION['order_error'] = 'Unable to create your order right now.';
            $this->redirect('shop/product/' . $productId);
        }
        $this->deductStockForItems($items);

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

    public function startKokoSingle()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('cart');
        }

        $settings = $this->settingModel->getAllPairs();

        if (!KokoGateway::isConfigured($settings)) {
            $_SESSION['order_error'] = 'KOKO is not configured for this shop yet.';
            $this->redirect('cart');
        }

        $productId = (int) ($_POST['product_id'] ?? 0);
        $qty = max(1, (int) ($_POST['quantity'] ?? 1));
        $variantText = trim((string) ($_POST['variants'] ?? ''));
        $variantKey = trim((string) ($_POST['variant_key'] ?? ''));

        $validation = $this->productModel->validatePurchase($productId, $qty, $variantKey);
        if (empty($validation['ok'])) {
            $_SESSION['order_error'] = $validation['message'] ?? 'This product is not available.';
            $this->redirect('shop/product/' . $productId);
        }

        [$product, $items] = $this->buildSingleProductItems($productId, $qty, $variantText, $variantKey);
        if (!$product || !$items) {
            $_SESSION['order_error'] = 'The selected product could not be found.';
            $this->redirect('cart');
        }

        $customer = $this->buildCustomerFromRequest();
        if (!$customer) {
            $_SESSION['order_error'] = 'Please fill in all required payment fields.';
            $this->redirect('shop/product/' . $productId);
        }

        $shippingQuote = $this->buildShippingQuote($items, $settings, $customer['district']);
        if (!$shippingQuote['has_rate']) {
            $_SESSION['order_error'] = 'Please select a valid district to calculate delivery.';
            $this->redirect('shop/product/' . $productId);
        }

        $order = $this->orderModel->createFromItems($customer, $items, $settings, [
            'subtotal_amount' => $shippingQuote['subtotal'],
            'shipping_fee' => $shippingQuote['shipping_fee'],
            'chargeable_weight_grams' => $shippingQuote['chargeable_weight_grams'],
            'payment_method' => 'koko',
            'payment_gateway' => 'koko',
            'payment_status' => 'pending',
            'order_status' => 'pending',
            'transaction_type' => 'koko_order_created',
            'transaction_status_code' => 'PENDING',
            'transaction_payload' => [
                'customer' => $customer,
                'items_count' => count($items),
                'source' => 'single_koko'
            ]
        ]);

        if (!$order) {
            $_SESSION['order_error'] = 'Unable to create your order right now.';
            $this->redirect('shop/product/' . $productId);
        }
        $this->deductStockForItems($items);

        $_SESSION['pending_order_number'] = $order['order_number'];
        $fullOrder = $this->orderModel->getByOrderNumberWithItems($order['order_number']);
        if ($fullOrder) {
            $this->notifyCustomerOrderEvent($fullOrder, 'order_placed');
            $order = $fullOrder;
        }

        try {
            $this->renderKokoRedirect($order, $settings, $product['title'] ?? (SeoHelper::shopName($settings) . ' Order'));
        } catch (Exception $e) {
            $_SESSION['order_error'] = 'KOKO checkout is not ready right now. Please contact the shop owner.';
            $this->redirect('shop/product/' . $productId);
        }
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
        $variantKey = trim((string) ($_POST['variant_key'] ?? ''));

        $product = $this->productModel->getById($productId);
        if (!$product) {
            $_SESSION['order_error'] = 'The selected product could not be found.';
            $this->redirect('cart');
        }
        $validation = $this->productModel->validatePurchase($productId, $qty, $variantKey);
        if (empty($validation['ok'])) {
            $_SESSION['order_error'] = $validation['message'] ?? 'This product is not available.';
            $this->redirect('shop/product/' . $productId);
        }

        $customer = $this->buildCustomerFromRequest();
        if (!$customer) {
            $_SESSION['order_error'] = 'Please fill in all required order fields.';
            $this->redirect('shop/product/' . $productId);
        }

        $items = $this->buildSingleProductItem($product, $qty, $variantText, $variantKey);
        $shippingQuote = $this->buildShippingQuote($items, $settings, $customer['district']);
        if (!$shippingQuote['has_rate']) {
            $_SESSION['order_error'] = 'Please select a valid district to calculate delivery.';
            $this->redirect('shop/product/' . $productId);
        }

        $order = $this->orderModel->createFromItems($customer, $items, $settings, [
            'subtotal_amount' => $shippingQuote['subtotal'],
            'shipping_fee' => $shippingQuote['shipping_fee'],
            'chargeable_weight_grams' => $shippingQuote['chargeable_weight_grams'],
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
        $this->deductStockForItems($items);

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

    public function kokoCallback()
    {
        $settings = $this->settingModel->getAllPairs();

        if (!empty($settings['koko_callback_secret'])) {
            $providedSecret = '';
            if (isset($_SERVER['HTTP_X_DARAZBNPL_SECRET'])) {
                $providedSecret = (string) $_SERVER['HTTP_X_DARAZBNPL_SECRET'];
            } elseif (isset($_REQUEST['secret'])) {
                $providedSecret = (string) $_REQUEST['secret'];
            }

            if (!hash_equals((string) $settings['koko_callback_secret'], $providedSecret)) {
                http_response_code(403);
                echo 'FORBIDDEN';
                exit;
            }
        }

        $orderIdRaw = isset($_REQUEST['orderId']) ? (string) $_REQUEST['orderId'] : '';
        $trnIdRaw = isset($_REQUEST['trnId']) ? (string) $_REQUEST['trnId'] : '';
        $statusRaw = isset($_REQUEST['status']) ? (string) $_REQUEST['status'] : '';
        $descRaw = isset($_REQUEST['desc']) ? (string) $_REQUEST['desc'] : '';
        $signatureParam = isset($_REQUEST['signature']) ? (string) $_REQUEST['signature'] : '';

        $orderId = (int) trim($orderIdRaw);
        $order = $orderId > 0 ? $this->orderModel->getById($orderId) : null;
        if (!$order) {
            $this->redirect('cart');
        }

        $paymentStatus = 'pending';
        $message = trim($descRaw) !== '' ? trim($descRaw) : 'KOKO payment pending.';
        $signatureValid = false;

        if ($signatureParam !== '' && !empty($settings['koko_public_key'])) {
            $signatureValid = KokoGateway::verifyCallback($orderIdRaw, $trnIdRaw, $statusRaw, $descRaw, $signatureParam, (string) $settings['koko_public_key']);
        }

        $this->orderModel->recordTransaction(
            (int) $order['id'],
            'koko',
            'callback',
            trim($trnIdRaw) !== '' ? trim($trnIdRaw) : null,
            trim($statusRaw) !== '' ? trim($statusRaw) : null,
            (float) ($order['total_amount'] ?? 0),
            $order['currency'] ?? 'LKR',
            $_REQUEST
        );

        if (!$signatureValid) {
            $paymentStatus = 'verification_failed';
            $message = 'KOKO signature verification failed.';
        } else {
            $paymentStatus = KokoGateway::normalizeStatus($statusRaw);
            if ($paymentStatus === 'paid') {
                $message = trim($descRaw) !== '' ? trim($descRaw) : 'KOKO payment completed successfully.';
            } elseif ($paymentStatus === 'cancelled') {
                $message = trim($descRaw) !== '' ? trim($descRaw) : 'KOKO payment was cancelled.';
            } elseif ($paymentStatus === 'failed') {
                $message = trim($descRaw) !== '' ? trim($descRaw) : 'KOKO payment failed.';
            }
        }

        $this->orderModel->updatePaymentStatus($order['order_number'], $paymentStatus, trim($trnIdRaw) ?: null, trim($statusRaw) ?: null, $message);
        if ($paymentStatus === 'paid' && (($order['order_status'] ?? 'pending') === 'pending')) {
            $this->orderModel->updateOrderStatus($order['order_number'], 'processing');
        }

        $updatedOrder = $this->orderModel->getByOrderNumberWithItems($order['order_number']);
        if ($updatedOrder) {
            if ($paymentStatus === 'paid') {
                $this->notifyCustomerOrderEvent($updatedOrder, 'payment_completed');
            } elseif ($paymentStatus === 'cancelled') {
                $this->notifyCustomerOrderEvent($updatedOrder, 'payment_cancelled');
            } elseif ($paymentStatus === 'failed' || $paymentStatus === 'verification_failed') {
                $this->notifyCustomerOrderEvent($updatedOrder, 'payment_failed');
            }
            $order = $updatedOrder;
        }

        if ($paymentStatus === 'paid' && !empty($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        unset($_SESSION['pending_order_number']);

        $seo = SeoHelper::defaultSeo($settings, [
            'seo_title' => 'Payment Status | ' . SeoHelper::shopName($settings),
            'seo_description' => 'Check the latest status of your payment order.',
            'seo_canonical' => SeoHelper::absoluteUrl(BASE_URL . 'order/kokoCallback?orderId=' . urlencode((string) $orderId)),
            'seo_robots' => 'noindex,nofollow'
        ]);

        $this->view('customer/payment_status', [
            'title' => 'Payment Status',
            'settings' => $settings,
            'order' => $order,
            'status_type' => 'return',
            'gateway_name' => 'KOKO',
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
