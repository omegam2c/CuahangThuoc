<?php

require_once __DIR__ . '/../BaseController.php';
require_once __DIR__ . '/../../Models/OrderModel.php';
require_once __DIR__ . '/../../Services/MailerService.php';
require_once __DIR__ . '/../../Services/Emails/BaseEmail.php';
require_once __DIR__ . '/../../Services/Emails/OrderCompletedEmail.php';

use App\Services\MailerService;
use App\Services\Emails\OrderCompletedEmail;
use App\Services\Emails\OrderCancelledEmail;

class AdminOrderController extends BaseController {
    private $orderModel;
    private $mailer;
    
    public function __construct() {
        parent::__construct();
        if (!isset($_SESSION['user_id'])) {
            $this->redirect(BASE_URL . 'auth/login');
        }
        
        $this->orderModel = new OrderModel();
        $this->mailer = new MailerService();
    }
    
    /**
     * Danh sách tất cả đơn hàng
     */
    public function index() {
        $this->checkPermission('process_orders'); // Ref point 🔴 - Permission System
        
        $db = (new BaseModel())->db;
        
        $q = $_GET['q'] ?? '';
        $status = $_GET['status'] ?? '';
        
        $where = ["1=1"];
        $params = [];
        
        // Note: Dược sĩ mặc định chỉ được xem các đơn hàng có toa thuốc (Rx)
        if ($_SESSION['role_id'] == 2) {
            $where[] = "o.has_prescription = 1";
        }
        
        if ($q !== '') {
            $where[] = "(o.order_id = :qid OR u.full_name LIKE :q OR o.shipping_name LIKE :q OR o.shipping_phone LIKE :q)";
            $params['qid'] = $q;
            $params['q'] = "%$q%";
        }
        
        if ($status !== '') {
            $where[] = "o.status = :status";
            $params['status'] = $status;
        }
        
        $whereSql = implode(' AND ', $where);
        
        $sql = "SELECT o.*, 
                       IFNULL(u.full_name, o.shipping_name) as customer_name,
                       IFNULL(u.email, o.customer_email) as customer_email
                FROM orders o 
                LEFT JOIN users u ON o.user_id = u.user_id 
                WHERE $whereSql
                ORDER BY o.order_date DESC";
                 
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->loadView('admin/order/index', [
            'orders' => $orders,
            'pageTitle' => 'Quản lý đơn hàng',
            'q' => $q,
            'status' => $status
        ]);
    }
    
    /**
     * Chi tiết đơn hàng
     */
    public function detail() {
        $this->checkPermission('process_orders');
        
        $id = $_GET['id'] ?? 0;
        $db = (new BaseModel())->db;
        
        $order = $db->query("SELECT o.*, 
                                    IFNULL(u.full_name, o.shipping_name) as customer_name, 
                                    IFNULL(u.email, o.customer_email) as customer_email 
                            FROM orders o 
                            LEFT JOIN users u ON o.user_id = u.user_id 
                            WHERE o.order_id = " . intval($id))->fetch(PDO::FETCH_ASSOC);
                            
        if (!$order) {
            $this->redirect(BASE_URL . 'admin/order');
        }

        // Object-level permission (Pharmacist only sees Rx orders)
        if ($_SESSION['role_id'] == 2 && !$order['has_prescription']) {
            $_SESSION['error_message'] = "Bạn không có quyền truy cập đơn hàng này.";
            $this->redirect(BASE_URL . 'admin/order');
        }
        
        $details = $db->query("SELECT od.*, b.batch_number 
                              FROM order_details od 
                              LEFT JOIN batches b ON od.batch_id = b.batch_id 
                              WHERE od.order_id = " . intval($id))->fetchAll(PDO::FETCH_ASSOC);
                              
        $this->loadView('admin/order/detail', [
            'order' => $order,
            'details' => $details,
            'pageTitle' => 'Chi tiết đơn hàng #' . $order['order_id']
        ]);
    }
    
    /**
     * Cập nhật trạng thái đơn hàng
     */
    /**
     * Cập nhật trạng thái đơn hàng (Ref point 📊 - Audit Trail)
     */
    public function updateStatus() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();
            $orderId = $_POST['order_id'];
            $status = $_POST['status'];

            // Quyền: Dược sĩ chỉ xử lý đơn có toa, các role khác cần quyền process_orders
            if ($_SESSION['role_id'] == 2) {
                $order = $this->orderModel->getOrderById($orderId);
                if (!$order || !$order['has_prescription']) {
                    $this->denyAccess();
                }
            } else {
                $this->checkPermission('process_orders');
            }

            
            $db = (new BaseModel())->db;
            
            // Lấy trạng thái cũ để Log
            $oldStatus = $db->query("SELECT status FROM orders WHERE order_id = " . intval($orderId))->fetchColumn();

            $sql = "UPDATE orders SET status = :status";
            if ($status === 'completed') {
                $sql .= ", payment_status = 'paid'";
            }
            $sql .= " WHERE order_id = :id";
            
            $stmt = $db->prepare($sql);
            $stmt->execute(['status' => $status, 'id' => $orderId]);

            // Audit Log (Ref point 3 - Audit Trail)
            require_once __DIR__ . '/../../Models/ActivityLogModel.php';
            (new ActivityLogModel())->log($_SESSION['user_id'], 'update_order_status', 'orders', $orderId, 
                "Thay đổi trạng thái đơn hàng: $oldStatus -> $status");

            // Gửi mail nếu đơn hàng hoàn thành
            if ($status === 'completed') {
                $order = $this->orderModel->getOrderById($orderId);
                $emailAddress = $order['customer_email'] ?? null;
                if ($emailAddress) {
                    $emailTemplate = new OrderCompletedEmail($order);
                    $this->mailer->dispatch($emailTemplate, $emailAddress, $order['shipping_name'], $orderId);
                }
            }
            
            $this->redirect(BASE_URL . 'admin/order/detail?id=' . $orderId);
        }
    }

