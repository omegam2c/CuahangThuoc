<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "auth/login");
    exit;
}

require_once __DIR__ . '/../config/pusher_config.php';
$conversation_id = $_GET['id'] ?? null; 

// Auto-Reconnect Logic: Nếu User vào /consult mà không có ID, tự động tìm kiếm xem họ có phòng chat hiện hữu không
if (!$conversation_id) {
    require_once __DIR__ . '/../config/db_connect.php';
    $userId = $_SESSION['user_id'];
    $isDoctorQuery = (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 2);
    
    $stmtFind = $pdo->prepare("SELECT id FROM conversations WHERE " . ($isDoctorQuery ? "doctor_id" : "customer_id") . " = ? ORDER BY created_at DESC LIMIT 1");
    $stmtFind->execute([$userId]);
    $activeConvo = $stmtFind->fetch();
    
    if ($activeConvo) {
        // Có phòng chưa xoá -> Ép văng vào phòng đó
        header("Location: " . BASE_URL . "consult?id=" . $activeConvo['id']);
        exit;
    } else {
        // Nếu là Bác sĩ/Dược sĩ mà ko có phòng, đẩy về Dashboard để chờ
        if ($isDoctorQuery) {
            header("Location: " . BASE_URL . "doctor/dashboard");
            exit;
        } else {
            // KHÁCH HÀNG: Tạo phòng chat tự động với Bot (doctor_id = 1)
            $botId = 1;
            $stmtInsert = $pdo->prepare("INSERT INTO conversations (doctor_id, customer_id) VALUES (?, ?)");
            $stmtInsert->execute([$botId, $userId]);
            $newConvoId = $pdo->lastInsertId();
            
            // Lời chào mở đầu từ Bot
            $welcomeMsg = "Hãy viết nguyên nhân bệnh của bạn xuống dưới đây để tôi có thể gợi ý những thuốc hợp lí nhất nhé.";
            $stmtBotMsg = $pdo->prepare("INSERT INTO messages (conversation_id, sender_id, content) VALUES (?, ?, ?)");
            $stmtBotMsg->execute([$newConvoId, 1, $welcomeMsg]);
            
            header("Location: " . BASE_URL . "consult?id=" . $newConvoId);
            exit;
        }
    }
}

$partner_name = "Tư vấn viên Chuyên nghiệp";
$partner_id = null;
$partner_initial = "T";

