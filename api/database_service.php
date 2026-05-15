<?php
// api/database_service.php
require_once __DIR__ . '/../config/db_connect.php';

/**
 * Core Algorithm: Find the next available doctor using Round-Robin and Status Filtering.
 * 
 * @param PDO $pdo
 * @param int $requestId
 * @return int|null Returns doctor_id or null if exhausted.
 */
function findNextAvailableDoctor(PDO $pdo, $requestId) {
    // Use SAVEPOINT so this function is safe both inside and outside a transaction
    $inTransaction = $pdo->inTransaction();
    if (!$inTransaction) {
        $pdo->beginTransaction();
    }

    try {
        // Lọc Bác sĩ đang Online (có Ping trong 60s qua), Rảnh rỗi và Chưa từng từ chối request này
        $stmt = $pdo->prepare("
            SELECT ds.doctor_id 
            FROM doctor_status ds
            JOIN users u ON ds.doctor_id = u.user_id
            WHERE ds.is_online = 1 
              AND ds.is_available = 1
              AND u.role_id = 2
              AND ds.last_heartbeat >= NOW() - INTERVAL 1 MINUTE
              AND ds.doctor_id NOT IN (
                  SELECT doctor_id FROM request_rejections WHERE request_id = ?
              )
            ORDER BY ds.last_assigned_at ASC
            LIMIT 1
            FOR UPDATE
        ");
        $stmt->execute([$requestId]);
        $row = $stmt->fetch();

        if ($row) {
            $doctorId = $row['doctor_id'];
            
            // Xếp bác sĩ này xuống cuối hàng chờ cho lượt sau
            $updateStmt = $pdo->prepare("UPDATE doctor_status SET last_assigned_at = NOW() WHERE doctor_id = ?");
            $updateStmt->execute([$doctorId]);
            
            // Cập nhật người phụ trách hiện tại của Request
            $reqStmt = $pdo->prepare("UPDATE consultation_requests SET current_doctor_id = ? WHERE id = ?");
            $reqStmt->execute([$doctorId, $requestId]);
            
            if (!$inTransaction) $pdo->commit();
            return $doctorId;
        }

        if (!$inTransaction) $pdo->commit();
        return null;
    } catch (Exception $e) {
        if (!$inTransaction) $pdo->rollBack();
        throw $e;
    }
}
?>
