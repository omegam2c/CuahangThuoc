<?php

class VNPayService {
    private $tmnCode;
    private $hashSecret;
    private $url;

    public function __construct() {
        $this->tmnCode = VNPAY_TMN_CODE;
        $this->hashSecret = VNPAY_HASH_SECRET;
        $this->url = VNPAY_URL;
    }

    public function createPaymentUrl($orderData) {
        $vnp_Url = $this->url;
        $vnp_Returnurl = VNPAY_RETURN_URL;
        $vnp_TmnCode = $this->tmnCode;
        $vnp_HashSecret = $this->hashSecret;

        $vnp_TxnRef = $orderData['order_id'];
        $vnp_OrderInfo = "Thanh toan don hang #" . $vnp_TxnRef;
        $vnp_OrderType = 'billpayment';
        $vnp_Amount = $orderData['amount'] * 100;
        $vnp_Locale = 'vn';
        $vnp_BankCode = ''; // Để trống để khách chọn tại VNPAY
        $vnp_IpAddr = $_SERVER['REMOTE_ADDR'];

        $inputData = array(
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => $vnp_OrderType,
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef" => $vnp_TxnRef
        );

        ksort($inputData);
        $query = "";
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $vnp_Url = $vnp_Url . "?" . $query;
        if (isset($vnp_HashSecret)) {
            $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }

        return $vnp_Url;
    }
}
