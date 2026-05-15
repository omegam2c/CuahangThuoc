<?php

require_once __DIR__ . '/../BaseController.php';
require_once __DIR__ . '/../../Models/ProductModel.php';

class AdminProductController extends BaseController {
    private $productModel;
    
    public function __construct() {
        parent::__construct();
        if (!isset($_SESSION['user_id'])) {
            $this->redirect(BASE_URL . 'auth/login');
        }
        $this->checkPermission('manage_prices_vouchers'); // Ref point 🔴
        $this->productModel = new ProductModel();
    }
    
    /**
     * Danh sách sản phẩm
     */
    public function index() {
        $db = (new BaseModel())->db;
        
        $q = $_GET['q'] ?? '';
        $category_id = $_GET['category_id'] ?? '';
        $is_rx = $_GET['is_rx'] ?? '';
        
        $where = ["1=1"];
        $params = [];
        
        if ($q !== '') {
            $where[] = "(p.product_name LIKE :q OR p.product_id = :qid)";
            $params['q'] = "%$q%";
            $params['qid'] = $q;
        }
        
        if ($category_id !== '') {
            $where[] = "(p.category_id = :cat_id OR p.category_id IN (SELECT category_id FROM categories WHERE parent_category_id = :cat_id))";
            $params['cat_id'] = $category_id;
        }

        if ($is_rx !== '') {
            $where[] = "p.is_prescription_required = :is_rx";
            $params['is_rx'] = (int)$is_rx;
        }
        
        $whereSql = implode(' AND ', $where);
        
        $sql = "SELECT p.*, c.category_name, m.manufacturer_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.category_id 
                LEFT JOIN manufacturers m ON p.manufacturer_id = m.manufacturer_id 
                WHERE $whereSql
                ORDER BY p.product_id DESC";
                
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $categories = $db->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
        
        $this->loadView('admin/product/index', [
            'products' => $products,
            'categories' => $categories,
            'pageTitle' => 'Quản lý sản phẩm',
            'q' => $q,
            'category_id' => $category_id,
            'is_rx' => $is_rx
        ]);
    }
    
    /**
     * Thêm sản phẩm mới
     */
    public function create() {
        $db = (new BaseModel())->db;
        $categories = $db->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
        $manufacturers = $db->query("SELECT * FROM manufacturers")->fetchAll(PDO::FETCH_ASSOC);
        
        $this->loadView('admin/product/create', [
            'categories' => $categories,
            'manufacturers' => $manufacturers,
            'pageTitle' => 'Thêm sản phẩm mới'
        ]);
    }
    
    /**
     * Lưu sản phẩm
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();
            $db = (new BaseModel())->db;
            
            // Xử lý upload ảnh (Ref point 🎨)
            $imageUrl = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../../../public/img/products/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0700, true);
                
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $_FILES['image']['tmp_name']);
                finfo_close($finfo);

                if (in_array($mime, ['image/jpeg', 'image/png', 'image/webp'])) {
                    $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                    $imageUrl = bin2hex(random_bytes(10)) . '.' . $ext;
                    move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $imageUrl);
                }
            }

            $isRx = ($_POST['sale_type'] === 'prescription') ? 1 : 0;
            $isOtc = ($_POST['sale_type'] === 'otc') ? 1 : 0;

            $sql = "INSERT INTO products (product_name, category_id, manufacturer_id, price, discount_percent, 
                                        generic_name, dosage_form, unit, indications, sale_type, is_prescription_required, is_otc, image_url) 
                    VALUES (:name, :cat_id, :m_id, :price, :discount, :generic, :dosage, :unit, :indications, :sale_type, :is_rx, :is_otc, :img)";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([
                'name' => $_POST['product_name'],
                'cat_id' => $_POST['category_id'],
                'm_id' => $_POST['manufacturer_id'],
                'price' => $_POST['price'],
                'discount' => $_POST['discount_percent'] ?? 0,
                'generic' => $_POST['generic_name'] ?? '',
                'dosage' => $_POST['dosage_form'] ?? '',
                'unit' => $_POST['unit'] ?? 'Viên',
                'indications' => $_POST['indications'] ?? '',
                'sale_type' => $_POST['sale_type'] ?? 'otc',
                'is_rx' => $isRx,
                'is_otc' => $isOtc,
                'img' => $imageUrl
            ]);
            
            $productId = $db->lastInsertId();
            
            // Audit Log (Ref point 📊)
            require_once __DIR__ . '/../../Models/ActivityLogModel.php';
            (new ActivityLogModel())->log($_SESSION['user_id'], 'create_product', 'products', $productId, "Tạo mới sản phẩm: " . $_POST['product_name']);

            $_SESSION['success_message'] = "Thêm sản phẩm thành công.";
            $this->redirect(BASE_URL . 'admin/product');
        }
    }

    /**
     * Sửa sản phẩm
     */
    public function edit() {
        $id = $_GET['id'] ?? 0;
        $db = (new BaseModel())->db;
        
        $product = $db->query("SELECT * FROM products WHERE product_id = " . intval($id))->fetch(PDO::FETCH_ASSOC);
        if (!$product) {
            $this->redirect(BASE_URL . 'admin/product');
        }
        
        $categories = $db->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
        $manufacturers = $db->query("SELECT * FROM manufacturers")->fetchAll(PDO::FETCH_ASSOC);
        
        $this->loadView('admin/product/edit', [
            'product' => $product,
            'categories' => $categories,
            'manufacturers' => $manufacturers,
            'pageTitle' => 'Sửa sản phẩm'
        ]);
    }

