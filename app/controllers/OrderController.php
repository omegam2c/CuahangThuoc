<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/OrderModel.php';
require_once __DIR__ . '/../Models/CartModel.php';
require_once __DIR__ . '/../Models/UserModel.php';
require_once __DIR__ . '/../Services/MailerService.php';
require_once __DIR__ . '/../Services/Emails/BaseEmail.php';
require_once __DIR__ . '/../Services/Emails/OrderConfirmationEmail.php';
require_once __DIR__ . '/../Services/Emails/PaymentSuccessEmail.php';
require_once __DIR__ . '/../Services/Emails/OrderCompletedEmail.php';

use App\Services\MailerService;
use App\Services\Emails\OrderConfirmationEmail;
use App\Services\Emails\PaymentSuccessEmail;
use App\Services\Emails\OrderCompletedEmail;

class OrderController extends BaseController {
    private $orderModel;
    private $cartModel;
    private $userModel;
    private $mailer;
    
    public function __construct() {
        parent::__construct();
        $this->orderModel = new OrderModel();
        $this->cartModel = new CartModel();
        $this->userModel = new UserModel();
        $this->mailer = new MailerService();
    }
    
    /**
     * Trang checkout
     */
    public function checkout() {
        $userId = $_SESSION['user_id'] ?? null;
        $cartId = null;
        
        if ($userId) {
            $cartId = $this->cartModel->getCartByUserId($userId, session_id());
        } else {
            $cartId = $this->cartModel->getCartBySessionId(session_id());
        }

        $cartItems = $this->cartModel->getCartItems($cartId);
        
        if (empty($cartItems)) {
            $this->redirect(BASE_URL . 'cart');
        }
        
        $user = $userId ? $this->userModel->getUserById($userId) : null;
        
        // Lấy dữ liệu cũ nếu có lỗi validate trước đó
        $oldInput = $_SESSION['old_checkout_input'] ?? [];
        unset($_SESSION['old_checkout_input']);
        
        // Nếu không có oldInput và không có data user, thử lấy từ Cookie (cho khách vãng lai đã từng mua hàng)
        if (empty($oldInput) && empty($user['address'])) {
            $savedInfo = isset($_COOKIE['last_shipping_info']) ? json_decode($_COOKIE['last_shipping_info'], true) : [];
            if (!empty($savedInfo)) {
                $oldInput = array_merge($oldInput, $savedInfo);
            }
        }
        
        $total = 0;
        $hasRx = false;
        foreach ($cartItems as $item) {
            $total += $item['current_price'] * $item['quantity'];
            if (isset($item['is_prescription_required']) && ($item['is_prescription_required'] == 1 || $item['is_prescription_required'] === true)) {
                $hasRx = true;
            }
        }
        
        $this->loadView('cart/checkout', [
            'cartItems' => $cartItems,
            'user' => $user,
            'oldInput' => $oldInput,
            'total' => $total,
            'hasRx' => $hasRx,
            'pageTitle' => 'Thanh toán - Nhà thuốc 1985'
        ]);
    }
    
