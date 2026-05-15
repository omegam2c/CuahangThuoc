<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/UserModel.php';
require_once __DIR__ . '/../Services/MailerService.php';
require_once __DIR__ . '/../Services/Emails/BaseEmail.php';
require_once __DIR__ . '/../Services/Emails/PasswordResetEmail.php';
require_once __DIR__ . '/../Models/ActivityLogModel.php';

use App\Services\MailerService;
use App\Services\Emails\PasswordResetEmail;

class AuthController extends BaseController {
    private $userModel;
    private $mailerService;

    public function __construct() {
        parent::__construct();
        $this->userModel = new UserModel();
        $this->mailerService = new MailerService();
        $this->activityLog = new ActivityLogModel();
    }

    /**
     * Hiển thị trang đăng nhập
     */
    public function login() {
        if (isset($_SESSION['user_id'])) {
            $this->redirect(BASE_URL . 'home');
        }

        // Generate simple captcha
        $captchaStr = substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 5);
        $_SESSION['captcha'] = $captchaStr;

        $data = [
            'pageTitle' => 'Đăng nhập — Nhà thuốc 1985',
            'errors' => $_SESSION['login_errors'] ?? null,
            'success' => $_SESSION['success_message'] ?? null,
            'captcha' => $captchaStr
        ];
        unset($_SESSION['login_errors'], $_SESSION['success_message']);
        
