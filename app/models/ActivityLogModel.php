<?php

require_once __DIR__ . '/BaseModel.php';

class ActivityLogModel extends BaseModel {
    
    /**
     * Ghi log hoạt động
     */
    public function log($userId, $action, $tableName = null, $recordId = null, $description = null) {
        $sql = "INSERT INTO activity_logs (user_id, action, table_name, record_id, description, ip_address, user_agent) 
                VALUES (:user_id, :action, :table, :record_id, :desc, :ip, :ua)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'user_id' => $userId,
            'action' => $action,
            'table' => $tableName,
            'record_id' => $recordId,
            'desc' => $description,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
            'ua' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
    }
}
