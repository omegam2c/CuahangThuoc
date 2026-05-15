<?php require_once 'app/views/layout/auth_header.php'; ?>

<form class="auth-form" action="<?= BASE_URL ?>auth/verifyOtp" method="POST">
    <?= $csrf_field ?>
    <div class="input-group">
        <input type="text" name="otp_code" placeholder="Nhập mã 6 số OTP thu được" maxlength="6" required style="text-align: center; font-size: 20px; letter-spacing: 4px;">
    </div>

    <button type="submit" class="btn-primary">Xác thực OTP</button>
</form>

<div class="auth-links">
    Không nhận được mã? <a href="<?= BASE_URL ?>auth/forgotPassword">Gửi lại</a>
</div>

<?php require_once 'app/views/layout/auth_footer.php'; ?>