    /**
     * Xử lý đặt hàng
     */
    public function placeOrder() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/');
        }
        
        $userId = $_SESSION['user_id'] ?? null;
        $note = $_POST['note'] ?? '';

        $fullName = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $paymentMethod = $_POST['payment_method'] ?? 'cod';
        $errors = [];

        if (empty($fullName) || strlen($fullName) < 2) {
            $errors[] = "Vui lòng nhập họ tên hợp lệ (tối thiểu 2 ký tự).";
        }

        if (empty($phone) || !preg_match('/^(0|84)(3|5|7|8|9)([0-9]{8})$/', $phone)) {
            $errors[] = "Số điện thoại không hợp lệ. Vui lòng nhập đúng định dạng (VD: 0912345678).";
        }

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Email không hợp lệ. Vui lòng nhập đúng định dạng (VD: nva@gmail.com).";
        }

        if (empty($address) || strlen($address) < 10) {
            $errors[] = "Địa chỉ nhận hàng quá ngắn. Vui lòng nhập đầy đủ số nhà, tên đường, phường/xã.";
        }

        if (!empty($errors)) {
            $_SESSION['error_message'] = implode("<br>", $errors);
            $_SESSION['old_checkout_input'] = $_POST;
            $this->redirect(BASE_URL . 'cart/checkout');
        }

        $shippingData = [
            'name' => $fullName,
            'phone' => $phone,
            'address' => $address,
            'note' => $note,
            'payment_method' => $paymentMethod
        ];
        
        if ($userId) {
            $cartId = $this->cartModel->getCartByUserId($userId, session_id());
        } else {
            $cartId = $this->cartModel->getCartBySessionId(session_id());
        }
        $cartItems = $this->cartModel->getCartItems($cartId);
        
        $hasRx = false;
        foreach ($cartItems as $item) {
            if (isset($item['is_prescription_required']) && ($item['is_prescription_required'] == 1 || $item['is_prescription_required'] === true)) {
                $hasRx = true;
                break;
            }
        }

        // Xử lý upload đơn thuốc nếu cần
        $prescriptionImage = null;
        if ($hasRx) {
            if (isset($_FILES['prescription']) && $_FILES['prescription']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../../app/storage/prescriptions/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                
                $fileExtension = pathinfo($_FILES['prescription']['name'], PATHINFO_EXTENSION);
                $prescriptionImage = 'rx_' . time() . '_' . ($userId ?? 'guest') . '.' . $fileExtension;
                
                if (!move_uploaded_file($_FILES['prescription']['tmp_name'], $uploadDir . $prescriptionImage)) {
                    $_SESSION['error_message'] = "Không thể tải lên ảnh đơn thuốc.";
                    $this->redirect(BASE_URL . 'cart/checkout');
                }
            } else {
                $_SESSION['error_message'] = "Đơn hàng chứa thuốc kê đơn. Vui lòng tải lên ảnh đơn thuốc của bác sĩ.";
                $this->redirect(BASE_URL . 'cart/checkout');
            }
        }

        $shippingData = [
            'name' => $fullName,
            'phone' => $phone,
            'email' => $email,
            'address' => $address,
            'note' => $note,
            'payment_method' => $_POST['payment_method'] ?? 'cod',
            'has_prescription' => $hasRx,
            'prescription_image' => $prescriptionImage
        ];
        
        // Lưu thông tin giao hàng vào Cookie để nhớ cho lần sau (30 ngày)
        $cookieData = json_encode([
            'full_name' => $fullName,
            'phone' => $phone,
            'email' => $email,
            'address' => $address
        ], JSON_UNESCAPED_UNICODE);
        setcookie('last_shipping_info', $cookieData, time() + (30 * 24 * 60 * 60), '/');

        $orderId = $this->orderModel->createOrder($userId, $shippingData, $cartItems, $cartId);
        
        if ($orderId) {
            // Gửi email xác nhận đơn hàng - Chỉ gửi ngay cho đơn hàng không có thuốc kê đơn
            // Đơn hàng có thuốc kê đơn sẽ được gửi sau khi Dược sĩ duyệt chuyên môn
            $emailAddress = $email; 
            if ($emailAddress && !$hasRx) {
                $orderData = $this->orderModel->getOrderById($orderId);
                $orderDetails = $this->orderModel->getOrderDetails($orderId);
                $emailTemplate = new OrderConfirmationEmail($orderData, $orderDetails);
                $this->mailer->dispatch($emailTemplate, $emailAddress, $shippingData['name'], $orderId);
            }

            $paymentMethod = $_POST['payment_method'] ?? 'cod';
            
            if ($paymentMethod === 'payos') {
                $order = $this->orderModel->getOrderById($orderId);
                $totalAmount = (int)$order['total_amount'];
                
                require_once __DIR__ . '/../../vendor/autoload.php';
                try {
                    $payOS = new \PayOS\PayOS(PAYOS_CLIENT_ID, PAYOS_API_KEY, PAYOS_CHECKSUM_KEY);
                    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
                    $domain = $_SERVER['HTTP_HOST'];
                    $absoluteBase = $protocol . "://" . $domain . BASE_URL;
                    
                    $data = [
                        "orderCode" => intval($orderId),
                        "amount" => $totalAmount,
                        "description" => "Thanh toan don " . $orderId,
                        "returnUrl" => $absoluteBase . "order/success?id=" . $orderId,
                        "cancelUrl" => $absoluteBase . "order/cancel"
                    ];
                    $response = $payOS->createPaymentLink($data);
                    $this->redirect($response['checkoutUrl']);
                    return;
                } catch (\Throwable $th) {
                    error_log("PayOS Error: " . $th->getMessage());
                    $_SESSION['error_message'] = "Lỗi PayOS: " . $th->getMessage() . ". Vui lòng kiểm tra lại API Key.";
                    $this->redirect(BASE_URL . 'cart/checkout');
                    return;
                }
            }

            $_SESSION['success_message'] = "Đặt hàng thành công! Mã đơn hàng của bạn là #" . $orderId;
            $this->redirect(BASE_URL . 'order/success?id=' . $orderId);
        } else {
            $_SESSION['error_message'] = $_SESSION['checkout_error'] ?? "Có lỗi xảy ra trong quá trình đặt hàng hoặc hàng trong kho không đủ.";
            unset($_SESSION['checkout_error']);
            $this->redirect(BASE_URL . 'cart/checkout');
        }
    }

    public function success() {
        $orderId = $_GET['id'] ?? null;
        $order = null;
        
        if ($orderId) {
            $order = $this->orderModel->getOrderById($orderId);
            
            // Nếu là thanh toán PayOS, kiểm tra trạng thái thực tế từ API
            if ($order && $order['payment_method'] === 'payos' && $order['payment_status'] !== 'paid') {
                require_once __DIR__ . '/../../vendor/autoload.php';
                try {
                    $payOS = new \PayOS\PayOS(PAYOS_CLIENT_ID, PAYOS_API_KEY, PAYOS_CHECKSUM_KEY);
                    $paymentInfo = $payOS->getPaymentLinkInformation($orderId);
                    if ($paymentInfo['status'] === 'PAID') {
                        $this->orderModel->updatePaymentStatus($orderId, 'paid');
                        $order['payment_status'] = 'paid'; // Cập nhật để hiển thị ngay
                        
                        // Gửi email xác nhận thanh toán thành công
                        $emailAddress = $_SESSION['user_email'] ?? $order['customer_email'] ?? null;
                        if ($emailAddress) {
                            $emailTemplate = new PaymentSuccessEmail($order);
                            $this->mailer->dispatch($emailTemplate, $emailAddress, $order['shipping_name'], $orderId);
                        }
                    }
                } catch (\Throwable $th) {
                    error_log("PayOS Sync Error: " . $th->getMessage());
                }
            }

            if ($order && isset($_SESSION['user_id']) && $order['user_id'] != $_SESSION['user_id']) {
                $order = null;
            }
        }
        
        $this->loadView('order/success', [
            'orderId' => $orderId,
            'order' => $order,
            'pageTitle' => 'Đặt hàng thành công - Nhà thuốc 1985'
        ]);
    }

    /**
     * Hủy thanh toán
     */
    public function cancel() {
        $_SESSION['error_message'] = "Thanh toán đã bị hủy.";
        $this->redirect(BASE_URL . 'cart/checkout');
    }
}
