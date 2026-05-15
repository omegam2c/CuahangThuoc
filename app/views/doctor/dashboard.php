<?php include __DIR__ . '/../layout/header.php'; ?>
<?php require_once __DIR__ . '/../../../config/pusher_config.php'; ?>

<div class="container section animate-fade-up">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <div>
            <h1 class="section-title">Tư vấn trực tuyến</h1>
            <p style="color: var(--text-muted);">Chào mừng Dược sĩ <strong><?= htmlspecialchars($_SESSION['full_name']) ?></strong> quay trở lại.</p>
        </div>
        <div class="status-indicator">
            <span class="pulse-green"></span>
            <span style="color: var(--green); font-weight: bold;">Đang trực tuyến</span>
        </div>
    </div>

    <div class="glass-card" style="padding: 30px; margin-bottom: 30px;">
        <div style="display: flex; gap: 20px; align-items: flex-start;">
            <div style="background: rgba(16, 185, 129, 0.1); padding: 15px; border-radius: 12px;">
                <svg width="40" height="40" fill="var(--green)" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                </svg>
            </div>
            <div>
                <h3 style="margin-bottom: 10px;">Hướng dẫn tư vấn</h3>
                <p style="color: var(--text-muted); line-height: 1.6;">
                    Hệ thống Load Balancer đang tự động phân luồng khách hàng vào hàng đợi. Vui lòng giữ trình duyệt mở. 
                    Khi có bệnh nhân cần tư vấn WebRTC, hệ thống sẽ hiển thị thông báo chấp nhận ngay bên dưới.
                </p>
                <div style="margin-top: 20px;">
                    <a href="<?= BASE_URL ?>doctor/prescriptions" class="btn btn-premium" style="text-decoration: none;">
                        📋 Quản lý đơn thuốc cần duyệt
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- DANH SÁCH YÊU CẦU ĐANG CHỜ -->
    <div class="section-header" style="margin-top: 50px;">
        <h2 class="section-title" style="font-size: 1.5rem;">Yêu cầu kết nối đang chờ (<?= count($pendingRequests) ?>)</h2>
    </div>

    <?php if (!empty($pendingRequests)): ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-top: 20px;">
            <?php foreach ($pendingRequests as $req): ?>
                <div class="glass-card animate-fade-up" id="req-item-<?= $req['id'] ?>" style="padding: 20px; border-top: 4px solid var(--blue);">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px;">
                        <div>
                            <span style="font-size: 0.8rem; color: var(--text-muted);"><?= date('H:i:s', strtotime($req['created_at'])) ?></span>
                            <h4 style="margin: 5px 0;"><?= htmlspecialchars($req['customer_name']) ?></h4>
                        </div>
                        <div style="background: rgba(59, 130, 246, 0.1); padding: 8px; border-radius: 50%;">
                            <svg width="20" height="20" fill="var(--blue)" viewBox="0 0 24 24">
                                <path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/>
                            </svg>
                        </div>
                    </div>
                    <p style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 20px;">Đang chờ kết nối tư vấn WebRTC trực tiếp...</p>
                    <div style="display: flex; gap: 10px;">
                        <button class="btn btn-premium" style="flex: 1; padding: 10px;" onclick="respondSpecificRequest('accept', <?= $req['id'] ?>)">Chấp nhận</button>
                        <button class="btn" style="flex: 1; padding: 10px; border: 1px solid var(--border);" onclick="respondSpecificRequest('reject', <?= $req['id'] ?>)">Từ chối</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="glass-card" style="padding: 50px; text-align: center; margin-top: 20px;">
            <div style="opacity: 0.3; margin-bottom: 20px;">
                <svg width="80" height="80" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/>
                </svg>
            </div>
            <h4 style="color: var(--text-muted);">Hiện tại không có yêu cầu tư vấn nào.</h4>
            <p style="color: var(--text-muted); font-size: 0.9rem;">Hệ thống sẽ tự động cập nhật khi có khách hàng mới.</p>
        </div>
    <?php endif; ?>

    <!-- PHIÊN ĐANG HOẠT ĐỘNG -->
    <?php if ($lastConvo): ?>
        <div style="margin-top: 50px;">
            <div class="glass-card" style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%); color: white; padding: 25px; border: none; display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 20px;">
                    <div style="background: rgba(16, 101, 52, 0.3); padding: 15px; border-radius: 50%; border: 1px solid var(--green);">
                        <span class="pulse-green"></span>
                    </div>
                    <div>
                        <h4 style="margin: 0; font-size: 1.2rem;">Phiên tư vấn gần nhất</h4>
                        <p style="margin: 5px 0 0 0; opacity: 0.8;">Khách hàng: <strong><?= htmlspecialchars($lastConvo['customer_name']) ?></strong></p>
                    </div>
                </div>
                <a href="<?= BASE_URL ?>consult?id=<?= $lastConvo['id'] ?>" class="btn btn-premium" style="background: var(--green); border-color: var(--green); text-decoration: none;">
                    Tiếp tục tư vấn
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.status-indicator {
    display: flex;
    align-items: center;
    gap: 10px;
    background: white;
    padding: 8px 16px;
    border-radius: 30px;
    box-shadow: var(--shadow-sm);
}

.pulse-green {
    display: inline-block;
    width: 12px;
    height: 12px;
    background: var(--green);
    border-radius: 50%;
    position: relative;
}

.pulse-green::after {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    border-radius: 50%;
    background: var(--green);
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); opacity: 0.8; }
    70% { transform: scale(3); opacity: 0; }
    100% { transform: scale(1); opacity: 0; }
}
</style>

<script>
    function respondSpecificRequest(actionType, reqId) {
        const formData = new URLSearchParams();
        formData.append('request_id', reqId);
        formData.append('action', actionType);

        fetch('<?= BASE_URL ?>api/doctor_action.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if(data.success && actionType === 'accept') {
                window.location.href = '<?= BASE_URL ?>consult?id=' + data.conversation_id;
            } else if(actionType === 'reject') {
                const el = document.getElementById('req-item-' + reqId);
                if(el) el.style.display = 'none';
            } else {
                alert(data.error || "Yêu cầu đã bị hủy hoặc được bác sĩ khác thụ lý.");
                const el = document.getElementById('req-item-' + reqId);
                if(el) el.style.display = 'none';
            }
        });
    }
</script>

<?php 
// Include doctor alert helper for Pusher notifications
include __DIR__ . '/../layout/doctor_alert.php'; 
?>
<?php include __DIR__ . '/../layout/footer.php'; ?>

