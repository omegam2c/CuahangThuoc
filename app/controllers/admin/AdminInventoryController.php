<?php

require_once __DIR__ . '/../BaseController.php';
require_once __DIR__ . '/../../Services/InventoryService.php';
require_once __DIR__ . '/../../Models/ActivityLogModel.php';

class AdminInventoryController extends BaseController {
    private $inventoryService;
    private $inventoryModel;
    
    public function __construct() {
        parent::__construct();
        if (!isset($_SESSION['user_id'])) {
            $this->redirect(BASE_URL . 'auth/login');
        }
        $this->inventoryService = new InventoryService();
        $this->inventoryModel = new InventoryModel();
    }
    
    /**
     * Dashboard Quản lý kho
     */
    public function index() {
        // Cho phép cả người xem và người quản lý (Ref point 🔴)
        if (!$this->hasPermission('view_inventory') && !$this->hasPermission('manage_inventory')) {
            $this->denyAccess();
        }

        $alerts = $this->inventoryService->getAllStockAlerts();
        $summary = $this->inventoryModel->getInventorySummary();
        
        $this->loadView('admin/inventory/index', [
            'summary' => $summary,
            'expiry_alerts' => $alerts['expiry'],
            'low_stock_alerts' => $alerts['low_stock'],
            'pageTitle' => 'Quản lý kho & Cảnh báo'
        ]);
    }

    /**
     * Xem chi tiết lô hàng của một sản phẩm
     */
    public function productBatches() {
        if (!$this->hasPermission('view_inventory') && !$this->hasPermission('manage_inventory')) {
            $this->denyAccess();
        }
        $productId = $_GET['id'] ?? 0;
        if (!$productId) {
            $this->redirect(BASE_URL . 'admin/inventory');
        }
        
        $batches = $this->inventoryModel->getBatchesByProductId($productId);
        $db = (new BaseModel())->db;
        $product = $db->query("SELECT product_name FROM products WHERE product_id = " . intval($productId))->fetchColumn();
        
        $this->loadView('admin/inventory/batches', [
            'batches' => $batches,
            'product_name' => $product,
            'pageTitle' => 'Chi tiết lô hàng: ' . $product
        ]);
    }

    /**
     * Giao diện nhập hàng mới
     */
    public function import() {
        $this->checkPermission('manage_inventory');
        $db = (new BaseModel())->db;
        $suppliers = $db->query("SELECT * FROM suppliers")->fetchAll(PDO::FETCH_ASSOC);
        $products = $db->query("SELECT product_id, product_name FROM products WHERE is_active = TRUE")->fetchAll(PDO::FETCH_ASSOC);
        
        $this->loadView('admin/inventory/import', [
            'suppliers' => $suppliers,
            'products' => $products,
            'pageTitle' => 'Nhập hàng vào kho'
        ]);
    }

    /**
     * Xử lý lưu phiếu nhập hàng
     */
    public function handleImport() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();
            $this->checkPermission('manage_inventory');
            
            try {
                $supplierName = $_POST['supplier_name'] ?? '';
                $supplierId = $this->inventoryModel->getOrCreateSupplier($supplierName);
                
                $items = [];
                if (isset($_POST['products']) && is_array($_POST['products'])) {
                    foreach ($_POST['products'] as $i => $pid) {
                        $items[] = [
                            'product_id' => $pid,
                            'batch_number' => $_POST['batch_numbers'][$i] ?? '',
                            'manufacture_date' => $_POST['manufacture_dates'][$i] ?? '',
                            'expiry_date' => $_POST['expiry_dates'][$i] ?? '',
                            'quantity' => intval($_POST['quantities'][$i] ?? 0),
                            'purchase_price' => floatval($_POST['purchase_prices'][$i] ?? 0),
                            'selling_price' => floatval($_POST['selling_prices'][$i] ?? 0),
                            'storage_location' => $_POST['storage_locations'][$i] ?? ''
                        ];
                    }
                }
                
                $data = [
                    'supplier_id' => $supplierId,
                    'receipt_date' => $_POST['receipt_date'] ?? date('Y-m-d'),
                    'invoice_number' => $_POST['invoice_number'] ?? '',
                    'total_amount' => floatval($_POST['total_amount'] ?? 0),
                    'notes' => $_POST['notes'] ?? ''
                ];
                
                if ($this->inventoryService->processImport($data, $items, $_SESSION['user_id'])) {
                    (new ActivityLogModel())->log($_SESSION['user_id'], 'import_inventory', 'inventory_receipts', null, "Nhập kho từ nhà cung cấp $supplierName. HĐ: " . $data['invoice_number'] . ". Tổng: " . number_format($data['total_amount']) . "đ");
                    $_SESSION['success_message'] = "Nhập kho thành công!";
                }
            } catch (Exception $e) {
                $_SESSION['error_message'] = $e->getMessage();
            }
            
            $this->redirect(BASE_URL . 'admin/inventory');
        }
    }
}
