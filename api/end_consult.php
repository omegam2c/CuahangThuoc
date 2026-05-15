<?php
// api/end_consult.php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../config/pusher_config.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$conversation_id = $_POST['conversation_id'] ?? null;

if (!$conversation_id) {
    http_response_code(400); 
    echo json_encode(['error' => 'Missing conversation_id']); 
    exit;
}

// Xác thực quyền (Chỉ người trong cuộc mới được phép xoá)
$stmt = $pdo->prepare("SELECT doctor_id FROM conversations WHERE id = ? AND (doctor_id = ? OR customer_id = ?)");
$stmt->execute([$conversation_id, $user_id, $user_id]);
$convo = $stmt->fetch();

if (!$convo) {
    http_response_code(403);
    echo json_encode(['error' => 'Phòng tư vấn không tồn tại hoặc bạn không có quyền.']);
    exit;
}

// Gửi thông báo huỷ phòng cho người kia qua Pusher trước khi xoá DB
try {
    $pusher = getPusher();
    $pusher->trigger('private-chat-' . $conversation_id, 'conversation-ended', [
        'message' => 'Phiên tư vấn đã được kết thúc bởi đối tác.'
    ]);
} catch (Exception $e) {
    // Ignore Pusher errors on end
}

// Xoá toàn bộ lịch sử (Nhờ FOREIGN KEY ON DELETE CASCADE ở bảng messages)
$stmtDelete = $pdo->prepare("DELETE FROM conversations WHERE id = ?");
$stmtDelete->execute([$conversation_id]);

// Free Bác sĩ bằng cách Update is_available = 1
$pdo->prepare("UPDATE doctor_status SET is_available = 1 WHERE doctor_id = ?")->execute([$convo['doctor_id']]);

echo json_encode(['success' => true]);
?>
