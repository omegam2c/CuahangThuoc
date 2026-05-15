<?php
// api/pusher_auth.php
session_start();
require_once __DIR__ . '/../config/pusher_config.php';
require_once __DIR__ . '/../config/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo "Forbidden";
    exit;
}

$user_id = $_SESSION['user_id'];
$channel_name = $_POST['channel_name'] ?? '';
$socket_id = $_POST['socket_id'] ?? '';

// 1. Định dạng channel Chat: private-chat-{conversation_id}
if (preg_match('/^private-chat-(\d+)$/', $channel_name, $matches)) {
    $conversation_id = $matches[1];
    $stmt = $pdo->prepare("SELECT * FROM conversations WHERE id = ? AND (doctor_id = ? OR customer_id = ?)");
    $stmt->execute([$conversation_id, $user_id, $user_id]);
    
    if ($stmt->rowCount() > 0) {
        $pusher = getPusher();
        try { echo $pusher->authorizeChannel($channel_name, $socket_id); exit; } catch(Exception $e) { http_response_code(500); exit; }
    }
}

// 2. Định dạng channel Thông báo Bác Sĩ: private-doctor-{id}
if (preg_match('/^private-doctor-(\d+)$/', $channel_name, $matches)) {
    if ($user_id == $matches[1] && isset($_SESSION['role_id']) && $_SESSION['role_id'] == 2) { // Chỉ chính mình (phải là Dược sĩ)
        $pusher = getPusher();
        try { echo $pusher->authorizeChannel($channel_name, $socket_id); exit; } catch(Exception $e) { http_response_code(500); exit; }
    }
}

// 3. Định dạng channel Thông báo Khách Hàng: private-customer-{id}
if (preg_match('/^private-customer-(\d+)$/', $channel_name, $matches)) {
    if ($user_id == $matches[1] && isset($_SESSION['role_id']) && $_SESSION['role_id'] != 2) {
        $pusher = getPusher();
        try { echo $pusher->authorizeChannel($channel_name, $socket_id); exit; } catch(Exception $e) { http_response_code(500); exit; }
    }
}

http_response_code(403);
echo "Forbidden";
?>
