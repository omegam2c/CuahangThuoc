<?php include __DIR__ . '/../../layout/header.php'; ?>

<style>
    .admin-page-header {
        background: linear-gradient(135deg, var(--green) 0%, #047857 100%);
        padding: 40px 0 80px 0;
        border-radius: 16px;
        margin-bottom: -50px;
        color: white;
        box-shadow: 0 10px 30px rgba(16, 185, 129, 0.2);
        position: relative;
        overflow: hidden;
    }
    .admin-page-header::after {
        content: '';
        position: absolute;
        top: 0; right: 0; bottom: 0; left: 0;
        background: url('data:image/svg+xml;utf8,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><path d="M10 10h10v10H10z" fill="rgba(255,255,255,0.05)"/></svg>') repeat;
        opacity: 0.5;
    }
    .admin-header-content {
        position: relative;
        z-index: 1;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0 40px;
    }
    .admin-header-content h1 {
        margin: 0;
        font-size: 2rem;
        font-weight: 800;
        display: flex;
        align-items: center;
        gap: 15px;
    }
    .admin-header-content p {
        margin: 5px 0 0 55px;
        color: rgba(255,255,255,0.8);
        font-size: 1rem;
    }
    .btn-back {
        background: rgba(255,255,255,0.2);
        color: white;
        border: 1px solid rgba(255,255,255,0.3);
        backdrop-filter: blur(10px);
        transition: all 0.3s ease;
    }
    .btn-back:hover {
        background: white;
        color: var(--green);
    }
    
    .form-wrapper {
        background: white;
        border-radius: 20px;
        box-shadow: 0 15px 40px rgba(0,0,0,0.08);
        padding: 40px;
        position: relative;
        z-index: 2;
        margin: 0 auto 50px auto;
        border: 1px solid rgba(0,0,0,0.05);
    }
    
    .form-section-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--text-color);
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 2px solid var(--border);
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .modern-form-group {
        margin-bottom: 25px;
        position: relative;
    }
    
    .modern-label {
        display: block;
        font-size: 0.95rem;
        font-weight: 600;
        color: var(--text-color);
        margin-bottom: 8px;
    }
    
    .input-icon-wrapper {
        position: relative;
    }
    
    .input-icon-wrapper i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
        font-size: 1.1rem;
        transition: color 0.3s ease;
    }
    
    .modern-input {
        width: 100%;
        padding: 12px 15px 12px 45px;
        border: 2px solid var(--border);
        border-radius: 10px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: #f8fafc;
        font-family: inherit;
        box-sizing: border-box;
    }
    
    .modern-input:focus {
        background: white;
        border-color: var(--green);
        box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
        outline: none;
    }
    
    .modern-input:focus + i, 
    .modern-input:focus ~ i {
        color: var(--green);
    }
    
    .modern-select {
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 15px center;
        background-size: 16px;
    }
    
    .role-badges {
        display: flex;
        gap: 10px;
        margin-top: 10px;
    }
    
    .role-badge {
        font-size: 0.75rem;
        padding: 4px 8px;
        border-radius: 6px;
        background: #e2e8f0;
        color: #475569;
        font-weight: 600;
    }
    .role-badge.admin { background: #fee2e2; color: #ef4444; }
    .role-badge.pharmacist { background: #dbeafe; color: #3b82f6; }
    
    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 15px;
        margin-top: 40px;
        padding-top: 25px;
        border-top: 1px solid var(--border);
    }
    
    .btn-save {
        background: linear-gradient(135deg, var(--green) 0%, #047857 100%);
        color: white;
        padding: 12px 30px;
        border-radius: 10px;
        font-weight: 700;
        border: none;
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
    }
    .btn-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
    }
    
    /* Layout grid */
    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0 25px;
    }
    .full-width {
        grid-column: span 2;
    }
    
    @media (max-width: 768px) {
        .form-grid {
            grid-template-columns: 1fr;
        }
        .full-width {
            grid-column: span 1;
        }
        .admin-header-content {
            flex-direction: column;
            align-items: flex-start;
            gap: 20px;
            padding: 0 20px;
        }
    }
</style>

