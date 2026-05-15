<?php

class BaseController {
    public function __construct() {
        // 1. Global Security Headers (Ref point 🟡 - Security Headers)
        header("X-Content-Type-Options: nosniff");
        header("X-Frame-Options: SAMEORIGIN");
        header("X-XSS-Protection: 1; mode=block");
        header("Referrer-Policy: strict-origin-when-cross-origin");

        // Khởi tạo CSRF token nếu chưa có
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 2. Kiểm tra Session Timeout (Ref point 7 - Session security)
        if (isset($_SESSION['user_id'])) {
            $timeout = (int)env('SESSION_LIFETIME', 120) * 60;
            if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
                session_unset();
                session_destroy();
                $this->redirect(BASE_URL . 'auth/login?timeout=1');
            }
            $_SESSION['last_activity'] = time();
        }

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        // 3. Exception Handler (Ref point 🟡 - Exception Leak)
        set_exception_handler(function($e) {
            error_log($e->getMessage());
            $msg = env('APP_DEBUG', false) ? $e->getMessage() : 'Đã có lỗi hệ thống xảy ra. Vui lòng thử lại sau.';
            
            if (!headers_sent()) {
                http_response_code(500);
                header('Content-Type: application/json');
            }
            echo json_encode(['success' => false, 'message' => $msg]);
            exit;
        });
    }

    /**
     * Kiểm tra CSRF Token cho các request POST
     */
    protected function validateCsrf() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
            if (!hash_equals($_SESSION['csrf_token'], $token)) {
                $this->json(['success' => false, 'message' => 'Lỗi bảo mật: CSRF token không hợp lệ.'], 403);
                exit;
            }
        }
    }

    /**
     * Trả về HTML input chứa CSRF token
     */
    protected function csrfField() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';
    }

    /**
     * Kiểm tra quyền truy cập (RBAC)
     */
    protected function checkPermission($permissionName) {
        if (!$this->hasPermission($permissionName)) {
            $this->denyAccess();
        }
    }

    protected function hasPermission($permissionName) {
        if (!isset($_SESSION['user_id'])) return false;
        
        require_once __DIR__ . '/../Models/UserModel.php';
        $userModel = new UserModel();
        return $userModel->hasPermission($_SESSION['user_id'], $permissionName);
    }

    protected function denyAccess() {
        $_SESSION['error_message'] = "Bạn không có quyền thực hiện hành động này.";
        $this->redirect(BASE_URL);
        exit;
    }

    /**
     * Yêu cầu các Role cụ thể
     */
    protected function requireRole($roles = []) {
        if (!isset($_SESSION['role_id']) || !in_array($_SESSION['role_id'], $roles)) {
            $_SESSION['error_message'] = "Truy cập bị từ chối.";
            $this->redirect(BASE_URL);
        }
    }

    /**
     * Load view với dữ liệu
     */
    protected function loadView($viewName, $data = []) {
        // Thêm CSRF helper vào data view
        $data['csrf_field'] = $this->csrfField();
        
        extract($data);
        $viewPath = __DIR__ . '/../views/' . $viewName . '.php';
        if (file_exists($viewPath)) {
            require_once $viewPath;
        } else {
            die("View không tồn tại: " . $viewPath);
        }
    }

    /**
     * Redirect to a URL
     */
    protected function redirect($url) {
        header("Location: " . $url);
        exit();
    }

    /**
     * Trả về JSON cho API (Chuẩn hóa Response Format - Ref point 📱)
     */
    protected function json($data, $code = 200) {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        
        // Cấu trúc Response chuẩn hóa: { success, message, data/payload }
        $response = [
            'success' => $code >= 200 && $code < 300,
            'message' => $data['message'] ?? ($code == 200 ? 'Thành công' : 'Có lỗi xảy ra'),
            'payload' => $data['payload'] ?? $data
        ];
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }
}
