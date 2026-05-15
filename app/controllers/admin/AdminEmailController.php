<?php

require_once __DIR__ . '/../BaseController.php';
require_once __DIR__ . '/../../Models/BaseModel.php';

class AdminEmailController extends BaseController {
    
    public function __construct() {
        if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 1) { // Tech Admin only
            $this->redirect(BASE_URL . 'auth/login');
        }
    }

    public function index() {
        $db = (new BaseModel())->db;
        
        $q = $_GET['q'] ?? '';
        $type = $_GET['type'] ?? '';
        $status = $_GET['status'] ?? '';
        
        $where = ["1=1"];
        $params = [];
        
        if ($q !== '') {
            $where[] = "(recipient_email LIKE :q OR subject LIKE :q OR order_id = :qid)";
            $params['q'] = "%$q%";
            $params['qid'] = is_numeric($q) ? $q : -1;
        }
        
        if ($type !== '') {
            $where[] = "type = :type";
            $params['type'] = $type;
        }

        if ($status !== '') {
            $where[] = "status = :status";
            $params['status'] = $status;
        }
        
        $whereSql = implode(' AND ', $where);
        
        $sql = "SELECT * FROM email_logs WHERE $whereSql ORDER BY sent_at DESC LIMIT 100";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->loadView('admin/emails', [
            'logs' => $logs,
            'pageTitle' => 'Quản lý Email',
            'q' => $q,
            'type' => $type,
            'status' => $status
        ]);
    }
}
