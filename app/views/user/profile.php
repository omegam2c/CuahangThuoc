<?php include __DIR__ . '/../layout/header.php'; ?>

<style>
    /* Profile Specific Styles */
    .profile-container {
        display: grid;
        grid-template-columns: 280px 1fr;
        gap: 30px;
        margin-top: -80px;
        position: relative;
        z-index: 2;
    }

    .profile-header-banner {
        height: 250px;
        background: var(--gradient-primary);
        border-radius: 0 0 40px 40px;
        margin-bottom: 0;
    }

    .profile-sidebar {
        height: fit-content;
        padding: 30px 20px;
    }

    .user-avatar-wrap {
        position: relative;
        width: 120px;
        height: 120px;
        margin: -90px auto 20px;
    }

    .user-avatar-main {
        width: 100%;
        height: 100%;
        background: white;
        color: var(--green);
        border-radius: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3.5rem;
        font-weight: 800;
        box-shadow: var(--shadow-lg);
        border: 4px solid white;
    }

    .sidebar-menu {
        list-style: none;
        padding: 0;
        margin-top: 30px;
    }

    .sidebar-menu li {
        margin-bottom: 12px;
    }

    .sidebar-menu a {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 18px;
        border-radius: 12px;
        color: var(--text-body);
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .sidebar-menu a:hover {
        background: var(--green-light);
        color: var(--green);
        transform: translateX(5px);
    }

    .sidebar-menu a.active {
        background: var(--green);
        color: white;
        box-shadow: 0 4px 12px rgba(0, 144, 74, 0.2);
    }

    .profile-main-content {
        display: flex;
        flex-direction: column;
        gap: 30px;
    }

    .profile-section-card {
        padding: 35px;
        border-radius: 24px;
    }

    .section-title-premium {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 25px;
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-main);
    }

    .section-title-premium i {
        color: var(--green);
        background: var(--green-light);
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
    }

    .modern-form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .form-group-modern {
        margin-bottom: 20px;
    }

    .form-group-modern.full-width {
        grid-column: span 2;
    }

    .form-group-modern label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: var(--text-main);
        font-size: 0.95rem;
    }

    .input-modern-wrap {
        position: relative;
    }

    .input-modern {
        width: 100%;
        padding: 14px 16px;
        border: 1.5px solid var(--border);
        border-radius: 12px;
        background: white;
        font-family: inherit;
        font-size: 1rem;
        transition: all 0.3s ease;
    }

    .input-modern:focus {
        outline: none;
        border-color: var(--green);
        box-shadow: 0 0 0 4px rgba(0, 144, 74, 0.1);
    }

    .input-modern[readonly] {
        background: #f8f9fa;
        cursor: not-allowed;
    }

    .textarea-modern {
        resize: vertical;
        min-height: 100px;
    }

    .btn-save-wrap {
        display: flex;
        justify-content: flex-end;
        margin-top: 10px;
    }

    .password-strength-hint {
        font-size: 0.85rem;
        color: var(--text-muted);
        margin-top: 8px;
    }

    @media (max-width: 992px) {
        .profile-container {
            grid-template-columns: 1fr;
        }
        .modern-form-grid {
            grid-template-columns: 1fr;
        }
        .form-group-modern.full-width {
            grid-column: span 1;
        }
    }
</style>

<!-- Top Banner Background -->
<div class="profile-header-banner"></div>

