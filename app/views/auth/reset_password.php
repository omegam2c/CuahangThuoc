<?php require_once 'app/views/layout/auth_header.php'; ?>

<form class="auth-form" action="<?= BASE_URL ?>auth/resetPassword" method="POST">
    <?= $csrf_field ?>
    <div class="input-group">
        <input type="password" name="new_password" placeholder="Mật khẩu mới" maxlength="100" required>
    </div>

    <div class="input-group">
        <input type="password" name="confirm_password" placeholder="Nhập lại Mật khẩu mới" maxlength="100" required>
    </div>

    <button type="submit" class="btn-primary">Cập nhật Mật khẩu</button>
</form>

<?php require_once 'app/views/layout/auth_footer.php'; ?>
