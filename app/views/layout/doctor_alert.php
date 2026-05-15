<?php
// app/views/layout/doctor_alert.php
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role_id']) || $_SESSION['role_id'] != 2) return;
require_once __DIR__ . '/../../../config/pusher_config.php';
?>
<!-- Global Doctor Alert UI -->
<style>
    .global-queue-alert {
        position: fixed;
        bottom: -100px;
        right: 30px;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.5);
        padding: 24px;
        border-radius: 16px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.15), 0 0 0 1px rgba(0,0,0,0.05);
        z-index: 9999;
        visibility: hidden;
        opacity: 0;
        transform: translateY(20px);
        max-width: 380px;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    .global-queue-alert.show {
        visibility: visible;
        opacity: 1;
        bottom: 30px;
        transform: translateY(0);
    }
    .global-queue-alert h3 { 
        color: #0f172a; 
        margin-top: 0; 
        display: flex; 
        align-items: center; 
        gap: 12px; 
        font-size: 18px; 
        font-weight: 700;
        font-family: 'Inter', sans-serif; 
    }
    .global-queue-alert h3 svg {
        color: #3b82f6;
        animation: bell-ring 2s ease infinite;
    }
    .global-queue-alert p { 
        color: #475569; 
        font-size: 15px; 
        font-family: 'Inter', sans-serif; 
        line-height: 1.5;
        margin-bottom: 20px;
    }
    .global-action-buttons { display: flex; gap: 12px; }
    .global-btn { 
        padding: 12px 16px; 
        border: none; 
        border-radius: 10px; 
        cursor: pointer; 
        font-size: 14px; 
        font-weight: 600; 
        font-family: 'Inter', sans-serif; 
        transition: all 0.2s ease; 
        flex: 1;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
    }
    .global-btn-accept { 
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); 
        color: white; 
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25); 
    }
    .global-btn-accept:hover { 
        transform: translateY(-2px); 
        box-shadow: 0 6px 15px rgba(37, 99, 235, 0.35); 
    }
    .global-btn-reject { 
        background: #f1f5f9; 
        color: #64748b; 
    }
    .global-btn-reject:hover { 
        background: #e2e8f0; 
        color: #334155; 
    }
    @keyframes bell-ring {
        0% { transform: rotate(0); }
        10% { transform: rotate(15deg); }
        20% { transform: rotate(-10deg); }
        30% { transform: rotate(5deg); }
        40% { transform: rotate(-5deg); }
        50% { transform: rotate(0); }
        100% { transform: rotate(0); }
    }
</style>

<div class="global-queue-alert" id="globalIncomingAlert">
    <h3>
        <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M12 22q-.825 0-1.412-.587Q10 20.825 10 20h4q0 .825-.587 1.413Q12.825 22 12 22zm-6-3v-2h2v-7q0-2.075 1.25-3.688Q10.5 4.7 12.5 4.2v-.7q0-.625.438-1.062Q13.375 2 14 2q.625 0 1.062.438.438.437.438 1.062v.7q2 .5 3.25 2.112Q20 7.925 20 10v7h2v2z"/></svg>
        YÊU CẦU TƯ VẤN MỚI!
    </h3>
    <p>Hệ thống vừa phân bổ 1 khách hàng đang chờ kết nối tư vấn trực tuyến.</p>
    <div class="global-action-buttons">
        <button class="global-btn global-btn-accept" onclick="globalRespondRequest('accept')">Chấp nhận</button>
        <button class="global-btn global-btn-reject" onclick="globalRespondRequest('reject')">Bỏ qua</button>
    </div>
</div>

<script src="https://js.pusher.com/8.0/pusher.min.js"></script>
<script>
    const G_THE_DOCTOR_ID = <?php echo $_SESSION['user_id']; ?>;
    const G_PUSHER_KEY = '<?php echo constant("PUSHER_APP_KEY") !== "" ? PUSHER_APP_KEY : ""; ?>';
    const G_PUSHER_CLUSTER = '<?php echo PUSHER_APP_CLUSTER; ?>';
    let g_currentRequestId = null;

    // 1. Gửi Heartbeat duy trì Online State (15s/lần)
    function globalSendPing() {
        fetch(window.BASE_URL + 'api/heartbeat.php').catch(e => {
            let badge = document.getElementById('statusBadge');
            if (badge) {
                badge.className = 'status-badge offline';
                badge.innerHTML = 'Mất kết nối';
            }
        });
    }
    setInterval(globalSendPing, 15000); 
    globalSendPing();

    // 2. Lắng nghe điều phối (Routing Pusher)
    if (G_PUSHER_KEY) {
        const globalPusher = new Pusher(G_PUSHER_KEY, {
            cluster: G_PUSHER_CLUSTER,
            authEndpoint: '<?= BASE_URL ?>api/pusher_auth.php'
        });
        
        const globalChannel = globalPusher.subscribe('private-doctor-' + G_THE_DOCTOR_ID);
        
        globalChannel.bind('incoming-consult', function(data) {
            console.log("CÓ CUỘC GỌI", data);
            
            // Nếu đang ở dashboard, tự động load lại trang để lấy danh sách từ DB
            // (Nếu bạn muốn pop-up vẫn hiện trên dashboard thì có thể bỏ comment block if dưới đây)
            /*
            if (window.location.href.includes('dashboard')) {
                window.location.reload();
                return;
            }
            */
            
            g_currentRequestId = data.request_id;
            document.getElementById('globalIncomingAlert').classList.add('show');
        });
    }

    // 3. Phản hồi Yêu cầu về Server
    window.globalRespondRequest = function(actionType) {
        if(!g_currentRequestId) return;
        
        const formData = new URLSearchParams();
        formData.append('request_id', g_currentRequestId);
        formData.append('action', actionType);

        fetch('<?= BASE_URL ?>api/doctor_action.php', {
            method: 'POST',
            headers: {
                'X-CSRF-Token': window.csrfToken
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if(data.success && actionType === 'accept') {
                window.location.href = '<?= BASE_URL ?>consult?id=' + data.conversation_id;
            } else if(actionType === 'reject') {
                document.getElementById('globalIncomingAlert').classList.remove('show');
                g_currentRequestId = null;
            } else {
                alert(data.error || "Yêu cầu đã bị hủy hoặc được bác sĩ khác thụ lý.");
                document.getElementById('globalIncomingAlert').classList.remove('show');
                g_currentRequestId = null;
            }
        });
    };
</script>