if ($conversation_id) {
    require_once __DIR__ . '/../config/db_connect.php';
    $stmt = $pdo->prepare("
        SELECT c.*, doc.full_name as doctor_name, cus.full_name as customer_name 
        FROM conversations c 
        JOIN users doc ON c.doctor_id = doc.user_id 
        JOIN users cus ON c.customer_id = cus.user_id 
        WHERE c.id = ?
    ");
    $stmt->execute([$conversation_id]);
    $convo = $stmt->fetch();
    
    if ($convo) {
        $isDoctor = (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 2);
        
        // Cấp quyền: Chỉ cho phép người trong cuộc (Bác sĩ hoặc Khách hàng)
        if ($convo['doctor_id'] != $_SESSION['user_id'] && $convo['customer_id'] != $_SESSION['user_id']) {
            die("Hội thoại không tồn tại hoặc bạn không có quyền truy cập.");
        }

        if ($isDoctor) {
            $partner_name = $convo['customer_name']; // Sẽ show tên Khách cho BS
            $partner_id = $convo['customer_id'];
        } else {
            if ($convo['doctor_id'] == 1) {
                $partner_name = "Trợ lý Ảo Nhà Thuốc 1985";
            } else {
                $partner_name = "BS. " . $convo['doctor_name']; // Show tên BS cho Khách
            }
            $partner_id = $convo['doctor_id'];
        }
        $partner_initial = mb_substr(str_replace('BS. ', '', $partner_name), 0, 1, 'UTF-8');
        
        $homeUrl = $isDoctor ? BASE_URL . 'doctor/dashboard' : BASE_URL . 'home';
    } else {
        die("Hội thoại không tồn tại hoặc bạn không có quyền truy cập.");
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ Thống Tư Vấn Nhà Thuốc</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/chat.css">
    
    <script>
        window.CURRENT_USER_ID = <?php echo $_SESSION['user_id']; ?>;
        window.CURRENT_CONVERSATION_ID = <?php echo $conversation_id ? $conversation_id : 'null'; ?>;
        window.PUSHER_KEY = '<?php echo constant("PUSHER_APP_KEY") !== "" ? PUSHER_APP_KEY : ""; ?>';
        window.PUSHER_CLUSTER = '<?php echo PUSHER_APP_CLUSTER; ?>';
        window.BASE_URL = '<?= BASE_URL ?>';
    </script>
</head>
<body style="background: #eef2f5;">

<?php if (!$conversation_id): ?>
    <!-- UI cũ không còn dùng nữa do khách hàng luôn có conversation với Bot -->
    <script>
        window.location.href = '<?= BASE_URL ?>';
    </script>


<?php else: ?>
    <!-- ============================================== -->
    <!-- PHÒNG CHAT CHÍNH THỨC (Sau khi khớp thành công)  -->
    <!-- ============================================== -->
    <div class="chat-container">
        <!-- Sidebar -->
        <div class="chat-sidebar">
            <div class="sidebar-header">
                <h2>Khung chat</h2>
            </div>
            <div class="conversation-list">
                <div class="conversation-item active">
                    <div class="avatar"><?php echo strtoupper($partner_initial); ?></div>
                    <div class="conversation-info">
                        <h3><?php echo htmlspecialchars($partner_name); ?></h3>
                        <p>Đang kết nối để tư vấn...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Khung chat chính -->
        <div class="chat-main">
            <div class="chat-header">
                <div class="header-user-info">
                    <div class="avatar" style="width: 40px; height: 40px; font-size: 16px;"><?php echo strtoupper($partner_initial); ?></div>
                    <h3><?php echo htmlspecialchars($partner_name); ?></h3>
                </div>
                <div class="header-actions">
                    <!-- Nút Quay về -->
                    <a href="<?php echo $homeUrl; ?>" title="Trở về Trang Chính" style="color: #64748b; margin-right: 15px; display: inline-flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 50%; background: #f1f5f9; text-decoration: none; transition: background 0.2s;">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
                    </a>
                    
                    <!-- Nút Kết Thúc Tư Vấn (Cancel/Delete Chat) -->
                    <button id="endConsultBtn" title="Kết Thúc Giao Dịch Tư Vấn" onclick="endConsultation()" style="background: #ef4444; color: white; border: none; border-radius: 20px; padding: 8px 16px; margin-right: 10px; font-weight: bold; cursor: pointer; transition: transform 0.2s;">
                        Kết Thúc
                    </button>
                    
                    <?php if (!$isDoctor && $convo['doctor_id'] == 1): ?>
                        <button id="findDoctorBtn" title="Kết nối Dược sĩ Thật" style="background: #10b981; color: white; border: none; border-radius: 20px; padding: 8px 16px; margin-right: 10px; font-weight: bold; cursor: pointer; transition: transform 0.2s;">
                            Kết nối Dược sĩ
                        </button>
                    <?php endif; ?>
                    
                    <!-- Nút bắt đầu gọi Video -->
                    <button id="startCallBtn" title="Gọi Video" aria-label="Audio/Video Call">
                        <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24"><path d="M17 10.5V7c0-.55-.45-1-1-1H4c-.55 0-1 .45-1 1v10c0 .55.45 1 1 1h12c.55 0 1-.45 1-1v-3.5l4 4v-11l-4 4z"/></svg>
                    </button>
                    <button title="Thông tin">
                        <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
                    </button>
                </div>
            </div>

            <!-- Box Tin nhắn -->
            <div class="chat-messages" id="chatMessages">
                <!-- Data đổ Ajax vào đây -->
            </div>

            <!-- Box nhập text -->
            <div class="chat-input-area">
                <input type="text" id="chatInput" class="chat-input" placeholder="Nhắn tin..." autocomplete="off">
                <button class="send-btn" id="sendBtn">
                    <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
                </button>
            </div>
        </div>
    </div>

    <!-- UI Modal Calling -->
    <div id="videoCallModal">
        <h2 style="color:white; margin-bottom: 20px;">Đang gọi Video WebRTC...</h2>
        <div class="video-container">
            <video id="remoteVideo" autoplay playsinline></video>
            <video id="localVideo" autoplay playsinline muted></video>
        </div>
        <div class="call-controls">
            <button class="end-call-btn" id="endCallBtn">Kết thúc cuộc gọi</button>
        </div>
    </div>

    <!-- WebRTC/Pusher Scripts -->
    <script src="https://js.pusher.com/8.0/pusher.min.js"></script>
    <script src="https://unpkg.com/peerjs@1.5.2/dist/peerjs.min.js"></script>
    
    <script>
        // Lấy Partner ID từ conversation trong thực tế để truyền cho PeerJS
        window.PARTNER_USER_ID = <?php echo $partner_id ? $partner_id : 'null'; ?>;
        window.THE_HOME_URL = "<?php echo $homeUrl; ?>";
    </script>
    <script src="<?= BASE_URL ?>public/js/chat.js?v=<?php echo time(); ?>"></script>
    <script src="<?= BASE_URL ?>public/js/rtc_call.js?v=<?php echo time(); ?>"></script>

<?php endif; ?>
</body>
</html>
