<?php
// api/get_messages.php
session_start();
require_once __DIR__ . '/../config/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$conversation_id = $_GET['conversation_id'] ?? null;

if (!$conversation_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing conversation_id']);
    exit;
}

// Xác thực quyền vào đoạn chat
$stmt = $pdo->prepare("SELECT * FROM conversations WHERE id = ? AND (doctor_id = ? OR customer_id = ?)");
$stmt->execute([$conversation_id, $user_id, $user_id]);
if ($stmt->rowCount() === 0) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

// Lấy danh sách tin nhắn
$stmtMsg = $pdo->prepare("
    SELECT m.id, m.sender_id, m.content, m.created_at, u.full_name as sender_name 
    FROM messages m
    JOIN users u ON m.sender_id = u.user_id
    WHERE m.conversation_id = ? 
    ORDER BY m.created_at ASC
");
$stmtMsg->execute([$conversation_id]);
$messages = $stmtMsg->fetchAll();

echo json_encode(['success' => true, 'messages' => $messages]);
?>
