<?php

require_once __DIR__ . '/../BaseController.php';

class AdminGeneralController extends BaseController {
    
    public function __construct() {
        parent::__construct();
        if (!isset($_SESSION['user_id'])) {
            $this->redirect(BASE_URL . 'auth/login');
        }
        $this->checkPermission('manage_categories'); // Ref point 🔴
    }
    
    /**
     * Quản lý nhà cung cấp
     */
    public function suppliers() {
        $db = (new BaseModel())->db;
        $suppliers = $db->query("SELECT * FROM suppliers ORDER BY supplier_id DESC")->fetchAll(PDO::FETCH_ASSOC);
        $this->loadView('admin/general/suppliers', ['suppliers' => $suppliers, 'pageTitle' => 'Nhà cung cấp']);
    }
    
    /**
     * Quản lý tương tác thuốc
     */
    public function interactions() {
        $db = (new BaseModel())->db;
        $sql = "SELECT di.*, p1.product_name as drug_a, p2.product_name as drug_b 
                FROM drug_interactions di
                JOIN products p1 ON di.drug_a_id = p1.product_id
                JOIN products p2 ON di.drug_b_id = p2.product_id";
        $interactions = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $this->loadView('admin/general/interactions', ['interactions' => $interactions, 'pageTitle' => 'Tương tác thuốc']);
    }

    /**
     * Quản lý nhà sản xuất
     */
    public function manufacturers() {
        $db = (new BaseModel())->db;
        $manufacturers = $db->query("SELECT * FROM manufacturers ORDER BY manufacturer_id DESC")->fetchAll(PDO::FETCH_ASSOC);
        $this->loadView('admin/general/manufacturers', ['manufacturers' => $manufacturers, 'pageTitle' => 'Nhà sản xuất']);
    }
    
    /**
     * Quản lý danh mục
     */
    public function categories() {
        $db = (new BaseModel())->db;
        $sql = "SELECT c1.*, c2.category_name as parent_name 
                FROM categories c1 
                LEFT JOIN categories c2 ON c1.parent_category_id = c2.category_id 
                ORDER BY c1.category_id DESC";
        $categories = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $this->loadView('admin/general/categories', ['categories' => $categories, 'pageTitle' => 'Danh mục sản phẩm']);
    }

    /**
     * Thêm danh mục mới
     */
    public function addCategory() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();
            $name = trim($_POST['category_name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $parent_id = !empty($_POST['parent_category_id']) ? $_POST['parent_category_id'] : null;
            
            if (!empty($name)) {
                $db = (new BaseModel())->db;
                $stmt = $db->prepare("INSERT INTO categories (category_name, description, parent_category_id) VALUES (?, ?, ?)");
                $stmt->execute([$name, $description, $parent_id]);
                $_SESSION['success_message'] = "Đã thêm danh mục mới: $name";
            }
            $this->redirect(BASE_URL . 'admin/general/categories');
        }
    }

    /**
     * Cập nhật danh mục
     */
    public function updateCategory() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();
            $id = $_POST['category_id'] ?? null;
            $name = trim($_POST['category_name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $parent_id = !empty($_POST['parent_category_id']) ? $_POST['parent_category_id'] : null;
            
            if (!empty($name) && !empty($id)) {
                $db = (new BaseModel())->db;
                $stmt = $db->prepare("UPDATE categories SET category_name = ?, description = ?, parent_category_id = ? WHERE category_id = ?");
                $stmt->execute([$name, $description, $parent_id, $id]);
                $_SESSION['success_message'] = "Đã cập nhật danh mục: $name";
            }
            $this->redirect(BASE_URL . 'admin/general/categories');
        }
    }

    /**
     * Xóa danh mục
     */
    public function deleteCategory() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();
            $id = $_POST['category_id'] ?? null;
            
            if (!empty($id)) {
                $db = (new BaseModel())->db;
                
                // 1. Kiểm tra sản phẩm
                $checkProd = $db->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
                $checkProd->execute([$id]);
                
                // 2. Kiểm tra danh mục con
                $checkChild = $db->prepare("SELECT COUNT(*) FROM categories WHERE parent_category_id = ?");
                $checkChild->execute([$id]);

                if ($checkProd->fetchColumn() > 0) {
                    $_SESSION['error_message'] = "Không thể xóa danh mục này vì vẫn còn sản phẩm thuộc danh mục.";
                } elseif ($checkChild->fetchColumn() > 0) {
                    $_SESSION['error_message'] = "Không thể xóa vì danh mục này đang chứa các danh mục con.";
                } else {
                    $stmt = $db->prepare("DELETE FROM categories WHERE category_id = ?");
                    $stmt->execute([$id]);
                    $_SESSION['success_message'] = "Đã xóa danh mục thành công.";
                }
            }
            $this->redirect(BASE_URL . 'admin/general/categories');
        }
    }
}
