<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="container section animate-fade-up">
    <div style="max-width: 600px; margin: 50px auto; text-align: center;">
        
        <div class="glass-card" style="padding: 50px 30px;">
            <div style="font-size: 5rem; margin-bottom: 20px; color: var(--green);">✅</div>
            <h1 style="margin-bottom: 20px; color: var(--text-color);">Gửi đơn thuốc thành công!</h1>
            
            <?php if (!empty($_SESSION['success_message'])): ?>
                <p style="font-size: 1.1rem; color: #555; line-height: 1.6; margin-bottom: 30px;">
                    <?= htmlspecialchars($_SESSION['success_message']) ?>
                </p>
                <?php unset($_SESSION['success_message']); ?>
            <?php else: ?>
                <p style="font-size: 1.1rem; color: #555; line-height: 1.6; margin-bottom: 30px;">
                    Đơn thuốc của bạn đã được ghi nhận. Dược sĩ của Nhà thuốc 1985 sẽ kiểm tra và liên hệ lại với bạn để báo giá và tư vấn trong thời gian sớm nhất.
                </p>
            <?php endif; ?>
            
            <div style="display: flex; gap: 15px; justify-content: center;">
                <a href="<?= BASE_URL ?>home" class="btn btn-outline" style="min-width: 150px;">Về trang chủ</a>
                <a href="<?= BASE_URL ?>prescription/upload" class="btn btn-premium" style="min-width: 150px;">Gửi thêm đơn mới</a>
            </div>
        </div>
        
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
