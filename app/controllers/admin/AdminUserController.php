<?php

require_once __DIR__ . '/../BaseController.php';
require_once __DIR__ . '/../../Models/BaseModel.php';
require_once __DIR__ . '/../../Models/UserModel.php';
require_once __DIR__ . '/../../Models/ActivityLogModel.php';

class AdminUserController extends BaseController {
    
    public function __construct() {
        parent::__construct();
        if (!isset($_SESSION['user_id'])) {
            $this->redirect(BASE_URL . 'auth/login');
        }
        $this->checkPermission('manage_pharmacists'); // Ref point 🔴
    }
    
    /**
     * Danh sách người dùng
     */
    public function index() {
        $db = (new BaseModel())->db;
        
        $q = $_GET['q'] ?? '';
        $role_id = $_GET['role_id'] ?? '';
        
        $where = ["1=1"];
        $params = [];
        
        if ($q !== '') {
            $where[] = "(username LIKE :q OR full_name LIKE :q OR email LIKE :q OR phone LIKE :q)";
            $params['q'] = "%$q%";
        }
        
        if ($role_id !== '') {
            $where[] = "role_id = :role_id";
            $params['role_id'] = $role_id;
        }
        
        $whereSql = implode(' AND ', $where);
        
        $sql = "SELECT user_id, username, email, full_name, phone, role_id, is_active, created_at, last_login 
                FROM users 
                WHERE $whereSql
                ORDER BY user_id DESC";
                
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $roles = [
            1 => 'Quản trị viên',
            2 => 'Dược sĩ',
            3 => 'Chủ cửa hàng',
            4 => 'Khách hàng'
        ];
        
        $this->loadView('admin/users/index', [
            'users' => $users,
            'roles' => $roles,
            'q' => $q,
            'role_id' => $role_id,
            'pageTitle' => 'Quản lý người dùng'
        ]);
    }
    
    /**
     * Form thêm người dùng mới
     */
    public function create() {
        $roles = [
            1 => 'Quản trị viên (Kỹ thuật)',
            2 => 'Dược sĩ (Chuyên môn)',
            3 => 'Chủ cửa hàng (Kinh doanh)',
            4 => 'Khách hàng'
        ];
        
        $this->loadView('admin/users/create', [
            'roles' => $roles,
            'pageTitle' => 'Thêm người dùng mới'
        ]);
    }
    
    /**
     * Xử lý lưu người dùng mới
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userModel = new UserModel();
            
            $data = [
                'username' => trim($_POST['username'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'full_name' => trim($_POST['full_name'] ?? ''),
                'phone' => trim($_POST['phone'] ?? ''),
                'address' => trim($_POST['address'] ?? ''),
                'role_id' => $_POST['role_id'] ?? 4,
                'password' => $_POST['password'] ?? ''
            ];
            
            // Validation cơ bản
            if (empty($data['username']) || empty($data['email']) || empty($data['password']) || empty($data['full_name'])) {
                $_SESSION['error_message'] = "Vui lòng điền đầy đủ các trường bắt buộc.";
                $this->redirect(BASE_URL . 'admin/users/create');
                return;
            }
            
            if ($userModel->checkUsernameExists($data['username'])) {
                $_SESSION['error_message'] = "Tên tài khoản đã tồn tại.";
                $this->redirect(BASE_URL . 'admin/users/create');
                return;
            }
            
            if ($userModel->checkEmailExists($data['email'])) {
                $_SESSION['error_message'] = "Email đã tồn tại.";
                $this->redirect(BASE_URL . 'admin/users/create');
                return;
            }
            
            $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
            
            if ($userModel->createUserWithRole($data, $data['role_id'])) {
                $newUserId = $db->lastInsertId();
                (new ActivityLogModel())->log($_SESSION['user_id'], 'create_user', 'users', $newUserId, "Tạo người dùng mới: " . $data['username']);
                
                $_SESSION['success_message'] = "Đã thêm người dùng mới thành công.";
                $this->redirect(BASE_URL . 'admin/users');
            } else {
                $_SESSION['error_message'] = "Có lỗi xảy ra khi thêm người dùng.";
                $this->redirect(BASE_URL . 'admin/users/create');
            }
        }
    }
    
    /**
     * Cập nhật trạng thái khóa/mở khóa tài khoản
     */
    public function toggleStatus() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_POST['user_id'] ?? 0;
            $db = (new BaseModel())->db;
            
            // Không cho phép khóa chính mình
            if ($userId == $_SESSION['user_id']) {
                $_SESSION['error_message'] = "Không thể tự khóa tài khoản của bạn.";
                $this->redirect(BASE_URL . 'admin/users');
                return;
            }
            
            $stmt = $db->prepare("SELECT is_active FROM users WHERE user_id = ?");
            $stmt->execute([$userId]);
            $currentStatus = $stmt->fetchColumn();
            
            $newStatus = $currentStatus ? 0 : 1;
            
            $updateStmt = $db->prepare("UPDATE users SET is_active = ? WHERE user_id = ?");
            $updateStmt->execute([$newStatus, $userId]);
            
            $action = $newStatus ? 'unlock_user' : 'lock_user';
            (new ActivityLogModel())->log($_SESSION['user_id'], $action, 'users', $userId, ($newStatus ? "Mở khóa" : "Khóa") . " tài khoản ID: $userId");
            
            $_SESSION['success_message'] = $newStatus ? "Đã mở khóa tài khoản." : "Đã khóa tài khoản.";
            $this->redirect(BASE_URL . 'admin/users');
        }
    }
    
    /**
     * Đổi Role của user
     */
    public function updateRole() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_POST['user_id'] ?? 0;
            $roleId = $_POST['role_id'] ?? 4;
            $db = (new BaseModel())->db;
            
            if ($userId == $_SESSION['user_id']) {
                $_SESSION['error_message'] = "Không thể tự đổi quyền của bạn.";
                $this->redirect(BASE_URL . 'admin/users');
                return;
            }
            
            $updateStmt = $db->prepare("UPDATE users SET role_id = ? WHERE user_id = ?");
            $updateStmt->execute([$roleId, $userId]);
            
            (new ActivityLogModel())->log($_SESSION['user_id'], 'update_role', 'users', $userId, "Cập nhật quyền (Role ID: $roleId) cho người dùng ID: $userId");
            
            $_SESSION['success_message'] = "Đã phân quyền tài khoản thành công.";
            $this->redirect(BASE_URL . 'admin/users');
        }
    }
}
