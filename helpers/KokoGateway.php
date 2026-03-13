<?php

class KokoGateway
{
    public static function isConfigured(array $settings)
    {
        return !empty($settings['koko_enabled'])
            && !empty($settings['koko_merchant_id'])
            && !empty($settings['koko_api_key'])
            && !empty($settings['koko_public_key'])
            && !empty($settings['koko_private_key']);
    }

    public static function checkoutUrl(array $settings)
    {
        return !empty($settings['koko_sandbox'])
            ? 'https://devapi.paykoko.com/api/merchants/orderCreate'
            : 'https://prodapi.paykoko.com/api/merchants/orderCreate';
    }

    public static function buildPayload(array $order, array $settings, $description, $callbackUrl, $pluginName = 'customphp', $pluginVersion = '1.0.0')
    {
        $merchantId = trim((string) $settings['koko_merchant_id']);
        $apiKey = trim((string) $settings['koko_api_key']);
        $amount = number_format((float) ($order['total_amount'] ?? 0), 2, '.', '');
        $currency = trim((string) ($order['currency'] ?? 'LKR'));
        $reference = $merchantId . random_int(111, 999) . '-' . ($order['order_number'] ?? $order['id']);
        $firstName = trim((string) ($order['first_name'] ?? 'Customer'));
        $lastName = trim((string) ($order['last_name'] ?? '-'));
        $email = trim((string) ($order['email'] ?? ''));
        $mobile = trim((string) ($order['phone'] ?? ''));
        $orderId = (string) ($order['id'] ?? '');

        $dataString = $merchantId
            . $amount
            . $currency
            . $pluginName
            . $pluginVersion
            . $callbackUrl
            . $callbackUrl
            . $orderId
            . $reference
            . $firstName
            . $lastName
            . $email
            . $description
            . $apiKey
            . $callbackUrl;

        $signatureEncoded = self::sign($dataString, (string) ($settings['koko_private_key'] ?? ''));

        return [
            '_mId' => $merchantId,
            'api_key' => $apiKey,
            '_returnUrl' => $callbackUrl,
            '_responseUrl' => $callbackUrl,
            '_currency' => $currency,
            '_amount' => $amount,
            '_reference' => $reference,
            '_pluginName' => $pluginName,
            '_pluginVersion' => $pluginVersion,
            '_cancelUrl' => $callbackUrl,
            '_orderId' => $orderId,
            '_firstName' => $firstName,
            '_lastName' => $lastName,
            '_email' => $email,
            '_description' => $description,
            'dataString' => $dataString,
            'signature' => $signatureEncoded,
            '_mobileNo' => $mobile
        ];
    }

    public static function verifyCallback($orderIdRaw, $trnIdRaw, $statusRaw, $descRaw, $signatureParam, $publicKey)
    {
        $signature = base64_decode((string) $signatureParam, true);
        if ($signature === false) {
            return false;
        }

        $publicKeyResource = openssl_pkey_get_public((string) $publicKey);
        if (!$publicKeyResource) {
            return false;
        }

        $dataString = (string) $orderIdRaw . (string) $trnIdRaw . (string) $statusRaw . (string) $descRaw;
        $verified = openssl_verify($dataString, $signature, $publicKeyResource, OPENSSL_ALGO_SHA256);
        openssl_free_key($publicKeyResource);

        return $verified === 1;
    }

    public static function normalizeStatus($status)
    {
        $status = strtoupper(trim((string) $status));

        if (in_array($status, ['SUCCESS', 'APPROVED', 'COMPLETED'], true)) {
            return 'paid';
        }

        if (in_array($status, ['CANCELLED', 'CANCELED'], true)) {
            return 'cancelled';
        }

        if (in_array($status, ['FAILED', 'DECLINED', 'ERROR'], true)) {
            return 'failed';
        }

        return 'pending';
    }

    private static function sign($dataString, $privateKey)
    {
        $privateKeyResource = openssl_pkey_get_private((string) $privateKey);
        if (!$privateKeyResource) {
            throw new Exception('Invalid KOKO private key.');
        }

        $signature = '';
        $result = openssl_sign($dataString, $signature, $privateKeyResource, OPENSSL_ALGO_SHA256);
        openssl_free_key($privateKeyResource);

        if (!$result) {
            throw new Exception('Unable to sign KOKO request.');
        }

        return base64_encode($signature);
    }
}