    /**
     * Cập nhật sản phẩm
     */
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();
            $id = $_POST['product_id'] ?? 0;
            $db = (new BaseModel())->db;
            
            // Lấy dữ liệu cũ để so sánh giá (Audit)
            $oldProduct = $db->query("SELECT price, product_name FROM products WHERE product_id = " . intval($id))->fetch(PDO::FETCH_ASSOC);

            // Xử lý upload ảnh
            $imageUrl = $_POST['old_image'] ?? null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../../../public/img/products/';
                $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $imageUrl = bin2hex(random_bytes(10)) . '.' . $ext;
                move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $imageUrl);
            }

            $isRx = ($_POST['sale_type'] === 'prescription') ? 1 : 0;
            $isOtc = ($_POST['sale_type'] === 'otc') ? 1 : 0;

            $sql = "UPDATE products SET 
                    product_name = :name, category_id = :cat_id, manufacturer_id = :m_id, 
                    price = :price, discount_percent = :discount, generic_name = :generic, 
                    dosage_form = :dosage, unit = :unit, indications = :indications, 
                    sale_type = :sale_type, is_prescription_required = :is_rx, is_otc = :is_otc, image_url = :img 
                    WHERE product_id = :id";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([
                'name' => $_POST['product_name'],
                'cat_id' => $_POST['category_id'],
                'm_id' => $_POST['manufacturer_id'],
                'price' => $_POST['price'],
                'discount' => $_POST['discount_percent'] ?? 0,
                'generic' => $_POST['generic_name'] ?? '',
                'dosage' => $_POST['dosage_form'] ?? '',
                'unit' => $_POST['unit'] ?? 'Viên',
                'indications' => $_POST['indications'] ?? '',
                'sale_type' => $_POST['sale_type'] ?? 'otc',
                'is_rx' => $isRx,
                'is_otc' => $isOtc,
                'img' => $imageUrl,
                'id' => $id
            ]);
            
            // Audit Log: Theo dõi thay đổi giá (Ref point 3 - Audit Trail)
            require_once __DIR__ . '/../../Models/ActivityLogModel.php';
            $logModel = new ActivityLogModel();
            if ($oldProduct['price'] != $_POST['price']) {
                $logModel->log($_SESSION['user_id'], 'update_price', 'products', $id, 
                    "Thay đổi giá '{$oldProduct['product_name']}': " . number_format($oldProduct['price']) . " -> " . number_format($_POST['price']));
            } else {
                $logModel->log($_SESSION['user_id'], 'update_product', 'products', $id, "Cập nhật thông tin sản phẩm: " . $_POST['product_name']);
            }

            $_SESSION['success_message'] = "Cập nhật sản phẩm thành công.";
            $this->redirect(BASE_URL . 'admin/product');
        }
    }

    /**
     * Thay đổi trạng thái hiển thị của sản phẩm (Hiển thị/Ẩn)
     */
    public function toggleStatus() {
        $id = $_GET['id'] ?? 0;
        if ($id) {
            $db = (new BaseModel())->db;
            
            // Lấy trạng thái hiện tại
            $stmt = $db->prepare("SELECT is_active, product_name FROM products WHERE product_id = ?");
            $stmt->execute([$id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$product) {
                $this->redirect(BASE_URL . 'admin/product');
            }

            $newStatus = $product['is_active'] ? 0 : 1;
            
            // Nếu là ẩn sản phẩm, kiểm tra đơn hàng đang xử lý
            if ($newStatus == 0) {
                $stmtCheck = $db->prepare("SELECT COUNT(*) FROM order_details od JOIN orders o ON od.order_id = o.order_id WHERE od.product_id = ? AND o.status IN ('pending', 'processing', 'shipping')");
                $stmtCheck->execute([$id]);
                $pendingCount = $stmtCheck->fetchColumn();
                
                if ($pendingCount > 0) {
                    $_SESSION['error_message'] = "Không thể ẩn sản phẩm đang có trong đơn hàng chưa hoàn tất.";
                    $this->redirect(BASE_URL . 'admin/product');
                }
            }
            
            $db->prepare("UPDATE products SET is_active = ? WHERE product_id = ?")->execute([$newStatus, $id]);
            
            // Audit Log
            require_once __DIR__ . '/../../Models/ActivityLogModel.php';
            $action = $newStatus ? 'unhide_product' : 'hide_product';
            $actionText = $newStatus ? 'Hiển thị lại' : 'Ẩn';
            (new ActivityLogModel())->log($_SESSION['user_id'], $action, 'products', $id, "$actionText sản phẩm: " . $product['product_name']);

            $_SESSION['success_message'] = "Đã " . strtolower($actionText) . " sản phẩm.";
        }
        $this->redirect(BASE_URL . 'admin/product');
    }

}
