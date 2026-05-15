<?php

require_once __DIR__ . '/../BaseController.php';
require_once __DIR__ . '/../../Models/BaseModel.php';

class AdminLogController extends BaseController {
    
    public function __construct() {
        parent::__construct();
        if (!isset($_SESSION['user_id'])) {
            $this->redirect(BASE_URL . 'auth/login');
        }
        $this->checkPermission('view_audit_logs'); // Ref point 🔴
    }
    
    public function index() {
        $this->checkPermission('view_audit_logs');
        $db = (new BaseModel())->db;
        
        $sql = "SELECT l.*, u.username, u.full_name 
                FROM activity_logs l
                LEFT JOIN users u ON l.user_id = u.user_id
                ORDER BY l.created_at DESC 
                LIMIT 500";
                
        $stmt = $db->query($sql);
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->loadView('admin/logs/index', [
            'logs' => $logs,
            'pageTitle' => 'Nhật ký hoạt động hệ thống'
        ]);
    }
}
