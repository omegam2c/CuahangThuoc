<?php
require_once __DIR__ . '/../config/app_config.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/vendor/autoload.php';
try {
    $orderId = time();
    $totalAmount = 10000;
    
    $payOS = new \PayOS\PayOS(PAYOS_CLIENT_ID, PAYOS_API_KEY, PAYOS_CHECKSUM_KEY);
    $protocol = "http";
    $domain = "localhost:8000";
    $absoluteBase = $protocol . "://" . $domain . BASE_URL;
    
    $data = [
        "orderCode" => intval($orderId),
        "amount" => $totalAmount,
        "description" => "Thanh toan don " . $orderId,
        "returnUrl" => $absoluteBase . "order/success?id=" . $orderId,
        "cancelUrl" => $absoluteBase . "order/cancel"
    ];
    $response = $payOS->paymentRequests->create($data);
    print_r($response);
} catch (\Throwable $th) {
    echo "PayOS API Error: " . $th->getMessage() . "\n";
    if (method_exists($th, 'getResponse')) {
        echo "Response: " . print_r($th->getResponse(), true) . "\n";
    }
}
