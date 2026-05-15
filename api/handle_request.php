<?php
// api/handle_request.php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../app/Helpers/functions.php';
loadEnv(__DIR__ . '/../.env');
require_once __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/database_service.php';
require_once __DIR__ . '/notification_service.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if ($_SESSION['role_id'] == 2) {
    http_response_code(403);
    echo json_encode(['error' => 'Dược sĩ không thể tạo yêu cầu tư vấn']);
    exit;
}

$customerId = $_SESSION['user_id'];
$action = $_POST['action'] ?? 'start';

if ($action === 'start') {
    // Tạo mã Request vào Queue
    $stmt = $pdo->prepare("INSERT INTO consultation_requests (customer_id, status) VALUES (?, 'pending')");
    $stmt->execute([$customerId]);
    $requestId = $pdo->lastInsertId();

    $doctorId = findNextAvailableDoctor($pdo, $requestId);

    if ($doctorId) {
        notifyDoctorIncomingRequest($doctorId, [
            'request_id' => $requestId,
            'customer_id' => $customerId,
            'message' => 'Bạn có 1 yêu cầu tư vấn mới'
        ]);
        echo json_encode(['success' => true, 'request_id' => $requestId, 'status' => 'routing']);
    } else {
        $pdo->prepare("UPDATE consultation_requests SET status = 'exhausted' WHERE id = ?")->execute([$requestId]);
        echo json_encode(['success' => false, 'error' => 'Chưa có bác sĩ trực. Vui lòng thử lại sau.']);
    }
} elseif ($action === 'advance') {
    // Front-end Khách hàng gửi lên khi hết 30s JS Timout mà bác sĩ chưa Accept
    $requestId = $_POST['request_id'] ?? null;
    
    if (!$requestId) {
        http_response_code(400); echo json_encode(['error' => 'Missing request_id']); exit;
    }

    $stmt = $pdo->prepare("SELECT current_doctor_id FROM consultation_requests WHERE id = ? AND status = 'pending'");
    $stmt->execute([$requestId]);
    $req = $stmt->fetch();
    
    if ($req && $req['current_doctor_id']) {
        // Phạt bác sĩ đã không phản hồi (timeout)
        $pdo->prepare("INSERT INTO request_rejections (request_id, doctor_id, reason) VALUES (?, ?, 'timeout')")
            ->execute([$requestId, $req['current_doctor_id']]);
            
        // Routing qua người tiếp theo
        $nextDoctorId = findNextAvailableDoctor($pdo, $requestId);
        if ($nextDoctorId) {
            notifyDoctorIncomingRequest($nextDoctorId, [
                'request_id' => $requestId,
                'customer_id' => $customerId,
                'message' => 'Bạn có 1 yêu cầu tư vấn mới do bác sĩ tuyến trước bận.'
            ]);
            echo json_encode(['success' => true, 'status' => 'routing_next']);
        } else {
            $pdo->prepare("UPDATE consultation_requests SET status = 'exhausted' WHERE id = ?")->execute([$requestId]);
            echo json_encode(['success' => false, 'status' => 'exhausted', 'error' => 'Tất cả bác sĩ đều đang bận.']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Luồng không hợp lệ.']);
    }
}
?>