        $this->loadView('auth/login', $data);
    }

    /**
     * Xử lý đăng nhập
     */
    public function handleLogin() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf(); // Check CSRF (Ref point 🛡️)
            

            $credential = trim($_POST['credential'] ?? '');
            $password = $_POST['password'] ?? '';
            $captchaInput = strtoupper(trim($_POST['captcha'] ?? ''));
            $errors = [];

            if (empty($captchaInput) || $captchaInput !== ($_SESSION['captcha'] ?? '')) {
                $errors[] = "Mã xác thực không chính xác.";
            }

            if (empty($credential) || empty($password)) {
                $errors[] = "Vui lòng nhập đầy đủ thông tin.";
            }

            if (empty($errors)) {
                $user = $this->userModel->getUserByEmailOrUsername($credential);

                if ($user && password_verify($password, $user['password_hash'])) {
                    if (!$user['is_active']) {
                        $errors[] = "Tài khoản của bạn đã bị khóa.";
                    } else {
                        // Reset rate limit khi login thành công
                        unset($_SESSION['login_attempts'], $_SESSION['last_attempt_time']);
                        
                        // Login thành công... (giữ logic cũ)
                        $_SESSION['user_id'] = $user['user_id'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['full_name'] = $user['full_name'];
                        $_SESSION['role_id'] = $user['role_id'];
                        $_SESSION['user'] = [
                            'id' => $user['user_id'],
                            'name' => $user['full_name'],
                            'role' => $user['role_id']
                        ];

                        // Cập nhật lần đăng nhập cuối
                        $this->userModel->updateLastLogin($user['user_id']);

                        // Log Login thành công
                        $this->activityLog->log($user['user_id'], 'login', 'users', $user['user_id'], "Đăng nhập hệ thống thành công");

                        // Phân luồng Dashboard theo Role: Tất cả về Home (Ref point 🏠)
                        $this->redirect(BASE_URL . 'home');
                    }
                } else {
                    // Log Login thất bại
                    $user_failed = $this->userModel->getUserByEmailOrUsername($credential);
                    if ($user_failed) {
                        $this->activityLog->log($user_failed['user_id'], 'login_failed', 'users', $user_failed['user_id'], "Đăng nhập thất bại (sai mật khẩu)");
                    }
                    
                    // Increment attempts on failure
                    $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
                    $_SESSION['last_attempt_time'] = time();
                }
            }

            $_SESSION['login_errors'] = $errors;
            $this->redirect(BASE_URL . 'auth/login');
        }
    }

    /**
     * Hiển thị trang đăng ký
     */
    public function register() {
        $data = [
            'pageTitle' => 'Đăng ký tài khoản — Nhà thuốc 1985',
            'errors' => $_SESSION['register_errors'] ?? null,
            'success' => $_SESSION['success_message'] ?? null,
            'oldInput' => $_SESSION['old_input'] ?? []
        ];
        unset($_SESSION['register_errors'], $_SESSION['success_message'], $_SESSION['old_input']);

        $this->loadView('auth/register', $data);
    }

    /**
     * Xử lý đăng ký
     */
    public function handleRegister() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = [];
            $username = trim($_POST['username'] ?? '');
            $fullname = trim($_POST['fullName'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $phone = trim($_POST['phone'] ?? '');

            if (empty($username) || empty($fullname) || empty($email) || empty($password)) {
                $errors[] = "Vui lòng điền các thông tin bắt buộc.";
            }

            if (strlen($username) > 50) {
                $errors[] = "Tên đăng nhập không được quá 50 ký tự.";
            }
            
            if (strlen($fullname) > 100) {
                $errors[] = "Họ tên không được quá 100 ký tự.";
            }

            if (strlen($email) > 100) {
                $errors[] = "Email không được quá 100 ký tự.";
            }
            
            if (!preg_match('/^(0|84)(3|5|7|8|9)([0-9]{8})$/', $phone)) {
                $errors[] = "Số điện thoại không hợp lệ. Vui lòng nhập đúng định dạng (VD: 0912345678).";
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !str_ends_with(strtolower($email), '@gmail.com')) {
                $errors[] = "Vui lòng sử dụng email @gmail.com hợp lệ.";
            }
            
            if (strlen($password) < 6 || strlen($password) > 100) {
                $errors[] = "Mật khẩu phải từ 6 đến 100 ký tự.";
            }

            if (!preg_match('/[A-Z]/', $password) || !preg_match('/[^a-zA-Z0-9]/', $password)) {
                $errors[] = "Mật khẩu phải có ít nhất 1 chữ in hoa và 1 ký tự đặc biệt.";
            }

            if ($this->userModel->checkUsernameExists($username)) $errors[] = "Tên đăng nhập đã tồn tại.";
            if ($this->userModel->checkEmailExists($email)) $errors[] = "Email này đã được sử dụng.";

            if (empty($errors)) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $data = [
                    'role_id' => 4, // Mặc định là Khách hàng
                    'username' => $username,
                    'full_name' => $fullname,
                    'email' => $email,
                    'password_hash' => $hashedPassword,
                    'phone' => $phone
                ];
                
                if ($this->userModel->createUser($data)) {
                    $_SESSION['success_message'] = "Đăng ký thành công! Hãy đăng nhập.";
                    $this->redirect(BASE_URL . 'auth/login');
                }
            }
            
            $_SESSION['register_errors'] = $errors;
            $_SESSION['old_input'] = $_POST;
            $this->redirect(BASE_URL . 'auth/register');
        }
    }

    /**
     * Đăng xuất
     */
    public function logout() {
        if (isset($_SESSION['user_id'])) {
            (new ActivityLogModel())->log($_SESSION['user_id'], 'logout', 'users', $_SESSION['user_id'], "Đăng xuất khỏi hệ thống");
        }

        // Xóa toàn bộ biến session
        $_SESSION = array();

        // Xóa cookie session nếu có
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        // Hủy session
        session_destroy();

        // Chuyển hướng về trang chủ thay vì trang login
        $this->redirect(BASE_URL . 'home');
    }

    /**
     * Quên mật khẩu
     */
    public function forgotPassword() {
        $this->loadView('auth/forgot_password', [
            'pageTitle' => 'Quên mật khẩu',
            'errors' => $_SESSION['otp_errors'] ?? null
        ]);
        unset($_SESSION['otp_errors']);
    }

    /**
     * Gửi OTP
     */
    public function sendOtp() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');

            $user = $this->userModel->getUserByEmailOrUsername($email);
            if (!$user) {
                $_SESSION['otp_errors'] = ["Email không tồn tại trong hệ thống."];
                $this->redirect(BASE_URL . 'auth/forgotPassword');
            }

            $otp = rand(100000, 999999);
            $this->userModel->saveOTP($email, $otp);

            // Sử dụng Mailer Service với Template Method Pattern
            $emailObj = new PasswordResetEmail($otp, $user['full_name']);
            $isSent = $this->mailerService->dispatch($emailObj, $email, $user['full_name']);

            if ($isSent) {
                $_SESSION['reset_email'] = $email;
                $_SESSION['success_message'] = "Mã OTP đã được gửi đến email. Vui lòng kiểm tra hộp thư.";
                $this->redirect(BASE_URL . 'auth/verifyOtp');
            } else {
                $_SESSION['otp_errors'] = ["Không thể gửi email OTP. Vui lòng thử lại sau."];
                $this->redirect(BASE_URL . 'auth/forgotPassword');
            }
        }
    }

    /**
     * Xác thực OTP
     */
    public function verifyOtp() {
        if (!isset($_SESSION['reset_email'])) {
            $this->redirect(BASE_URL . 'auth/forgotPassword');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $otp_entered = $_POST['otp_code'] ?? '';
            $email = $_SESSION['reset_email'];

            if ($this->userModel->verifyOTP($email, $otp_entered)) {
                $_SESSION['otp_verified'] = true;
                $this->redirect(BASE_URL . 'auth/resetPassword');
            } else {
                $_SESSION['otp_errors'] = ["Mã OTP không chính xác hoặc đã hết hạn."];
                $this->redirect(BASE_URL . 'auth/verifyOtp');
            }
        }

        $this->loadView('auth/verify_otp', [
            'pageTitle' => 'Xác thực OTP',
            'errors' => $_SESSION['otp_errors'] ?? null,
            'success' => $_SESSION['success_message'] ?? null
        ]);
        unset($_SESSION['otp_errors'], $_SESSION['success_message']);
    }

    /**
     * Đặt lại mật khẩu
     */
    public function resetPassword() {
        if (!isset($_SESSION['reset_email']) || !isset($_SESSION['otp_verified'])) {
            $this->redirect(BASE_URL . 'auth/forgotPassword');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            if (strlen($new_password) < 6 || !preg_match('/[A-Z]/', $new_password) || !preg_match('/[^a-zA-Z0-9]/', $new_password)) {
                $_SESSION['reset_errors'] = ["Mật khẩu mới không đủ mạnh."];
                $this->redirect(BASE_URL . 'auth/resetPassword');
            }

            if ($new_password !== $confirm_password) {
                $_SESSION['reset_errors'] = ["Mật khẩu xác nhận không khớp."];
                $this->redirect(BASE_URL . 'auth/resetPassword');
            }

            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $email = $_SESSION['reset_email'];

            if ($this->userModel->updatePassword($email, $hashed)) {
                unset($_SESSION['reset_email'], $_SESSION['otp_verified']);
                $_SESSION['success_message'] = "Đã đặt lại mật khẩu thành công.";
                $this->redirect(BASE_URL . 'auth/login');
            } else {
                $_SESSION['reset_errors'] = ["Có lỗi xảy ra. Vui lòng thử lại."];
                $this->redirect(BASE_URL . 'auth/resetPassword');
            }
        }

        $this->loadView('auth/reset_password', [
            'pageTitle' => 'Đặt lại mật khẩu',
            'errors' => $_SESSION['reset_errors'] ?? null
        ]);
        unset($_SESSION['reset_errors']);
    }
}
