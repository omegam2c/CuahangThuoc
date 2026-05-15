<?php

class MoMoService {
    private $partnerCode;
    private $accessKey;
    private $secretKey;
    private $endpoint;

    public function __construct() {
        $this->partnerCode = MOMO_PARTNER_CODE;
        $this->accessKey = MOMO_ACCESS_KEY;
        $this->secretKey = MOMO_SECRET_KEY;
        $this->endpoint = MOMO_ENDPOINT;
    }

    public function createPayment($orderData) {
        $orderId = (string)$orderData['order_id'];
        $amount = (string)$orderData['amount'];
        $orderInfo = "Thanh toan don hang " . $orderId;
        $requestId = $orderId; 
        $redirectUrl = MOMO_RETURN_URL;
        $ipnUrl = MOMO_NOTIFY_URL;
        $extraData = "";
        $requestType = "captureWallet";

        // Xây dựng chuỗi thô để tạo chữ ký (Signature) - Thứ tự cực kỳ quan trọng
        $rawHash = "accessKey=" . $this->accessKey . 
                   "&amount=" . $amount . 
                   "&extraData=" . $extraData . 
                   "&ipnUrl=" . $ipnUrl . 
                   "&orderId=" . $orderId . 
                   "&orderInfo=" . $orderInfo . 
                   "&partnerCode=" . $this->partnerCode . 
                   "&redirectUrl=" . $redirectUrl . 
                   "&requestId=" . $requestId . 
                   "&requestType=" . $requestType;

        $signature = hash_hmac("sha256", $rawHash, $this->secretKey);

        $data = array(
            'partnerCode' => $this->partnerCode,
            'partnerName' => "MoMo",
            'storeId' => $this->partnerCode,
            'requestId' => $requestId,
            'amount' => $amount,
            'orderId' => $orderId,
            'orderInfo' => $orderInfo,
            'redirectUrl' => $redirectUrl,
            'ipnUrl' => $ipnUrl,
            'lang' => 'vi',
            'extraData' => $extraData,
            'requestType' => $requestType,
            'signature' => $signature
        );

        $ch = curl_init($this->endpoint);
        $payload = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($payload)
        ));

        $result = curl_exec($ch);
        curl_close($ch);

        return json_decode($result, true);
    }
}
