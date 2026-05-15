<?php

require_once __DIR__ . '/../BaseController.php';

class AdminSettingsController extends BaseController {
    
    private string $settingsFile;

    public function __construct() {
        parent::__construct();
        if (!isset($_SESSION['user_id'])) {
            $this->redirect(BASE_URL . 'auth/login');
        }
        $this->checkPermission('system_config'); // Ref point 🔴
        $this->settingsFile = __DIR__ . '/../../storage/settings.json'; // Ref point 🟡 - Private Storage
    }

    private function loadSettings(): array {
        if (file_exists($this->settingsFile)) {
            $content = file_get_contents($this->settingsFile);
            $data = json_decode($content, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $data;
            }
        }
        return [
            'site_name' => 'Nhà thuốc 1985',
            'email'     => 'admin@1985.com',
            'lang'      => 'vi',
            'notify'    => true,
            'twofa'     => false,
        ];
    }

    private function saveSettingsData(array $data): void {
        $dir = dirname($this->settingsFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($this->settingsFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    public function index() {
        $settings = $this->loadSettings();
        $this->loadView('admin/settings/index', [
            'pageTitle' => 'Cài đặt hệ thống',
            'settings' => $settings
        ]);
    }

    public function save() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf(); // CSRF Protection (Ref point 🟡)

            $data = [
                'site_name' => htmlspecialchars($_POST['site_name'] ?? 'Nhà thuốc 1985'), // XSS Protection
                'email'     => filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL),
                'lang'      => $_POST['lang'] ?? 'vi',
                'notify'    => isset($_POST['notify']),
                'twofa'     => isset($_POST['twofa']),
            ];

            $this->saveSettingsData($data);
            
            // Audit Log
            require_once __DIR__ . '/../../Models/ActivityLogModel.php';
            (new ActivityLogModel())->log($_SESSION['user_id'], 'update_settings', 'settings', null, "Cập nhật cấu hình hệ thống.");

            $_SESSION['flash_success'] = "Đã lưu cấu hình thành công!";
            $this->redirect(BASE_URL . 'admin/settings');
        }
    }
}
