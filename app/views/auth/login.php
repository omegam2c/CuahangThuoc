<?php require_once 'app/views/layout/auth_header.php'; ?>

<form class="auth-form" action="<?= BASE_URL ?>auth/handleLogin" method="POST">
    <?= $csrf_field ?>
    <div class="input-group">
        <input type="text" name="credential" placeholder="Email hoặc Tên đăng nhập" maxlength="100" required>
    </div>
    
    <div class="input-group">
        <input type="password" name="password" placeholder="Mật khẩu" maxlength="100" required>
        <a href="<?= BASE_URL ?>auth/forgotPassword" class="forgot-pw-link">Quên mật khẩu?</a>
    </div>

    <div class="input-group" style="display: flex; gap: 10px; align-items: center; margin-bottom: 20px;">
        <input type="text" name="captcha" placeholder="Nhập mã xác thực bên cạnh" maxlength="5" required style="flex: 1; text-transform: uppercase;">
        <div style="background: linear-gradient(135deg, #0f172a, #334155); color: white; padding: 10px 20px; border-radius: 8px; font-weight: 800; letter-spacing: 5px; font-family: 'Courier New', Courier, monospace; font-size: 1.2rem; user-select: none; box-shadow: inset 0 2px 4px rgba(0,0,0,0.3);">
            <?= htmlspecialchars($captcha ?? '') ?>
        </div>
    </div>

    <button type="submit" class="btn-primary">Đăng nhập</button>
</form>

<div class="auth-links">
    Chưa có tài khoản? <a href="<?= BASE_URL ?>auth/register">Đăng ký ngay</a>
</div>

<?php require_once 'app/views/layout/auth_footer.php'; ?>
