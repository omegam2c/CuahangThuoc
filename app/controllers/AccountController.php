<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/UserModel.php';
require_once __DIR__ . '/../Models/OrderModel.php';
require_once __DIR__ . '/../Services/MailerService.php';
require_once __DIR__ . '/../Services/Emails/BaseEmail.php';
require_once __DIR__ . '/../Services/Emails/OrderCancelledEmail.php';

class AccountController extends BaseController {
    private $userModel;
    private $orderModel;

    public function __construct() {
        parent::__construct();
        if (!isset($_SESSION['user_id'])) {
            $this->redirect(BASE_URL . 'auth/login');
        }
        $this->userModel = new UserModel();
        $this->orderModel = new OrderModel();
        $this->mailer = new \App\Services\MailerService();
    }

    /**
     * Thông tin cá nhân
     */
    public function index() {
        $db = (new BaseModel())->db;
        $user = $db->query("SELECT * FROM users WHERE user_id = " . intval($_SESSION['user_id']))->fetch(PDO::FETCH_ASSOC);

        $this->loadView('user/profile', [
            'user' => $user,
            'pageTitle' => 'Thông tin tài khoản'
        ]);
    }

    /**
     * Cập nhật thông tin cá nhân (Ref point 🟡 - CSRF)
     */
    public function updateProfile() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf(); // CSRF Protection FIX

            $name = htmlspecialchars(trim($_POST['full_name'] ?? '')); // Sanitize
            $phone = trim($_POST['phone'] ?? '');
            $address = htmlspecialchars(trim($_POST['address'] ?? ''));
            $errors = [];

            if (empty($name)) $errors[] = "Họ tên không được để trống.";
            if (!empty($phone) && !preg_match('/^(0|84)(3|5|7|8|9)([0-9]{8})$/', $phone)) $errors[] = "Số điện thoại không hợp lệ.";

