<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="container section animate-fade-up">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h1 class="section-title" style="margin: 0;">Quản lý người dùng</h1>
        <a href="<?= BASE_URL ?>admin/users/create" class="btn btn-premium">
            <i class="fas fa-plus"></i> Thêm người dùng mới
        </a>
    </div>

    <div class="glass-card" style="padding: 20px; margin-top: 30px; margin-bottom: 20px;">
        <form action="<?= BASE_URL ?>admin/users" method="GET" style="display: flex; gap: 15px;">
            <input type="text" name="q" placeholder="Tìm theo Tên, Username, Email, SĐT..." class="form-control" style="flex: 2;" value="<?= htmlspecialchars($q ?? '') ?>">
            <select name="role_id" class="form-control" style="flex: 1;">
                <option value="">Tất cả vai trò</option>
                <?php foreach ($roles as $id => $name): ?>
                    <option value="<?= $id ?>" <?= (isset($role_id) && $role_id == $id) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($name) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary">Tìm kiếm</button>
        </form>
    </div>

    <div class="glass-card" style="padding: 20px;">
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="text-align: left; border-bottom: 1px solid var(--border);">
                        <th style="padding: 10px;">ID</th>
                        <th style="padding: 10px;">Tài khoản</th>
                        <th style="padding: 10px;">Thông tin</th>
                        <th style="padding: 10px;">Vai trò</th>
                        <th style="padding: 10px;">Ngày đăng ký</th>
                        <th style="padding: 10px;">Lần cuối đăng nhập</th>
                        <th style="padding: 10px;">Trạng thái</th>
                        <th style="padding: 10px;">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr style="border-bottom: 1px solid var(--border);">
                            <td style="padding: 10px;"><strong>#<?= $u['user_id'] ?></strong></td>
                            <td style="padding: 10px;">
                                <strong><?= htmlspecialchars($u['username']) ?></strong><br>
                                <small style="color: var(--text-muted);"><?= htmlspecialchars($u['email']) ?></small>
                            </td>
                            <td style="padding: 10px;">
                                <?= htmlspecialchars($u['full_name']) ?><br>
                                <small style="color: var(--text-muted);"><?= htmlspecialchars($u['phone']) ?></small>
                            </td>
                            <td style="padding: 10px;">
                                <form action="<?= BASE_URL ?>admin/users/updateRole" method="POST" style="margin: 0;">
                                    <?= $csrf_field ?>
                                    <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                                    <select name="role_id" onchange="this.form.submit()" style="padding: 5px; border-radius: 5px; border: 1px solid var(--border);" <?= $u['user_id'] == $_SESSION['user_id'] ? 'disabled' : '' ?>>
                                        <?php foreach ($roles as $id => $name): ?>
                                            <option value="<?= $id ?>" <?= $u['role_id'] == $id ? 'selected' : '' ?>><?= $name ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </form>
                            </td>
                            <td style="padding: 10px;"><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
                            <td style="padding: 10px;"><?= $u['last_login'] ? date('d/m/Y H:i', strtotime($u['last_login'])) : 'Chưa từng' ?></td>
                            <td style="padding: 10px;">
                                <?php if ($u['is_active']): ?>
                                    <span style="color: var(--green); font-weight: bold;">Hoạt động</span>
                                <?php else: ?>
                                    <span style="color: var(--red); font-weight: bold;">Đã khóa</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 10px;">
                                <?php if ($u['user_id'] != $_SESSION['user_id']): ?>
                                    <form action="<?= BASE_URL ?>admin/users/toggleStatus" method="POST" style="margin: 0;">
                                        <?= $csrf_field ?>
                                        <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                                        <?php if ($u['is_active']): ?>
                                            <button type="submit" class="btn btn-outline" style="border-color: var(--red); color: var(--red); padding: 5px 10px; font-size: 0.8rem;" onclick="return confirm('Bạn có chắc muốn KHÓA tài khoản này?');">Khóa</button>
                                        <?php else: ?>
                                            <button type="submit" class="btn btn-premium" style="background: var(--green); padding: 5px 10px; font-size: 0.8rem;" onclick="return confirm('Bạn có chắc muốn MỞ KHÓA tài khoản này?');">Mở khóa</button>
                                        <?php endif; ?>
                                    </form>
                                <?php else: ?>
                                    <span style="color: var(--text-muted); font-size: 0.85rem;">(Bạn)</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../layout/footer.php'; ?>
