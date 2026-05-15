<?php
// api/heartbeat.php
session_start();
require_once __DIR__ . '/../config/db_connect.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role_id']) || $_SESSION['role_id'] != 2) {
    http_response_code(401);
    exit;
}

$doctorId = $_SESSION['user_id'];

// Liên tục đánh dấu Online (Update last_heartbeat)
// Nếu chưa có row trong DB thì INSERT
$stmt = $pdo->prepare("
    INSERT INTO doctor_status (doctor_id, is_online, is_available, last_heartbeat) 
    VALUES (?, 1, 1, NOW())
    ON DUPLICATE KEY UPDATE 
    is_online = 1, last_heartbeat = NOW()
");
$stmt->execute([$doctorId]);

echo json_encode(['success' => true, 'message' => 'Heartbeat updated']);
?>
