<?php
// api/send_message.php
session_start();
require_once __DIR__ . '/../app/Helpers/functions.php';
loadEnv(__DIR__ . '/../.env');
require_once __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../config/pusher_config.php';
require_once __DIR__ . '/bot_service.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Bắt buộc xác thực người dùng đã đăng nhập (điều chỉnh key session theo dự án thực tế)
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$sender_id = $_SESSION['user_id'];
$conversation_id = $_POST['conversation_id'] ?? null;
$content = $_POST['content'] ?? '';

if (!$conversation_id || trim($content) === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Missing data']);
    exit;
}

// Kiểm tra xem người dùng có phải là thành viên trong conversation này không
$stmt = $pdo->prepare("SELECT * FROM conversations WHERE id = ? AND (doctor_id = ? OR customer_id = ?)");
$stmt->execute([$conversation_id, $sender_id, $sender_id]);
if ($stmt->rowCount() === 0) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

// Lưu tin nhắn vào cơ sở dữ liệu
$stmtMsg = $pdo->prepare("INSERT INTO messages (conversation_id, sender_id, content) VALUES (?, ?, ?)");
$stmtMsg->execute([$conversation_id, $sender_id, $content]);
$message_id = $pdo->lastInsertId();

$msgData = [
    'id' => $message_id,
    'conversation_id' => $conversation_id,
    'sender_id' => $sender_id,
    'content' => htmlspecialchars($content),
    'created_at' => date('Y-m-d H:i:s')
];

// Phát sự kiện qua Pusher cho channel Private (để bảo mật)
$pusher = getPusher();
$channelName = 'private-chat-' . $conversation_id;
$pusher->trigger($channelName, 'new-message', $msgData);

// NẾU ĐANG CHAT VỚI BOT (doctor_id = 1), GỌI API GEMINI
$stmtDoc = $pdo->prepare("SELECT doctor_id FROM conversations WHERE id = ?");
$stmtDoc->execute([$conversation_id]);
$docId = $stmtDoc->fetchColumn();

if ($docId == 1 && $sender_id != 1) {
    // Để tránh UI bị treo, có thể đóng session
    session_write_close();
    
    $bot = new AntigravityBot($pdo);
    $botResponse = $bot->processMessage($conversation_id, $content);
    
    // Lưu tin nhắn của Bot
    $stmtBotMsg = $pdo->prepare("INSERT INTO messages (conversation_id, sender_id, content) VALUES (?, 1, ?)");
    $stmtBotMsg->execute([$conversation_id, $botResponse]);
    $botMsgId = $pdo->lastInsertId();
    
    $botMsgData = [
        'id' => $botMsgId,
        'conversation_id' => $conversation_id,
        'sender_id' => 1,
        'content' => htmlspecialchars($botResponse),
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    // Phát tin nhắn của Bot qua Pusher
    $pusher->trigger($channelName, 'new-message', $botMsgData);
}

echo json_encode(['success' => true, 'message' => $msgData]);
?>
