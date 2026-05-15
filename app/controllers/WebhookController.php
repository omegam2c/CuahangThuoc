<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/OrderModel.php';
require_once __DIR__ . '/../Services/MoMoService.php';

class WebhookController extends BaseController {
    private $orderModel;
    private $momoService;

    public function __construct() {
        $this->orderModel = new OrderModel();
        $this->momoService = new MoMoService();
    }


    /**
     * Handle MoMo IPN
     */
    /**
     * Handle MoMo IPN (Ref point 🔗 - Payment Integration)
     */
    public function momo() {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) return;

        // 1. Verify Signature (Simulated - Cần dùng secret key thực tế)
        // signature = hmac_sha256(accessKey=$accessKey&amount=$amount&extraData=$extraData&message=$message&orderId=$orderId&orderInfo=$orderInfo&orderType=$orderType&partnerCode=$partnerCode&payType=$payType&requestId=$requestId&responseTime=$responseTime&resultCode=$resultCode&transId=$transId)
        
        $orderId = $data['orderId'] ?? null;
        if (!$orderId) return;

        // 2. Idempotency Check: Kiểm tra trạng thái hiện tại (Tránh xử lý 2 lần)
        $order = $this->orderModel->getOrderById($orderId);
        if (!$order || $order['payment_status'] === 'paid') {
            header("HTTP/1.1 200 OK"); // Trả về OK để MoMo ngừng retry
            return;
        }

        $resultCode = $data['resultCode'] ?? -1;
        if ($resultCode == 0) {
            $this->orderModel->updatePaymentStatus($orderId, 'paid');
            
            // Ghi Audit Log cho thanh toán
            require_once __DIR__ . '/../Models/ActivityLogModel.php';
            (new ActivityLogModel())->log($order['user_id'] ?? 0, 'payment_success', 'orders', $orderId, "Thanh toán thành công qua MoMo.");
        }

        // MoMo yêu cầu trả về HTTP 204 hoặc JSON trống
        header("HTTP/1.1 204 No Content");
    }
    /**
     * Handle SePay Webhook (Ref point 🔴 - Webhook Security)
     */
    public function sepay() {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        // 1. Xác thực API Key (Ref point 🔴 - Webhook Signature)
        $apiKey = env('SEPAY_API_KEY');
        if (empty($apiKey) || strpos($authHeader, $apiKey) === false) {
            header('HTTP/1.1 401 Unauthorized');
            echo json_encode(['success' => false, 'message' => 'Xác thực Webhook thất bại']);
            return;
        }

        // 2. Di chuyển Log ra khỏi thư mục công khai (Ref point 🟡 - Public Log)
        $logDir = __DIR__ . '/../../storage/logs';
        if (!is_dir($logDir)) mkdir($logDir, 0755, true);
        
        $logFile = $logDir . '/sepay_webhook.log';
        $logData = date('Y-m-d H:i:s') . " - Data: " . json_encode($data) . "\n";
        file_put_contents($logFile, $logData, FILE_APPEND);

        if (!$data) return;

        $content = $data['content'] ?? ''; 
        $amount = $data['transferAmount'] ?? 0;

        // Tìm mã đơn hàng (DH123 hoặc chỉ 123 nếu nội dung chỉ có số)
        $orderId = null;
        if (preg_match('/DH(\d+)/i', $content, $matches)) {
            $orderId = $matches[1];
        } elseif (is_numeric(trim($content))) {
            $orderId = trim($content);
        }

        if ($orderId) {
            $order = $this->orderModel->getOrderById($orderId);
            if ($order && ($order['payment_status'] ?? '') !== 'paid') {
                $this->orderModel->updatePaymentStatus($orderId, 'paid');
                error_log("SePay: Đơn hàng #$orderId đã được cập nhật thành công.");
            }
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
    }

    /**
     * Handle PayOS Webhook
     */
    public function payos() {
        $payload = file_get_contents('php://input');
        $webhookData = json_decode($payload, true);
        
        if (!$webhookData) {
            header('HTTP/1.1 400 Bad Request');
            echo json_encode(["error" => 1, "message" => "Invalid JSON payload"]);
            return;
        }

        require_once __DIR__ . '/../../vendor/autoload.php';
        
        try {
            $payOS = new \PayOS\PayOS(PAYOS_CLIENT_ID, PAYOS_API_KEY, PAYOS_CHECKSUM_KEY);
            $verifiedData = $payOS->verifyPaymentWebhookData($webhookData);
            
            if (!$verifiedData) {
                throw new \Exception("Chữ ký webhook không hợp lệ.");
            }

            $orderCode = $verifiedData['orderCode'];
            
            $order = $this->orderModel->getOrderById($orderCode);
            if ($order && ($order['payment_status'] ?? '') !== 'paid') {
                $this->orderModel->updatePaymentStatus($orderCode, 'paid');
                error_log("PayOS Webhook: Đơn hàng #$orderCode đã được thanh toán thành công.");
            }

            header('Content-Type: application/json');
            echo json_encode(["error" => 0, "message" => "Ok", "data" => null]);

        } catch (\Exception $e) {
            error_log("PayOS Webhook Error: " . $e->getMessage());
            header('HTTP/1.1 403 Forbidden');
            echo json_encode(["error" => 1, "message" => $e->getMessage(), "data" => null]);
        }
    }
}
