<?php

require_once __DIR__ . '/BaseModel.php';

class InventoryModel extends BaseModel {
    
    /**
     * Lấy tóm tắt tồn kho (từ View v_inventory_summary)
     */
    public function getInventorySummary() {
        $sql = "SELECT * FROM v_inventory_summary ORDER BY total_quantity ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Lấy cảnh báo hạn sử dụng (từ View v_expiry_alerts)
     */
    public function getExpiryAlerts() {
        $sql = "SELECT 
                    b.batch_id,
                    p.product_name,
                    b.batch_number,
                    b.manufacture_date,
                    b.expiry_date,
                    DATEDIFF(b.expiry_date, CURRENT_DATE) as days_to_expiry,
                    b.quantity_remaining,
                    b.storage_location,
                    b.status,
                    CASE 
                        WHEN DATEDIFF(b.expiry_date, CURRENT_DATE) <= 0 THEN 'danger'
                        WHEN DATEDIFF(b.expiry_date, CURRENT_DATE) <= 30 THEN 'warning'
                        WHEN DATEDIFF(b.expiry_date, CURRENT_DATE) <= 90 THEN 'info'
                        ELSE 'normal'
                    END as alert_level
                FROM batches b
                JOIN products p ON b.product_id = p.product_id
                WHERE b.quantity_remaining > 0 
                    AND DATEDIFF(b.expiry_date, CURRENT_DATE) <= 90
                ORDER BY b.expiry_date ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Lấy danh sách lô hàng của một sản phẩm
     */
    public function getBatchesByProductId($productId) {
        $sql = "SELECT * FROM batches WHERE product_id = :pid ORDER BY expiry_date ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['pid' => $productId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Lấy hoặc tạo nhà cung cấp
     */
    public function getOrCreateSupplier($supplierName) {
        $supplierName = trim($supplierName);
        if (empty($supplierName)) return null;

        $stmt = $this->db->prepare("SELECT supplier_id FROM suppliers WHERE supplier_name = ? LIMIT 1");
        $stmt->execute([$supplierName]);
        $supplier = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($supplier) {
            return $supplier['supplier_id'];
        }

        $stmtInsert = $this->db->prepare("INSERT INTO suppliers (supplier_name) VALUES (?)");
        $stmtInsert->execute([$supplierName]);
        return $this->db->lastInsertId();
    }
    
    /**
     * Kiểm tra khả năng đáp ứng tồn kho (Tránh Overselling) (Ref point 🔐)
     */
    public function checkStockAvailability($productId, $requestedQty) {
        $sql = "SELECT SUM(quantity_remaining) as total 
                FROM batches 
                WHERE product_id = :pid 
                  AND expiry_date > CURRENT_DATE 
                  AND status = 'active'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['pid' => $productId]);
        $total = $stmt->fetchColumn();
        
        return ($total >= $requestedQty);
    }

    /**
     * Lấy danh sách thuốc sắp hết hàng (Low Stock) (Ref point 2)
     */
    public function getLowStockAlerts($threshold = 20) {
        $sql = "SELECT p.product_id, p.product_name, SUM(b.quantity_remaining) as total_qty, p.unit
                FROM products p
                LEFT JOIN batches b ON p.product_id = b.product_id
                WHERE p.is_active = TRUE
                GROUP BY p.product_id, p.product_name, p.unit
                HAVING total_qty <= :threshold
                ORDER BY total_qty ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['threshold' => $threshold]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Thêm phiếu nhập kho mới và các lô hàng (Có ghi Ledger)
     */
    public function addStockReceipt($data, $items) {
        try {
            $this->db->beginTransaction();
            
            // 1. Thêm phiếu nhập
            $sql = "INSERT INTO stock_receipts (supplier_id, user_id, receipt_date, invoice_number, total_amount, notes, status) 
                    VALUES (:supplier_id, :user_id, :r_date, :inv_num, :total, :notes, 'completed')";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'supplier_id' => $data['supplier_id'],
                'user_id' => $data['user_id'],
                'r_date' => $data['receipt_date'],
                'inv_num' => $data['invoice_number'],
                'total' => $data['total_amount'],
                'notes' => $data['notes'] ?? ''
            ]);
            $receiptId = $this->db->lastInsertId();
            
            // Audit Log cho hành động nhập kho
            require_once __DIR__ . '/ActivityLogModel.php';
            (new ActivityLogModel())->log($data['user_id'], 'import_stock', 'stock_receipts', $receiptId, "Nhập kho mới. Hóa đơn: " . $data['invoice_number']);

            // 2. Thêm từng lô hàng & Ghi Ledger
            foreach ($items as $item) {
                $sqlBatch = "INSERT INTO batches (product_id, receipt_id, batch_number, manufacture_date, expiry_date, 
                                               quantity_received, quantity_remaining, purchase_price, selling_price, storage_location) 
                             VALUES (:pid, :rid, :b_num, :m_date, :e_date, :qty, 0, :p_price, :s_price, :loc)";
                $stmtBatch = $this->db->prepare($sqlBatch);
                $stmtBatch->execute([
                    'pid' => $item['product_id'],
                    'rid' => $receiptId,
                    'b_num' => $item['batch_number'],
                    'm_date' => $item['manufacture_date'],
                    'e_date' => $item['expiry_date'],
                    'qty' => $item['quantity'],
                    'p_price' => $item['purchase_price'],
                    's_price' => $item['selling_price'],
                    'loc' => $item['storage_location'] ?? ''
                ]);
                $batchId = $this->db->lastInsertId();

                // Ghi vào Ledger (Nhật ký biến động kho)
                $sqlTrans = "INSERT INTO inventory_transactions (product_id, batch_id, user_id, receipt_id, transaction_type, quantity, note) 
                            VALUES (:pid, :bid, :uid, :rid, 'import', :qty, 'Nhập hàng từ phiếu nhập')";
                $stmtTrans = $this->db->prepare($sqlTrans);
                $stmtTrans->execute([
                    'pid' => $item['product_id'],
                    'bid' => $batchId,
                    'uid' => $data['user_id'],
                    'rid' => $receiptId,
                    'qty' => $item['quantity']
                ]);
            }
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Lỗi nhập kho: " . $e->getMessage());
            return false;
        }
    }
}