            if (empty($errors)) {
                $db = (new BaseModel())->db;
                $sql = "UPDATE users SET full_name = :name, phone = :phone, address = :address WHERE user_id = :id";
                $stmt = $db->prepare($sql);
                $stmt->execute(['name' => $name, 'phone' => $phone, 'address' => $address, 'id' => $_SESSION['user_id']]);
                
                // Audit Log
                require_once __DIR__ . '/../Models/ActivityLogModel.php';
                (new ActivityLogModel())->log($_SESSION['user_id'], 'update_profile', 'users', $_SESSION['user_id'], "Cập nhật thông tin cá nhân.");

                $_SESSION['full_name'] = $name;
                $_SESSION['success_message'] = "Cập nhật thông tin thành công!";
            } else {
                $_SESSION['error_message'] = implode("<br>", $errors);
            }
            $this->redirect(BASE_URL . 'account');
        }
    }

    /**
     * Lịch sử đơn hàng
     */
    public function orders() {
        $orders = $this->orderModel->getOrdersByUserId($_SESSION['user_id']);
        $this->loadView('user/orders', ['orders' => $orders, 'pageTitle' => 'Lịch sử đơn hàng']);
    }

    /**
     * Chi tiết đơn hàng
     */
    public function order_detail() {
        $orderId = $_GET['id'] ?? 0;
        $order = $this->orderModel->getOrderById($orderId);
        if (!$order || $order['user_id'] != $_SESSION['user_id']) $this->redirect(BASE_URL . 'account/orders');
        
        $db = (new BaseModel())->db;
        $details = $db->query("SELECT * FROM order_details WHERE order_id = " . intval($orderId))->fetchAll(PDO::FETCH_ASSOC);
        
        $this->loadView('user/order_detail', ['order' => $order, 'details' => $details, 'pageTitle' => 'Chi tiết đơn hàng #' . $orderId]);
    }

    /**
     * Hủy đơn hàng (Ref point 🟡 - Time Limit)
     */
    public function cancel_order() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();
            $orderId = $_POST['order_id'];
            
            $db = (new BaseModel())->db;
            // Kiểm tra thời hạn hủy (Chỉ cho phép hủy trong 24h đầu - Ref point 🟡)
            $sqlCheck = "SELECT *, (TIMESTAMPDIFF(HOUR, order_date, NOW()) < 24) as can_cancel FROM orders WHERE order_id = ? AND user_id = ?";
            $stmt = $db->prepare($sqlCheck);
            $stmt->execute([$orderId, $_SESSION['user_id']]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($order && $order['status'] == 'pending' && $order['can_cancel']) {
                try {
                    $db->beginTransaction();

                    // 1. Cập nhật trạng thái đơn hàng
                    $db->prepare("UPDATE orders SET status = 'cancelled' WHERE order_id = ?")->execute([$orderId]);
                    
                    // 2. Hoàn trả hàng về kho
                    $details = $db->query("SELECT * FROM order_details WHERE order_id = " . intval($orderId))->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($details as $od) {
                        $sqlReturn = "INSERT INTO inventory_transactions (product_id, batch_id, user_id, order_id, transaction_type, quantity, note) 
                                      VALUES (:pid, :bid, :uid, :oid, 'cancel_order', :qty, 'Khách hàng tự hủy đơn hàng')";
                        $db->prepare($sqlReturn)->execute([
                            'pid' => $od['product_id'],
                            'bid' => $od['batch_id'],
                            'uid' => $_SESSION['user_id'],
                            'oid' => $orderId,
                            'qty' => $od['quantity']
                        ]);
                    }

                    (new ActivityLogModel())->log($_SESSION['user_id'], 'cancel_order', 'orders', $orderId, "Khách hàng tự hủy đơn hàng trong hạn 24h.");

                    // Gửi email thông báo hủy đơn
                    $emailAddress = $_SESSION['user_email'] ?? $order['customer_email'] ?? null;
                    if ($emailAddress) {
                        $emailTemplate = new \App\Services\Emails\OrderCancelledEmail($order, "Khách hàng tự hủy đơn hàng.");
                        $this->mailer->dispatch($emailTemplate, $emailAddress, $order['shipping_name'], $orderId);
                    }

                    $db->commit();
                    $_SESSION['success_message'] = "Đã hủy đơn hàng thành công và hoàn lại kho.";
                } catch (Exception $e) {
                    if ($db->inTransaction()) $db->rollBack();
                    $_SESSION['error_message'] = "Lỗi khi hủy đơn: " . $e->getMessage();
                }
            } else {
                $_SESSION['error_message'] = "Không thể hủy đơn hàng này (Đơn đã quá 24h hoặc đang được xử lý).";
            }
            $this->redirect(BASE_URL . 'account/orders');
        }
    }

    /**
     * Đổi mật khẩu (Ref point 🟡 - Validation)
     */
    public function changePassword() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();
            $currentPassword = $_POST['current_password'];
            $newPassword = $_POST['new_password'];
            $confirmPassword = $_POST['confirm_password'];
            
            // Validation độ mạnh mật khẩu (Ref point 🟡)
            if ($newPassword !== $confirmPassword) {
                $_SESSION['error_message'] = "Mật khẩu xác nhận không khớp.";
                $this->redirect(BASE_URL . 'account'); return;
            }

            if (strlen($newPassword) < 8 || !preg_match('/[A-Z]/', $newPassword) || !preg_match('/[^a-zA-Z0-9]/', $newPassword)) {
                $_SESSION['error_message'] = "Mật khẩu phải từ 8 ký tự, có 1 chữ in hoa và 1 ký tự đặc biệt.";
                $this->redirect(BASE_URL . 'account'); return;
            }

            if ($currentPassword === $newPassword) {
                $_SESSION['error_message'] = "Mật khẩu mới không được trùng với mật khẩu cũ.";
                $this->redirect(BASE_URL . 'account'); return;
            }
            
            $db = (new BaseModel())->db;
            $user = $db->query("SELECT password_hash FROM users WHERE user_id = " . intval($_SESSION['user_id']))->fetch(PDO::FETCH_ASSOC);
            
            if (password_verify($currentPassword, $user['password_hash'])) {
                $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $db->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
                $stmt->execute([$newHash, $_SESSION['user_id']]);

                require_once __DIR__ . '/../Models/ActivityLogModel.php';
                (new ActivityLogModel())->log($_SESSION['user_id'], 'change_password', 'users', $_SESSION['user_id'], "Thay đổi mật khẩu tài khoản.");

                $_SESSION['success_message'] = "Đổi mật khẩu thành công!";
            } else {
                $_SESSION['error_message'] = "Mật khẩu hiện tại không đúng.";
            }
            $this->redirect(BASE_URL . 'account');
        }
    }
}
