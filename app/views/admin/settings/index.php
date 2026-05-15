<?php include __DIR__ . '/../../layout/header.php'; ?>

<style>
.toggle-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px 0;
  border-bottom: 1px solid var(--border, rgba(0,0,0,0.05));
}
.toggle-row:last-child {
  border-bottom: none;
}
.toggle {
  position: relative;
  display: inline-block;
  width: 50px;
  height: 28px;
}
.toggle input {
  opacity: 0;
  width: 0;
  height: 0;
}
.toggle-slider {
  position: absolute;
  cursor: pointer;
  top: 0; left: 0; right: 0; bottom: 0;
  background-color: #ccc;
  transition: .4s;
  border-radius: 34px;
}
.toggle-slider:before {
  position: absolute;
  content: "";
  height: 20px;
  width: 20px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  transition: .4s;
  border-radius: 50%;
}
input:checked + .toggle-slider {
  background-color: var(--blue, #2196F3);
}
input:checked + .toggle-slider:before {
  transform: translateX(22px);
}
</style>

<div class="container section animate-fade-up">
    <h1 class="section-title">Cấu hình hệ thống</h1>

    <?php if (isset($_SESSION['flash_success'])): ?>
        <div style="background: var(--green); color: white; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <?= htmlspecialchars($_SESSION['flash_success']) ?>
            <?php unset($_SESSION['flash_success']); ?>
        </div>
    <?php endif; ?>

    <div style="max-width: 600px; margin: 0 auto;">
        <div class="glass-card" style="padding: 30px;">
            <form method="POST" action="<?= BASE_URL ?>admin/settings/save">
                <?= $csrf_field ?>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600;">Tên hệ thống</label>
                    <input type="text" name="site_name" value="<?= htmlspecialchars($settings['site_name'] ?? '') ?>" class="form-control" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border);">
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600;">Email Admin</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($settings['email'] ?? '') ?>" class="form-control" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border);">
                </div>

                <div class="form-group" style="margin-bottom: 30px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600;">Ngôn ngữ</label>
                    <select name="lang" class="form-control" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border);">
                        <option value="vi" <?= ($settings['lang'] ?? '') === 'vi' ? 'selected' : '' ?>>Tiếng Việt</option>
                        <option value="en" <?= ($settings['lang'] ?? '') === 'en' ? 'selected' : '' ?>>English</option>
                    </select>
                </div>

                <div style="margin-bottom: 30px; padding: 20px; background: rgba(0,0,0,0.02); border-radius: 12px; border: 1px solid var(--border);">
                    <h3 style="font-size: 1.1rem; margin-bottom: 15px;">🔒 Bảo mật & Thông báo</h3>

                    <div class="toggle-row">
                        <div>
                            <strong>Nhận thông báo email</strong>
                            <p style="margin: 4px 0 0; font-size: 0.85rem; color: var(--text-muted);">Gửi email cho Admin khi có đơn hàng mới</p>
                        </div>
                        <label class="toggle">
                            <input type="checkbox" name="notify" <?= !empty($settings['notify']) ? 'checked' : '' ?>>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>

                    <div class="toggle-row">
                        <div>
                            <strong>Xác thực 2 bước (2FA)</strong>
                            <p style="margin: 4px 0 0; font-size: 0.85rem; color: var(--text-muted);">Tăng cường bảo mật cho tài khoản Quản trị viên</p>
                        </div>
                        <label class="toggle">
                            <input type="checkbox" name="twofa" <?= !empty($settings['twofa']) ? 'checked' : '' ?>>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; font-size: 1rem;">Lưu cấu hình</button>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../layout/footer.php'; ?>
