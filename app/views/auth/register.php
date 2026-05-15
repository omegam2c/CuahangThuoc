<?php require_once 'app/views/layout/auth_header.php'; ?>

<form class="auth-form" action="<?= BASE_URL ?>auth/handleRegister" method="POST">
    <?= $csrf_field ?>
    <div class="input-group">
        <input type="text" name="fullName" value="<?= htmlspecialchars($oldInput['fullName'] ?? '') ?>" placeholder="Họ và Tên" maxlength="100" required>
    </div>

    <div class="input-group">
        <input type="text" name="username" value="<?= htmlspecialchars($oldInput['username'] ?? '') ?>" placeholder="Tên đăng nhập" maxlength="50" required>
    </div>

    <div class="input-group">
        <input type="email" name="email" value="<?= htmlspecialchars($oldInput['email'] ?? '') ?>" placeholder="Địa chỉ Email" maxlength="100" required>
    </div>

    <div class="input-group">
        <input type="tel" name="phone" value="<?= htmlspecialchars($oldInput['phone'] ?? '') ?>" placeholder="Số điện thoại" maxlength="11" pattern="^(0|84)(3|5|7|8|9)([0-9]{8})$" title="Vui lòng nhập số điện thoại Việt Nam hợp lệ (10-11 chữ số, bắt đầu bằng 0 hoặc 84)" required>
    </div>

    <div class="input-group">
        <input type="password" name="password" placeholder="Mật khẩu (6-100 ký tự, 1 hoa, 1 đặc biệt)" maxlength="100" required>
    </div>

    <button type="submit" class="btn-primary">Tạo tài khoản</button>
</form>

<div class="auth-links">
    Đã có tài khoản? <a href="<?= BASE_URL ?>auth/login">Đăng nhập</a>
</div>

<?php require_once 'app/views/layout/auth_footer.php'; ?>