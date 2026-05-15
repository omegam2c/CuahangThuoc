<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/OrderModel.php';
require_once __DIR__ . '/../Models/ActivityLogModel.php';
require_once __DIR__ . '/../Services/MailerService.php';
require_once __DIR__ . '/../Services/Emails/BaseEmail.php';
require_once __DIR__ . '/../Services/Emails/OrderConfirmationEmail.php';
require_once __DIR__ . '/../Services/Emails/OrderCancelledEmail.php';

use App\Services\MailerService;
use App\Services\Emails\OrderConfirmationEmail;
use App\Services\Emails\OrderCancelledEmail;

class PharmacistController extends BaseController
{
    private $orderModel;
    private $mailer;

    public function __construct()
    {
        parent::__construct();
        if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 2) { // Role 2 is Pharmacist
            $this->redirect(BASE_URL . 'auth/login');
        }
        $this->orderModel = new OrderModel();
        $this->mailer = new MailerService();
    }
    public function dashboard()
    {
        $db = (new BaseModel())->db;
        
        // 1. Tìm phiên chat gần nhất của bác sĩ này
        $stmtConvo = $db->prepare("
            SELECT c.id, u.full_name as customer_name 
            FROM conversations c
            JOIN users u ON c.customer_id = u.user_id
            WHERE c.doctor_id = ? 
            ORDER BY c.created_at DESC 
            LIMIT 1
        ");
        $stmtConvo->execute([$_SESSION['user_id']]);
        $lastConvo = $stmtConvo->fetch(PDO::FETCH_ASSOC);

        // 2. Lấy danh sách yêu cầu tư vấn đang chờ
        $stmtPending = $db->prepare("
            SELECT r.id, u.full_name as customer_name, r.created_at
            FROM consultation_requests r
            JOIN users u ON r.customer_id = u.user_id
            WHERE r.current_doctor_id = ? AND r.status = 'pending'
            ORDER BY r.created_at DESC
        ");
        $stmtPending->execute([$_SESSION['user_id']]);
        $pendingRequests = $stmtPending->fetchAll(PDO::FETCH_ASSOC);

        $this->loadView('doctor/dashboard', [
            'pageTitle' => 'Tư vấn trực tuyến',
            'lastConvo' => $lastConvo,
            'pendingRequests' => $pendingRequests
        ]);
    }

    /**
     * Danh sách đơn hàng cần duyệt đơn thuốc
     */
    public function pendingPrescriptions()
    {
        // Lấy các đơn hàng có ảnh toa thuốc, ưu tiên đơn chưa duyệt
        // Sử dụng LEFT JOIN để lấy cả khách vãng lai (user_id = NULL)
        $sql = "SELECT o.*, 
                       IFNULL(u.full_name, o.shipping_name) as customer_name, 
                       IFNULL(u.phone, o.shipping_phone) as customer_phone 
                FROM orders o 
                LEFT JOIN users u ON o.user_id = u.user_id 
                WHERE o.has_prescription = 1 
                ORDER BY o.prescription_verified ASC, o.order_id DESC 
                LIMIT 50";
        $db = (new BaseModel())->db;
        $orders = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        // Lấy chi tiết sản phẩm cho từng đơn hàng
        foreach ($orders as &$o) {
            $detailsSql = "SELECT od.*, p.unit 
                          FROM order_details od 
                          LEFT JOIN products p ON od.product_id = p.product_id 
                          WHERE od.order_id = " . intval($o['order_id']);
            $o['items'] = $db->query($detailsSql)->fetchAll(PDO::FETCH_ASSOC);
        }

        $this->loadView('doctor/prescriptions', [
            'orders' => $orders,
            'pageTitle' => 'Duyệt đơn thuốc - Dược sĩ'
        ]);
    }

    /**
     * Duyệt đơn thuốc
     */
    public function verify()
    {
        $orderId = $_POST['order_id'] ?? null;
        $status = $_POST['status'] ?? 'approved';

        if ($orderId) {
            $db = (new BaseModel())->db;
            if ($status == 'approved') {
                $sql = "UPDATE orders SET prescription_verified = TRUE, verified_by = :user_id, verified_at = NOW(), status = 'confirmed' 
                        WHERE order_id = :order_id";
                $stmt = $db->prepare($sql);
                $stmt->execute(['user_id' => $_SESSION['user_id'], 'order_id' => $orderId]);

                // Gửi email xác nhận sau khi duyệt thành công
                $orderData = $this->orderModel->getOrderById($orderId);
                $orderDetails = $this->orderModel->getOrderDetails($orderId);
                $emailAddress = $orderData['customer_email'] ?? null;
                if ($emailAddress) {
                    $emailTemplate = new OrderConfirmationEmail($orderData, $orderDetails);
                    $this->mailer->dispatch($emailTemplate, $emailAddress, $orderData['shipping_name'], $orderId);
                }

                // Log hành động
                (new ActivityLogModel())->log($_SESSION['user_id'], 'verify_prescription', 'orders', $orderId, "Phê duyệt đơn thuốc cho đơn hàng #$orderId.");
            } else {
                try {
                    $db->beginTransaction();

                    // Hoàn lại kho nếu đơn này đã được kê đơn trước đó
                    $oldDetails = $db->query("SELECT * FROM order_details WHERE order_id = " . intval($orderId))->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($oldDetails as $od) {
                        $sqlReturn = "INSERT INTO inventory_transactions (product_id, batch_id, user_id, order_id, transaction_type, quantity, note) 
                                      VALUES (:pid, :bid, :uid, :oid, 'cancel_order', :qty, 'Từ chối đơn thuốc: Kho hàng được hoàn lại')";
                        $db->prepare($sqlReturn)->execute([
                            'pid' => $od['product_id'],
                            'bid' => $od['batch_id'],
                            'uid' => $_SESSION['user_id'],
                            'oid' => $orderId,
                            'qty' => $od['quantity']
                        ]);
                    }

                    $sql = "UPDATE orders SET status = 'cancelled', admin_note = 'Đơn thuốc không hợp lệ' WHERE order_id = :order_id";
                    $stmt = $db->prepare($sql);
                    $stmt->execute(['order_id' => $orderId]);

                    // Gửi email thông báo từ chối đơn thuốc
                    $orderData = $this->orderModel->getOrderById($orderId);
                    $emailAddress = $orderData['customer_email'] ?? null;
                    if ($emailAddress) {
                        $emailTemplate = new OrderCancelledEmail($orderData, "Đơn thuốc không hợp lệ hoặc không đủ điều kiện phê duyệt chuyên môn.");
                        $this->mailer->dispatch($emailTemplate, $emailAddress, $orderData['shipping_name'], $orderId);
                    }

                    $db->commit();
                    
                    // Log hành động
                    (new ActivityLogModel())->log($_SESSION['user_id'], 'reject_prescription', 'orders', $orderId, "Từ chối đơn thuốc cho đơn hàng #$orderId.");
                } catch (Exception $e) {
                    if ($db->inTransaction()) $db->rollBack();
                    error_log("Lỗi khi từ chối đơn thuốc: " . $e->getMessage());
                }
            }
        }

        $this->redirect(BASE_URL . 'doctor/prescriptions');
    }

    /**
     * Viết đơn thuốc online (UC-M03)
     */
    public function createPrescription()
    {
        $customerId = $_GET['user_id'] ?? null;
        $orderId = $_GET['order_id'] ?? null;

        $db = (new BaseModel())->db;
        $products = $db->query("SELECT product_id, product_name, price, unit FROM products WHERE is_active = 1")->fetchAll(PDO::FETCH_ASSOC);

        $customer = null;
        
        // 1. Nếu có order_id, lấy thông tin từ đơn hàng (Chính xác nhất cho khách vãng lai)
        if ($orderId) {
            $order = $db->query("SELECT user_id, shipping_name as full_name, shipping_phone as phone, customer_email as email, shipping_address as address FROM orders WHERE order_id = " . intval($orderId))->fetch(PDO::FETCH_ASSOC);
            if ($order) {
                $customer = $order;
            }
        }
        
        // 2. Nếu không có thông tin từ đơn hàng nhưng có user_id, lấy từ profile
        if (!$customer && $customerId) {
            $customer = $db->query("SELECT user_id, full_name, phone, email, address FROM users WHERE user_id = " . intval($customerId))->fetch(PDO::FETCH_ASSOC);
        }

        $this->loadView('doctor/create_prescription', [
            'products' => $products,
            'customer' => $customer,
            'order_id' => $orderId,
            'pageTitle' => 'Kê đơn thuốc trực tuyến'
        ]);
    }

    /**
     * Lưu đơn thuốc và tạo đơn hàng (UC-M04)
     */
    public function storePrescription()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $customerId = $_POST['customer_id'] ?? null;
            $customerName = $_POST['customer_name'] ?? '';
            $customerPhone = $_POST['customer_phone'] ?? '';
            $customerAddress = $_POST['customer_address'] ?? '';
            $notes = $_POST['notes'] ?? '';

            $productIds = $_POST['products'] ?? [];
            $quantities = $_POST['quantities'] ?? [];
            $dosages = $_POST['dosages'] ?? [];

            if (empty($productIds)) {
                $_SESSION['error_message'] = "Vui lòng chọn ít nhất 1 loại thuốc.";
                $this->redirect(BASE_URL . 'doctor/createPrescription' . ($customerId ? '?user_id=' . $customerId : ''));
                return;
            }

            $db = (new BaseModel())->db;

            try {
                $db->beginTransaction();

                // Chuẩn bị ghi chú chi tiết
                $fullNotes = "Chỉ định y khoa: " . $notes . "\n\nHDSD chi tiết:\n";
                foreach ($productIds as $i => $pid) {
                    // (Sẽ được xây dựng lại trong vòng lặp dưới)
                }

                $subtotal = 0;
                $items = [];
                foreach ($productIds as $i => $pid) {
                    $qty = $quantities[$i];
                    $dosage = $dosages[$i];

                    $product = $db->query("SELECT product_name, price FROM products WHERE product_id = " . intval($pid))->fetch(PDO::FETCH_ASSOC);
                    if ($product) {
                        $subtotal += $product['price'] * $qty;
                        $items[] = [
                            'product_id' => $pid,
                            'product_name' => $product['product_name'],
                            'quantity' => $qty,
                            'unit_price' => $product['price'],
                            'dosage_instruction' => $dosage
                        ];
                        $fullNotes .= "- " . $product['product_name'] . ": " . $dosage . "\n";
                    }
                }

                $orderId = $_POST['order_id'] ?? null;

                if ($orderId && !empty($orderId)) {
                    // 1. Lấy phí vận chuyển cũ để cộng vào tổng tiền
                    $oldOrder = $db->query("SELECT shipping_fee FROM orders WHERE order_id = " . intval($orderId))->fetch(PDO::FETCH_ASSOC);
                    $shippingFee = $oldOrder['shipping_fee'] ?? 0;
                    $totalWithShipping = $subtotal + $shippingFee;

                    // 2. Cập nhật đơn hàng hiện tại (đơn mà khách đã gửi ảnh toa)
                    $sql = "UPDATE orders SET subtotal = :subtotal, total_amount = :total, admin_note = :admin_note, 
                                             prescription_verified = 1, verified_by = :doctor_id, verified_at = NOW(), 
                                             status = 'confirmed' 
                            WHERE order_id = :id";
                    $stmt = $db->prepare($sql);
                    $stmt->execute([
                        'subtotal' => $subtotal,
                        'total' => $totalWithShipping,
                        'admin_note' => $fullNotes,
                        'doctor_id' => $_SESSION['user_id'],
                        'id' => $orderId
                    ]);

                    // 3. Hoàn lại kho cũ (nếu có) trước khi xóa để cập nhật mới
                    $oldDetails = $db->query("SELECT * FROM order_details WHERE order_id = " . intval($orderId))->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($oldDetails as $od) {
                        $sqlReturn = "INSERT INTO inventory_transactions (product_id, batch_id, user_id, order_id, transaction_type, quantity, note) 
                                      VALUES (:pid, :bid, :uid, :oid, 'cancel_order', :qty, 'Hoàn kho để cập nhật lại đơn thuốc')";
                        $db->prepare($sqlReturn)->execute([
                            'pid' => $od['product_id'],
                            'bid' => $od['batch_id'],
                            'uid' => $_SESSION['user_id'],
                            'oid' => $orderId,
                            'qty' => $od['quantity']
                        ]);
                    }

                    // 4. Xóa các chi tiết cũ và tạo mới
                    $db->prepare("DELETE FROM order_details WHERE order_id = ?")->execute([$orderId]);
                } else {
                    // Tạo Order mới hoàn toàn
                    $sql = "INSERT INTO orders (user_id, shipping_name, shipping_phone, shipping_address, shipping_note, 
                                             subtotal, total_amount, payment_method, status, 
                                             has_prescription, prescription_verified, verified_by, verified_at, admin_note) 
                            VALUES (:user_id, :name, :phone, :address, :note, :subtotal, :total, 'cod', 'pending', 
                                    1, 1, :doctor_id, NOW(), :admin_note)";
                    $stmt = $db->prepare($sql);
                    $stmt->execute([
                        'user_id' => $customerId ?: null,
                        'name' => $customerName,
                        'phone' => $customerPhone,
                        'address' => $customerAddress,
                        'note' => '',
                        'subtotal' => $subtotal,
                        'total' => $subtotal,
                        'doctor_id' => $_SESSION['user_id'],
                        'admin_note' => $fullNotes
                    ]);
                    $orderId = $db->lastInsertId();
                }

                // 3. Tạo Order Details với logic FEFO và trừ kho
                foreach ($items as $item) {
                    $remainingToAllocate = $item['quantity'];
                    
                    // Lấy danh sách lô hàng theo FEFO từ Stored Procedure
                    $stmtBatch = $db->prepare("CALL get_batch_fefo(:product_id, :qty)");
                    $stmtBatch->execute(['product_id' => $item['product_id'], 'qty' => $item['quantity']]);
                    $batches = $stmtBatch->fetchAll(PDO::FETCH_ASSOC);
                    $stmtBatch->closeCursor();

                    if (empty($batches)) {
                        throw new Exception("Sản phẩm " . $item['product_name'] . " đã hết hàng hoặc hết hạn.");
                    }

                    foreach ($batches as $batch) {
                        if ($remainingToAllocate <= 0) break;
                        
                        $qtyToTake = min($remainingToAllocate, $batch['quantity_remaining']);

                        // Lưu chi tiết đơn hàng cho từng lô
                        $sqlDetail = "INSERT INTO order_details (order_id, product_id, batch_id, product_name, quantity, unit_price, subtotal) 
                                      VALUES (:order_id, :product_id, :batch_id, :p_name, :qty, :price, :subtotal)";
                        $stmtDetail = $db->prepare($sqlDetail);
                        $stmtDetail->execute([
                            'order_id' => $orderId,
                            'product_id' => $item['product_id'],
                            'batch_id' => $batch['batch_id'],
                            'p_name' => $item['product_name'],
                            'qty' => $qtyToTake,
                            'price' => $item['unit_price'],
                            'subtotal' => $item['unit_price'] * $qtyToTake
                        ]);

                        // Ghi Ledger (Nhật ký biến động kho) - Trừ số lượng
                        $sqlTrans = "INSERT INTO inventory_transactions (product_id, batch_id, user_id, order_id, transaction_type, quantity, note) 
                                    VALUES (:pid, :bid, :uid, :oid, 'sale', :qty, 'Kê đơn bởi dược sĩ (Đơn hàng :oid)')";
                        $stmtTrans = $db->prepare($sqlTrans);
                        $stmtTrans->execute([
                            'pid' => $item['product_id'],
                            'bid' => $batch['batch_id'],
                            'uid' => $_SESSION['user_id'],
                            'oid' => $orderId,
                            'qty' => -$qtyToTake
                        ]);

                        $remainingToAllocate -= $qtyToTake;
                    }

                    if ($remainingToAllocate > 0) {
                        throw new Exception("Không đủ hàng trong kho cho sản phẩm: " . $item['product_name']);
                    }
                }

                $db->commit();
                
                // Log hành động
                (new ActivityLogModel())->log($_SESSION['user_id'], 'create_prescription', 'orders', $orderId, "Kê đơn thuốc trực tuyến cho đơn hàng #$orderId.");
                
                $_SESSION['success_message'] = "Đã kê đơn và tạo đơn hàng thành công (Mã ĐH: #$orderId).";

                // Gửi email thông báo cho khách hàng
                $customerEmail = $_POST['customer_email'] ?? '';
                if ($customerEmail) {
                    $orderData = $this->orderModel->getOrderById($orderId);
                    $orderDetails = $this->orderModel->getOrderDetails($orderId);
                    $emailTemplate = new OrderConfirmationEmail($orderData, $orderDetails);
                    $this->mailer->dispatch($emailTemplate, $customerEmail, $_POST['customer_name'] ?? 'Khách hàng', $orderId);
                }

                // Redirect back to prescriptions list
                $this->redirect(BASE_URL . 'doctor/prescriptions');
            } catch (Exception $e) {
                $db->rollBack();
                $_SESSION['error_message'] = "Lỗi khi tạo đơn: " . $e->getMessage();
                $this->redirect(BASE_URL . 'doctor/createPrescription');
            }
        }
    }
}
