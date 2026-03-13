<?php
require_once 'models/Order.php';
require_once 'models/Setting.php';
require_once 'helpers/SeoHelper.php';

class OrderController extends BaseController
{
    private $orderModel;
    private $settingModel;

    public function __construct()
    {
        $this->orderModel = new Order();
        $this->settingModel = new Setting();
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
        $orders = $this->orderModel->getRecent(50);

        $this->view('admin/orders/index', [
            'title' => 'Orders',
            'settings' => $settings,
            'orders' => $orders
        ]);
    }

    public function details($orderNumber = null)
    {
        $this->requireAdminSession();

        $settings = $this->settingModel->getAllPairs();
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

    public function markCompleted($orderNumber = null)
    {
        $this->requireAdminSession();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($orderNumber)) {
            $this->redirect('order/manage');
        }

        $order = $this->orderModel->getByOrderNumber($orderNumber);
        if ($order) {
            $this->orderModel->updateOrderStatus($orderNumber, 'completed');
        }

        $this->redirect('order/details/' . urlencode($orderNumber));
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

        $customerName = trim($_POST['customer_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $country = trim($_POST['country'] ?? 'Sri Lanka');

        if ($customerName === '' || $email === '' || $phone === '' || $address === '' || $city === '' || $country === '') {
            $_SESSION['order_error'] = 'Please fill in all required payment fields.';
            $this->redirect('cart');
        }

        [$firstName, $lastName] = $this->splitName($customerName);

        $customer = [
            'customer_name' => $customerName,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'phone' => $phone,
            'phone_alt' => trim($_POST['phone_alt'] ?? ''),
            'address' => $address,
            'city' => $city,
            'district' => trim($_POST['district'] ?? ''),
            'postal_code' => trim($_POST['postal_code'] ?? ''),
            'country' => $country,
            'note' => trim($_POST['note'] ?? '')
        ];

        $order = $this->orderModel->createFromCart($customer, $cart, $settings);
        if (!$order) {
            $_SESSION['order_error'] = 'Unable to create your order right now.';
            $this->redirect('cart');
        }

        $_SESSION['pending_order_number'] = $order['order_number'];

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

    public function payhereNotify()
    {
        $settings = $this->settingModel->getAllPairs();
        $merchantId = $_POST['merchant_id'] ?? '';
        $orderNumber = $_POST['order_id'] ?? '';
        $paymentId = $_POST['payment_id'] ?? '';
        $payhereAmount = $_POST['payhere_amount'] ?? '';
        $payhereCurrency = $_POST['payhere_currency'] ?? '';
        $statusCode = $_POST['status_code'] ?? '';
        $md5sig = $_POST['md5sig'] ?? '';

        $order = $this->orderModel->getByOrderNumber($orderNumber);
        if (!$order || $merchantId !== ($settings['payhere_merchant_id'] ?? '')) {
            http_response_code(400);
            echo 'INVALID';
            exit;
        }

        $merchantSecret = trim($settings['payhere_merchant_secret'] ?? '');
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
            http_response_code(400);
            echo 'INVALID';
            exit;
        }

        $status = 'pending';
        $message = 'Payment is pending.';

        if ((string) $statusCode === '2') {
            $status = 'paid';
            $message = 'Payment completed successfully.';
        } elseif ((string) $statusCode === '-1') {
            $status = 'cancelled';
            $message = 'Payment cancelled by customer.';
        } elseif ((string) $statusCode === '-2') {
            $status = 'failed';
            $message = 'Payment failed.';
        } elseif ((string) $statusCode === '-3') {
            $status = 'chargedback';
            $message = 'Payment charged back.';
        }

        $this->orderModel->updatePaymentStatus($orderNumber, $status, $paymentId, $statusCode, $message);
        if ($status === 'paid' && (($order['order_status'] ?? 'pending') === 'pending')) {
            $this->orderModel->updateOrderStatus($orderNumber, 'processing');
        }
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

        if ($order && $order['payment_status'] === 'pending') {
            $this->orderModel->updatePaymentStatus($orderNumber, 'cancelled', $order['gateway_payment_id'], $order['gateway_status_code'], 'Payment cancelled before completion.');
            $order = $this->orderModel->getByOrderNumber($orderNumber);
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
}
