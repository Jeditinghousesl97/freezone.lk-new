<?php
require_once 'models/Setting.php';
require_once 'models/SmsNotification.php';
require_once 'helpers/SeoHelper.php';
require_once 'helpers/SmsLenzClient.php';

class OrderSmsService
{
    private $settingModel;
    private $notificationModel;
    private $client;

    private $defaultTemplates = [
        'order_placed' => 'Hi {customer_name}, your order {order_number} at {shop_name} has been placed. Total: {currency} {total_amount}.',
        'payment_completed' => 'Good news {customer_name}. Payment completed for order {order_number} at {shop_name}.',
        'payment_cancelled' => 'Your payment was cancelled for order {order_number} at {shop_name}.',
        'payment_failed' => 'We could not confirm payment for order {order_number} at {shop_name}. Please try again or contact us.',
        'payment_received' => 'Payment received for your order {order_number} at {shop_name}. Thank you.',
        'order_completed' => 'Your order {order_number} from {shop_name} is completed. Thank you for shopping with us.',
        'order_cancelled' => 'Your order {order_number} from {shop_name} has been cancelled.',
    ];

    public function __construct()
    {
        $this->settingModel = new Setting();
        $this->notificationModel = new SmsNotification();
        $this->client = new SmsLenzClient();
    }

    public function getDefaultTemplate($eventKey)
    {
        return $this->defaultTemplates[$eventKey] ?? '';
    }

    public function sendForEvent(array $order, $eventKey)
    {
        if (empty($order['id'])) {
            return;
        }

        $settings = $this->settingModel->getAllPairs();
        if (!$this->isReady($settings)) {
            return;
        }

        $phone = $this->normalizePhone((string) ($order['phone'] ?? ''));
        if ($phone === '') {
            $this->logFailure($eventKey, (string) ($order['phone'] ?? ''), 'Invalid customer phone number.');
            return;
        }

        if ($this->notificationModel->wasSent((int) $order['id'], $eventKey, $phone)) {
            return;
        }

        $message = $this->buildMessage($order, $settings, $eventKey);
        if ($message === '') {
            return;
        }

        try {
            $response = $this->client->send($settings, $phone, $message);
            $this->notificationModel->markSent((int) $order['id'], $eventKey, $phone);
            $this->logSuccess($eventKey, $phone, $response['body'] ?? '');
        } catch (Exception $e) {
            $this->logFailure($eventKey, $phone, $e->getMessage());
        }
    }

    private function isReady(array $settings)
    {
        return !empty($settings['sms_enabled'])
            && !empty($settings['sms_user_id'])
            && !empty($settings['sms_api_key'])
            && !empty($settings['sms_sender_id']);
    }

    private function buildMessage(array $order, array $settings, $eventKey)
    {
        $templateKey = 'sms_template_' . $eventKey;
        $template = trim((string) ($settings[$templateKey] ?? ''));
        if ($template === '') {
            $template = $this->getDefaultTemplate($eventKey);
        }

        if ($template === '') {
            return '';
        }

        $placeholders = [
            '{shop_name}' => SeoHelper::shopName($settings),
            '{customer_name}' => (string) ($order['customer_name'] ?? 'Customer'),
            '{order_number}' => (string) ($order['order_number'] ?? ''),
            '{currency}' => (string) ($order['currency'] ?? ($settings['currency_symbol'] ?? 'LKR')),
            '{total_amount}' => number_format((float) ($order['total_amount'] ?? 0), 2),
            '{payment_status}' => ucfirst(str_replace('_', ' ', (string) ($order['payment_status'] ?? 'pending'))),
            '{order_status}' => ucfirst(str_replace('_', ' ', (string) ($order['order_status'] ?? 'pending'))),
            '{payment_method}' => strtoupper((string) ($order['payment_method'] ?? $order['payment_gateway'] ?? 'ORDER')),
            '{shop_whatsapp}' => (string) ($settings['shop_whatsapp'] ?? ''),
            '{website_url}' => SeoHelper::absoluteUrl(BASE_URL)
        ];

        $message = strtr($template, $placeholders);
        $message = preg_replace('/\s+/', ' ', trim((string) $message));

        if (function_exists('mb_substr')) {
            return mb_substr($message, 0, 621);
        }

        return substr($message, 0, 621);
    }

    private function normalizePhone($phone)
    {
        $phone = trim($phone);
        if ($phone === '') {
            return '';
        }

        $phone = preg_replace('/[^\d+]/', '', $phone);
        if (strpos($phone, '+94') === 0) {
            $digits = substr($phone, 3);
            return ctype_digit($digits) ? '+94' . $digits : '';
        }

        if (strpos($phone, '94') === 0) {
            $digits = substr($phone, 2);
            return ctype_digit($digits) ? '+94' . $digits : '';
        }

        if (strpos($phone, '0') === 0) {
            $digits = substr($phone, 1);
            return ctype_digit($digits) ? '+94' . $digits : '';
        }

        return '';
    }

    private function logSuccess($eventKey, $recipientPhone, $responseBody)
    {
        $this->writeLog([
            'time' => date('c'),
            'event' => $eventKey,
            'recipient' => $recipientPhone,
            'status' => 'sent',
            'response' => substr((string) $responseBody, 0, 500)
        ]);
    }

    private function logFailure($eventKey, $recipientPhone, $message)
    {
        $this->writeLog([
            'time' => date('c'),
            'event' => $eventKey,
            'recipient' => $recipientPhone,
            'status' => 'failed',
            'message' => $message
        ]);
    }

    private function writeLog(array $entry)
    {
        $logDir = ROOT_PATH . 'storage/logs/';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0775, true);
        }

        file_put_contents(
            $logDir . 'sms.log',
            json_encode($entry, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL,
            FILE_APPEND | LOCK_EX
        );
    }
}