<div class="container section animate-fade-up">
    
    <!-- Hero Header -->
    <div class="admin-page-header">
        <div class="admin-header-content">
            <div>
                <h1><i class="fas fa-user-plus"></i> Thêm người dùng mới</h1>
                <p>Tạo tài khoản mới và thiết lập quyền truy cập hệ thống</p>
            </div>
            <a href="<?= BASE_URL ?>admin/users" class="btn btn-outline btn-back">
                <i class="fas fa-arrow-left"></i> Trở về danh sách
            </a>
        </div>
    </div>

    <!-- Form Container -->
    <div class="form-wrapper">
        <form action="<?= BASE_URL ?>admin/users/store" method="POST">
            <?= $csrf_field ?>
            
            <h3 class="form-section-title"><i class="fas fa-id-card" style="color: var(--green);"></i> Thông tin đăng nhập</h3>
            
            <div class="form-grid">
                <div class="modern-form-group">
                    <label for="username" class="modern-label">Tên tài khoản (Username) <span style="color: var(--red);">*</span></label>
                    <div class="input-icon-wrapper">
                        <input type="text" name="username" id="username" class="modern-input" required placeholder="Nhập tên tài khoản">
                        <i class="fas fa-user"></i>
                    </div>
                </div>
                
                <div class="modern-form-group">
                    <label for="password" class="modern-label">Mật khẩu <span style="color: var(--red);">*</span></label>
                    <div class="input-icon-wrapper">
                        <input type="password" name="password" id="password" class="modern-input" required placeholder="Nhập mật khẩu an toàn">
                        <i class="fas fa-lock"></i>
                    </div>
                </div>

                <div class="modern-form-group full-width">
                    <label for="role_id" class="modern-label">Vai trò hệ thống <span style="color: var(--red);">*</span></label>
                    <div class="input-icon-wrapper">
                        <select name="role_id" id="role_id" class="modern-input modern-select" required>
                            <?php foreach ($roles as $id => $name): ?>
                                <option value="<?= $id ?>" <?= $id == 4 ? 'selected' : '' ?>><?= $name ?></option>
                            <?php endforeach; ?>
                        </select>
                        <i class="fas fa-user-tag"></i>
                    </div>
                    <div class="role-badges">
                        <span class="role-badge admin">Admin: Quản trị kỹ thuật</span>
                        <span class="role-badge pharmacist">Dược sĩ: Chuyên môn & Bán hàng</span>
                        <span class="role-badge owner" style="background: #fef9c3; color: #854d0e;">Chủ cửa hàng: Quản lý & Nhập hàng</span>
                        <span class="role-badge">Khách hàng: Người mua</span>
                    </div>
                </div>
            </div>

            <h3 class="form-section-title" style="margin-top: 20px;"><i class="fas fa-address-book" style="color: var(--green);"></i> Thông tin cá nhân</h3>
            
            <div class="form-grid">
                <div class="modern-form-group">
                    <label for="full_name" class="modern-label">Họ và tên <span style="color: var(--red);">*</span></label>
                    <div class="input-icon-wrapper">
                        <input type="text" name="full_name" id="full_name" class="modern-input" required placeholder="Nhập đầy đủ họ tên">
                        <i class="fas fa-signature"></i>
                    </div>
                </div>
                
                <div class="modern-form-group">
                    <label for="email" class="modern-label">Địa chỉ Email <span style="color: var(--red);">*</span></label>
                    <div class="input-icon-wrapper">
                        <input type="email" name="email" id="email" class="modern-input" required placeholder="email@example.com">
                        <i class="fas fa-envelope"></i>
                    </div>
                </div>
                
                <div class="modern-form-group full-width">
                    <label for="phone" class="modern-label">Số điện thoại</label>
                    <div class="input-icon-wrapper">
                        <input type="tel" name="phone" id="phone" class="modern-input" placeholder="09xx xxx xxx">
                        <i class="fas fa-phone-alt"></i>
                    </div>
                </div>
                
                <div class="modern-form-group full-width">
                    <label for="address" class="modern-label">Địa chỉ liên hệ</label>
                    <div class="input-icon-wrapper">
                        <textarea name="address" id="address" class="modern-input" rows="3" placeholder="Nhập địa chỉ chi tiết (Số nhà, đường, phường/xã, quận/huyện...)" style="padding-top: 15px;"></textarea>
                        <i class="fas fa-map-marker-alt" style="top: 25px;"></i>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="reset" class="btn btn-outline" style="padding: 12px 25px; border-radius: 10px; font-weight: 600;">
                    <i class="fas fa-redo-alt" style="margin-right: 5px;"></i> Làm mới
                </button>
                <button type="submit" class="btn-save">
                    <i class="fas fa-save"></i> Tạo tài khoản
                </button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../../layout/footer.php'; ?>
