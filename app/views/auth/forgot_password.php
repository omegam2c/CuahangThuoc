<?php require_once 'app/views/layout/auth_header.php'; ?>

<form class="auth-form" action="<?= BASE_URL ?>auth/sendOtp" method="POST">
    <?= $csrf_field ?>
    <div class="input-group">
        <input type="email" name="email" placeholder="Nhập địa chỉ Email của bạn" maxlength="100" required>
    </div>

    <button type="submit" class="btn-primary">Nhận mã OTP</button>
</form>

<div class="auth-links">
    <a href="<?= BASE_URL ?>auth/login">Quay lại Đăng nhập</a>
</div>

<?php require_once 'app/views/layout/auth_footer.php'; ?>
