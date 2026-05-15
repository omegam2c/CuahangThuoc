<?php

class PayOSService {
    private $clientId;
    private $apiKey;
    private $checksumKey;
    private $apiUrl = "https://api-merchant.payos.vn/v2/payment-requests";

    public function __construct() {
        $this->clientId = PAYOS_CLIENT_ID;
        $this->apiKey = PAYOS_API_KEY;
        $this->checksumKey = PAYOS_CHECKSUM_KEY;
    }

    /**
     * Tạo link thanh toán PayOS
     */
    public function createPaymentLink($orderData) {
        if (empty($this->clientId)) return null;

        $data = [
            "orderCode" => (int)$orderData['order_id'],
            "amount" => (int)$orderData['amount'],
            "description" => "Thanh toan don hang #" . $orderData['order_id'],
            "returnUrl" => PAYOS_RETURN_URL . "?id=" . $orderData['order_id'],
            "cancelUrl" => PAYOS_CANCEL_URL . "?id=" . $orderData['order_id'],
        ];

        // Tạo chữ ký (Signature)
        $data['signature'] = $this->createSignature($data);

        $ch = curl_init($this->apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "x-client-id: " . $this->clientId,
            "x-api-key: " . $this->apiKey
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);
        
        if (isset($result['code']) && $result['code'] == "00") {
            return $result['data']['checkoutUrl'];
        }

        return null;
    }

    /**
     * Tạo chữ ký bảo mật theo yêu cầu của PayOS
     */
    private function createSignature($data) {
        $dataToHash = [
            "amount" => $data['amount'],
            "cancelUrl" => $data['cancelUrl'],
            "description" => $data['description'],
            "orderCode" => $data['orderCode'],
            "returnUrl" => $data['returnUrl']
        ];
        
        ksort($dataToHash);
        $query = http_build_query($dataToHash);
        $query = str_replace(['%2F', '%3A', '%23', '%3F', '%3D', '%26'], ['/', ':', '#', '?', '=', '&'], $query);
        
        return hash_hmac('sha256', $query, $this->checksumKey);
    }

    /**
     * Xác minh dữ liệu Webhook từ PayOS
     */
    public function verifyWebhookData($webhookData) {
        $data = $webhookData['data'];
        $signature = $webhookData['signature'];
        
        ksort($data);
        $query = http_build_query($data);
        $query = str_replace(['%2F', '%3A', '%23', '%3F', '%3D', '%26'], ['/', ':', '#', '?', '=', '&'], $query);
        
        $expectedSignature = hash_hmac('sha256', $query, $this->checksumKey);
        
        return $signature === $expectedSignature;
    }
}