    /**
     * Hủy đơn hàng (Admin/Pharmacist)
     */
    public function cancelOrder() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();
            $orderId = $_POST['order_id'];
            $reason = $_POST['cancel_reason'] ?? 'Không rõ lý do';

            // Quyền: Dược sĩ chỉ xử lý đơn có toa, các role khác cần quyền process_orders
            if ($_SESSION['role_id'] == 2) {
                $order = $this->orderModel->getOrderById($orderId);
                if (!$order || !$order['has_prescription']) {
                    $this->denyAccess();
                }
            } else {
                $this->checkPermission('process_orders');
            }
            

            $db = (new BaseModel())->db;
            
            try {
                $db->beginTransaction();
                
                // 1. Cập nhật trạng thái đơn hàng (Sửa chính tả: cancelled)
                $db->prepare("UPDATE orders SET status = 'cancelled', cancel_reason = ? WHERE order_id = ?")->execute([$reason, $orderId]);

                // 2. Hoàn trả hàng về kho
                $details = $db->query("SELECT * FROM order_details WHERE order_id = " . intval($orderId))->fetchAll(PDO::FETCH_ASSOC);
                foreach ($details as $od) {
                    $sqlReturn = "INSERT INTO inventory_transactions (product_id, batch_id, user_id, order_id, transaction_type, quantity, note) 
                                  VALUES (:pid, :bid, :uid, :oid, 'cancel_order', :qty, 'Hủy đơn hàng: $reason')";
                    $db->prepare($sqlReturn)->execute([
                        'pid' => $od['product_id'],
                        'bid' => $od['batch_id'],
                        'uid' => $_SESSION['user_id'],
                        'oid' => $orderId,
                        'qty' => $od['quantity']
                    ]);
                }

                // Audit Log
                require_once __DIR__ . '/../../Models/ActivityLogModel.php';
                (new ActivityLogModel())->log($_SESSION['user_id'], 'cancel_order', 'orders', $orderId, "Hủy đơn hàng. Lý do: $reason");

                // Gửi email thông báo hủy đơn
                $order = $this->orderModel->getOrderById($orderId);
                $emailAddress = $order['customer_email'] ?? null;
                if ($emailAddress) {
                    require_once __DIR__ . '/../../Services/Emails/OrderCancelledEmail.php';
                    $emailTemplate = new OrderCancelledEmail($order, $reason);
                    $this->mailer->dispatch($emailTemplate, $emailAddress, $order['shipping_name'], $orderId);
                }

                $db->commit();
                $_SESSION['success_message'] = "Đã hủy đơn hàng #$orderId và hoàn trả kho.";
            } catch (Exception $e) {
                if ($db->inTransaction()) $db->rollBack();
                $_SESSION['error_message'] = "Lỗi khi hủy đơn: " . $e->getMessage();
            }

            $this->redirect(BASE_URL . 'admin/order');
        }
    }
    
    /**
     * Xác nhận đơn thuốc (Chỉ Dược sĩ được quyền)
     */
    public function verifyPrescription() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();
            $this->checkPermission('approve_prescriptions');
            $orderId = $_POST['order_id'];
            
            $db = (new BaseModel())->db;
            $sql = "UPDATE orders SET prescription_verified = 1, verified_by = :user_id, verified_at = NOW() WHERE order_id = :id";
            $stmt = $db->prepare($sql);
            $stmt->execute(['user_id' => $_SESSION['user_id'], 'id' => $orderId]);
            
            // Audit Log
            require_once __DIR__ . '/../../Models/ActivityLogModel.php';
            (new ActivityLogModel())->log($_SESSION['user_id'], 'verify_prescription', 'orders', $orderId, "Đã phê duyệt chuyên môn cho đơn thuốc.");

            // Gửi email xác nhận sau khi duyệt thành công
            $orderData = $this->orderModel->getOrderById($orderId);
            $orderDetails = $this->orderModel->getOrderDetails($orderId);
            $emailAddress = $orderData['customer_email'] ?? null;
            if ($emailAddress) {
                require_once __DIR__ . '/../../Services/Emails/OrderConfirmationEmail.php';
                $emailTemplate = new \App\Services\Emails\OrderConfirmationEmail($orderData, $orderDetails);
                $this->mailer->dispatch($emailTemplate, $emailAddress, $orderData['shipping_name'], $orderId);
            }

            $_SESSION['success_message'] = "Đã xác nhận đơn thuốc.";
            $this->redirect(BASE_URL . 'admin/order/detail?id=' . $orderId);
        }
    }
}