<div class="container animate-fade-up">
    <div class="profile-container">
        
        <!-- Sidebar -->
        <aside class="glass-card profile-sidebar">
            <div class="user-avatar-wrap">
                <div class="user-avatar-main">
                    <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
                </div>
            </div>
            <div style="text-align: center;">
                <h3 style="font-weight: 700; margin-bottom: 5px;"><?= htmlspecialchars($user['full_name']) ?></h3>
                <p style="color: var(--text-muted); font-size: 0.9rem;"><?= htmlspecialchars($user['email']) ?></p>
            </div>
            
            <ul class="sidebar-menu">
                <li>
                    <a href="<?= BASE_URL ?>account" class="active">
                        <span>👤</span> Thông tin cá nhân
                    </a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>account/orders">
                        <span>📦</span> Lịch sử đơn hàng
                    </a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>cart">
                        <span>🛒</span> Giỏ hàng của tôi
                    </a>
                </li>
                <li style="margin-top: 20px; border-top: 1px solid var(--border); padding-top: 20px;">
                    <a href="<?= BASE_URL ?>auth/logout" style="color: var(--red);">
                        <span>🚪</span> Đăng xuất
                    </a>
                </li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="profile-main-content">
            
            <!-- Section: Personal Information -->
            <section class="glass-card profile-section-card">
                <div class="section-title-premium">
                    <i>👤</i>
                    <span>Thông tin cá nhân</span>
                </div>
                
                <form action="<?= BASE_URL ?>account/updateProfile" method="POST">
                    <?= $this->csrfField() ?>
                    <div class="modern-form-grid">
                        <div class="form-group-modern">
                            <label>Họ và tên *</label>
                            <div class="input-modern-wrap">
                                <input type="text" name="full_name" class="input-modern" value="<?= htmlspecialchars($user['full_name']) ?>" maxlength="100" required placeholder="Nhập họ tên của bạn">
                            </div>
                        </div>
                        
                        <div class="form-group-modern">
                            <label>Số điện thoại</label>
                            <div class="input-modern-wrap">
                                <input type="text" name="phone" class="input-modern" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" maxlength="11" pattern="^(0|84)(3|5|7|8|9)([0-9]{8})$" title="Số điện thoại Việt Nam hợp lệ" placeholder="Số điện thoại liên hệ">
                            </div>
                        </div>

                        <div class="form-group-modern full-width">
                            <label>Email (Tài khoản đăng nhập)</label>
                            <div class="input-modern-wrap">
                                <input type="email" class="input-modern" value="<?= htmlspecialchars($user['email']) ?>" readonly>
                            </div>
                        </div>
                        
                        <div class="form-group-modern full-width">
                            <label>Địa chỉ giao hàng mặc định</label>
                            <div class="input-modern-wrap">
                                <textarea name="address" class="input-modern textarea-modern" maxlength="255" placeholder="Địa chỉ nhận hàng (Số nhà, đường, phường/xã...)"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="btn-save-wrap">
                        <button type="submit" class="btn-premium">
                            ✨ Lưu thay đổi
                        </button>
                    </div>
                </form>
            </section>

            <!-- Section: Security -->
            <section class="glass-card profile-section-card">
                <div class="section-title-premium">
                    <i>🔒</i>
                    <span>Bảo mật & Mật khẩu</span>
                </div>
                
                <form action="<?= BASE_URL ?>account/changePassword" method="POST">
                    <?= $this->csrfField() ?>
                    <div class="modern-form-grid">
                        <div class="form-group-modern full-width">
                            <label>Mật khẩu hiện tại *</label>
                            <div class="input-modern-wrap">
                                <input type="password" name="current_password" class="input-modern" required placeholder="••••••••">
                            </div>
                        </div>
                        
                        <div class="form-group-modern">
                            <label>Mật khẩu mới *</label>
                            <div class="input-modern-wrap">
                                <input type="password" name="new_password" class="input-modern" required minlength="6" placeholder="Mật khẩu mới">
                            </div>
                            <div class="password-strength-hint">Sử dụng ít nhất 6 ký tự, có in hoa và ký tự đặc biệt.</div>
                        </div>
                        
                        <div class="form-group-modern">
                            <label>Xác nhận mật khẩu *</label>
                            <div class="input-modern-wrap">
                                <input type="password" name="confirm_password" class="input-modern" required minlength="6" placeholder="Xác nhận lại mật khẩu">
                            </div>
                        </div>
                    </div>
                    
                    <div class="btn-save-wrap">
                        <button type="submit" class="btn-premium" style="background: var(--text-main);">
                            🛡️ Cập nhật bảo mật
                        </button>
                    </div>
                </form>
            </section>

        </main>
    </div>
</div>

<div style="margin-bottom: 80px;"></div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
