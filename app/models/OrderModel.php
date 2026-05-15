<?php

require_once __DIR__ . '/BaseModel.php';

class OrderModel extends BaseModel {
    
    /**
     * Tạo đơn hàng mới với logic FEFO
     */
    public function createOrder($userId, $shippingData, $cartItems, $cartId = null) {
        try {
            $this->db->beginTransaction();
            
            // 1. Tính tổng tiền
            $subtotal = 0;
            foreach ($cartItems as $item) {
                $subtotal += $item['current_price'] * $item['quantity'];
            }
            $totalAmount = $subtotal + ($shippingData['shipping_fee'] ?? 0);
            
            // 2. Lưu thông tin đơn hàng
            $sql = "INSERT INTO orders (user_id, shipping_name, shipping_phone, customer_email, shipping_address, shipping_note, 
                                     subtotal, total_amount, payment_method, status, 
                                     has_prescription, prescription_image) 
                    VALUES (:user_id, :name, :phone, :email, :address, :note, :subtotal, :total, :payment, 'pending', 
                            :has_rx, :rx_image)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'user_id' => $userId,
                'name' => $shippingData['name'],
                'phone' => $shippingData['phone'],
                'email' => $shippingData['email'] ?? null,
                'address' => $shippingData['address'],
                'note' => $shippingData['note'] ?? '',
                'subtotal' => $subtotal,
                'total' => $totalAmount,
                'payment' => $shippingData['payment_method'] ?? 'cod',
                'has_rx' => $shippingData['has_prescription'] ? 1 : 0,
                'rx_image' => $shippingData['prescription_image'] ?? null
            ]);
            
            $orderId = $this->db->lastInsertId();
            
            // 3. Lưu chi tiết đơn hàng & Trừ kho theo FEFO
            foreach ($cartItems as $item) {
                $remainingToAllocate = $item['quantity'];
                
                // Lấy danh sách lô hàng theo FEFO từ Stored Procedure
                $stmt = $this->db->prepare("CALL get_batch_fefo(:product_id, :qty)");
                $stmt->execute(['product_id' => $item['product_id'], 'qty' => $item['quantity']]);
                $batches = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $stmt->closeCursor(); // Quan trọng khi dùng SP
                
                if (empty($batches)) {
                    throw new Exception("Sản phẩm " . $item['product_name'] . " đã hết hàng hoặc hết hạn.");
                }
                
                foreach ($batches as $batch) {
                    if ($remainingToAllocate <= 0) break;
                    
                    $qtyToTake = min($remainingToAllocate, $batch['quantity_remaining']);
                    
                    // Lưu chi tiết đơn hàng cho từng lô
                    $sql = "INSERT INTO order_details (order_id, product_id, batch_id, product_name, quantity, unit_price, subtotal) 
                            VALUES (:order_id, :product_id, :batch_id, :p_name, :qty, :price, :subtotal)";
                    $stmtDetail = $this->db->prepare($sql);
                    $stmtDetail->execute([
                        'order_id' => $orderId,
                        'product_id' => $item['product_id'],
                        'batch_id' => $batch['batch_id'],
                        'p_name' => $item['product_name'],
                        'qty' => $qtyToTake,
                        'price' => $item['current_price'],
                        'subtotal' => $item['current_price'] * $qtyToTake
                    ]);
                    
                    // Ghi Ledger (Nhật ký biến động kho) - Trừ số lượng
                    // Trigger 'trg_inventory_transaction_after_insert' sẽ tự động cập nhật batches.quantity_remaining
                    $sqlTrans = "INSERT INTO inventory_transactions (product_id, batch_id, user_id, order_id, transaction_type, quantity, note) 
                                VALUES (:pid, :bid, :uid, :oid, 'sale', :qty, 'Bán hàng (Đơn hàng :oid)')";
                    $stmtTrans = $this->db->prepare($sqlTrans);
                    $stmtTrans->execute([
                        'pid' => $item['product_id'],
                        'bid' => $batch['batch_id'],
                        'uid' => $userId,
                        'oid' => $orderId,
                        'qty' => -$qtyToTake // Trừ kho
                    ]);
                    
                    $remainingToAllocate -= $qtyToTake;
                }
                
                if ($remainingToAllocate > 0) {
                    throw new Exception("Không đủ hàng trong kho cho sản phẩm: " . $item['product_name']);
                }
            }
            
            // 4. Xóa giỏ hàng sau khi đặt thành công
            if ($cartId) {
                $sql = "DELETE FROM cart_items WHERE cart_id = :cart_id";
                $stmt = $this->db->prepare($sql);
                $stmt->execute(['cart_id' => $cartId]);
            } elseif ($userId) {
                $sql = "DELETE FROM cart_items WHERE cart_id = (SELECT cart_id FROM carts WHERE user_id = :user_id)";
                $stmt = $this->db->prepare($sql);
                $stmt->execute(['user_id' => $userId]);
            }
            
            $this->db->commit();
            return $orderId;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Lỗi tạo đơn hàng: " . $e->getMessage());
            $_SESSION['checkout_error'] = $e->getMessage();
            return false;
        }
    }
    
    /**
     * Tạo đơn thuốc (không cần chọn sản phẩm ngay)
     */
    public function createPrescriptionOrder($data) {
        try {
            $sql = "INSERT INTO orders (user_id, shipping_name, shipping_phone, shipping_address, shipping_note, 
                                     subtotal, total_amount, payment_method, status, 
                                     has_prescription, prescription_image, prescription_verified) 
                    VALUES (:user_id, :name, :phone, :address, :note, :subtotal, :total, :payment, :status, 
                            :has_rx, :rx_image, :rx_verified)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'user_id' => $data['user_id'],
                'name' => $data['receiver_name'],
                'phone' => $data['receiver_phone'],
                'address' => $data['shipping_address'] ?? '',
                'note' => $data['note'] ?? '',
                'subtotal' => 0,
                'total' => 0,
                'payment' => 'cod',
                'status' => 'pending',
                'has_rx' => 1,
                'rx_image' => $data['prescription_image'],
                'rx_verified' => 0
            ]);
            
            return $this->db->lastInsertId();
        } catch (Exception $e) {
            error_log("Lỗi tạo đơn thuốc: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Lấy lịch sử đơn hàng của người dùng
     */
    public function getOrdersByUserId($userId) {
        $sql = "SELECT * FROM orders WHERE user_id = :user_id ORDER BY order_date DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Lấy chi tiết một đơn hàng theo ID
     */
    public function getOrderById($orderId) {
        $sql = "SELECT * FROM orders WHERE order_id = :order_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['order_id' => $orderId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy danh sách sản phẩm trong đơn hàng
     */
    public function getOrderDetails($orderId) {
        $sql = "SELECT * FROM order_details WHERE order_id = :order_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['order_id' => $orderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Cập nhật trạng thái thanh toán
     */
    public function updatePaymentStatus($orderId, $status) {
        $sql = "UPDATE orders SET payment_status = :status WHERE order_id = :order_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['status' => $status, 'order_id' => $orderId]);
    }
}
