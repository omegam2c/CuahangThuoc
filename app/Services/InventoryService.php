<?php

require_once __DIR__ . '/../Models/InventoryModel.php';
require_once __DIR__ . '/../Models/ActivityLogModel.php';

class InventoryService {
    private $inventoryModel;
    private $logModel;

    public function __construct() {
        $this->inventoryModel = new InventoryModel();
        $this->logModel = new ActivityLogModel();
    }

    /**
     * Lấy toàn bộ cảnh báo kho (Hết hạn & Sắp hết hàng)
     */
    public function getAllStockAlerts() {
        return [
            'expiry' => $this->inventoryModel->getExpiryAlerts(),
            'low_stock' => $this->inventoryModel->getLowStockAlerts(20)
        ];
    }

    /**
     * Xử lý nhập kho chuyên nghiệp
     */
    public function processImport($data, $items, $userId) {
        $data['user_id'] = $userId;
        
        // Validate dates
        foreach ($items as $item) {
            if (strtotime($item['manufacture_date']) >= strtotime($item['expiry_date'])) {
                throw new Exception("Ngày sản xuất phải nhỏ hơn hạn sử dụng cho lô " . $item['batch_number']);
            }
        }

        $result = $this->inventoryModel->addStockReceipt($data, $items);
        
        if ($result) {
            $this->logModel->log($userId, 'import_completed', 'stock_receipts', null, "Hoàn tất nhập kho lô hàng mới.");
        }

        return $result;
    }

    /**
     * Kiểm tra tồn kho khả dụng cho giỏ hàng
     */
    public function validateCartStock($cartItems) {
        $errors = [];
        foreach ($cartItems as $item) {
            if (!$this->inventoryModel->checkStockAvailability($item['product_id'], $item['quantity'])) {
                $errors[] = "Sản phẩm '{$item['product_name']}' không đủ tồn kho khả dụng.";
            }
        }
        return $errors;
    }
}
