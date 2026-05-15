<?php include __DIR__ . '/../layout/header.php'; ?>

<style>
    /* Profile & Orders Shared Styles */
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

    .detail-main-content {
        display: flex;
        flex-direction: column;
        gap: 30px;
    }

    .detail-card {
        padding: 30px;
        border-radius: 24px;
    }

    .section-title-premium {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 25px;
        font-size: 1.3rem;
        font-weight: 700;
        color: var(--text-main);
    }

    .section-title-premium i {
        color: var(--green);
        background: var(--green-light);
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
    }

    /* Product Table */
    .detail-table {
        width: 100%;
        border-collapse: collapse;
    }

    .detail-table th {
        padding: 15px 10px;
        text-align: left;
        border-bottom: 2px solid var(--border);
        color: var(--text-muted);
        font-size: 0.9rem;
    }

    .detail-table td {
        padding: 20px 10px;
        border-bottom: 1px solid var(--border);
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        color: var(--text-body);
    }

    .summary-total {
        display: flex;
        justify-content: space-between;
        padding: 15px 0;
        margin-top: 10px;
        border-top: 2px solid var(--border);
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--green);
    }

    .info-item {
        margin-bottom: 15px;
    }

    .info-item label {
        display: block;
        font-size: 0.85rem;
        color: var(--text-muted);
        margin-bottom: 4px;
    }

    .info-item span {
        font-weight: 600;
        color: var(--text-main);
    }

    .status-badge-big {
        display: inline-block;
        padding: 8px 16px;
        border-radius: 10px;
        font-weight: 700;
        margin-bottom: 15px;
    }

    @media (max-width: 992px) {
        .profile-container { grid-template-columns: 1fr; }
        .detail-grid { grid-template-columns: 1fr !important; }
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
                    <?= strtoupper(substr($_SESSION['user']['name'], 0, 1)) ?>
                </div>
            </div>
            <div style="text-align: center;">
                <h3 style="font-weight: 700; margin-bottom: 5px;"><?= htmlspecialchars($_SESSION['user']['name']) ?></h3>
                <p style="color: var(--text-muted); font-size: 0.9rem;"><?= htmlspecialchars($_SESSION['email'] ?? 'Khách hàng') ?></p>
            </div>
            
            <ul class="sidebar-menu">
                <li><a href="<?= BASE_URL ?>account"><span>👤</span> Thông tin cá nhân</a></li>
                <li><a href="<?= BASE_URL ?>account/orders" class="active"><span>📦</span> Lịch sử đơn hàng</a></li>
                <li><a href="<?= BASE_URL ?>cart"><span>🛒</span> Giỏ hàng của tôi</a></li>
                <li style="margin-top: 20px; border-top: 1px solid var(--border); padding-top: 20px;">
                    <a href="<?= BASE_URL ?>auth/logout" style="color: var(--red);"><span>🚪</span> Đăng xuất</a>
                </li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="detail-main-content">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h2 style="font-weight: 800;">Chi tiết đơn hàng #<?= $order['order_id'] ?></h2>
                <a href="<?= BASE_URL ?>account/orders" class="btn btn-outline" style="border-radius: 10px;">← Quay lại</a>
            </div>

            <div class="detail-grid" style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
                
                <!-- Left: Products -->
                <div class="glass-card detail-card">
                    <div class="section-title-premium">
                        <i>💊</i>
                        <span>Sản phẩm trong đơn</span>
                    </div>

                    <table class="detail-table">
                        <thead>
                            <tr>
                                <th>Sản phẩm</th>
                                <th style="text-align: center;">SL</th>
                                <th style="text-align: right;">Đơn giá</th>
                                <th style="text-align: right;">Tổng</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($details as $d): ?>
                                <tr>
                                    <td>
                                        <div style="font-weight: 700;"><?= htmlspecialchars($d['product_name']) ?></div>
                                        <small style="color: var(--text-muted);">Mã SP: <?= $d['product_id'] ?></small>
                                    </td>
                                    <td style="text-align: center; font-weight: 600;"><?= $d['quantity'] ?></td>
                                    <td style="text-align: right;"><?= number_format($d['unit_price']) ?>đ</td>
                                    <td style="text-align: right; font-weight: 700; color: var(--green);">
                                        <?= number_format($d['unit_price'] * $d['quantity']) ?>đ
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <div style="margin-top: 30px; max-width: 300px; margin-left: auto;">
                        <div class="summary-row">
                            <span>Tạm tính:</span>
                            <span><?= number_format($order['subtotal']) ?>đ</span>
                        </div>
                        <div class="summary-row">
                            <span>Phí vận chuyển:</span>
                            <span><?= number_format($order['total_amount'] - $order['subtotal']) ?>đ</span>
                        </div>
                        <div class="summary-total">
                            <span>Tổng cộng</span>
                            <span><?= number_format($order['total_amount']) ?>đ</span>
                        </div>
                    </div>

                    <?php if ($order['has_prescription']): ?>
                    <div style="margin-top: 40px; padding-top: 30px; border-top: 1px dashed var(--border);">
                        <div class="section-title-premium">
                            <i>📋</i>
                            <span>Hình ảnh đơn thuốc</span>
                        </div>
                        <a href="<?= BASE_URL ?>prescription/view?file=<?= htmlspecialchars($order['prescription_image']) ?>" target="_blank" style="display: inline-block; padding: 10px; border: 1px solid var(--border); border-radius: 15px;">
                            <img src="<?= BASE_URL ?>prescription/view?file=<?= htmlspecialchars($order['prescription_image']) ?>" style="max-width: 250px; border-radius: 10px;">
                        </a>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Right: Information -->
                <div style="display: flex; flex-direction: column; gap: 30px;">
                    
                    <div class="glass-card detail-card">
                        <div class="section-title-premium">
                            <i>🚚</i>
                            <span>Giao hàng</span>
                        </div>
                        <div class="info-item">
                            <label>Người nhận</label>
                            <span><?= htmlspecialchars($order['shipping_name']) ?></span>
                        </div>
                        <div class="info-item">
                            <label>Số điện thoại</label>
                            <span><?= htmlspecialchars($order['shipping_phone']) ?></span>
                        </div>
                        <div class="info-item">
                            <label>Địa chỉ</label>
                            <span><?= htmlspecialchars($order['shipping_address']) ?></span>
                        </div>
                        <div class="info-item" style="margin-bottom: 0;">
                            <label>Ghi chú</label>
                            <span style="font-style: italic; color: var(--text-muted);"><?= htmlspecialchars($order['shipping_note']) ?: 'Không có ghi chú' ?></span>
                        </div>
                    </div>

                    <div class="glass-card detail-card">
                        <div class="section-title-premium">
                            <i>⭐</i>
                            <span>Trạng thái</span>
                        </div>
                        <?php 
                            $statusMap = [
                                'pending' => ['text' => 'Chờ xử lý', 'bg' => '#fff8e1', 'color' => '#ffa000'],
                                'confirmed' => ['text' => 'Đã xác nhận', 'bg' => '#e3f2fd', 'color' => '#1976d2'],
                                'shipping' => ['text' => 'Đang giao', 'bg' => '#f3e5f5', 'color' => '#7b1fa2'],
                                'completed' => ['text' => 'Đã hoàn thành', 'bg' => '#e8f5e9', 'color' => '#388e3c'],
                                'cancelled' => ['text' => 'Đã hủy', 'bg' => '#ffebee', 'color' => '#d32f2f']
                            ];
                            $st = $statusMap[$order['status']] ?? ['text' => $order['status'], 'bg' => '#f5f5f5', 'color' => '#757575'];
                        ?>
                        <div class="status-badge-big" style="background: <?= $st['bg'] ?>; color: <?= $st['color'] ?>;">
                            <?= $st['text'] ?>
                        </div>
                        
                        <div class="info-item">
                            <label>Thanh toán</label>
                            <span style="<?= $order['payment_status'] == 'paid' ? 'color: var(--green);' : 'color: var(--red);' ?>">
                                <?= $order['payment_status'] == 'paid' ? '✅ Đã thanh toán' : '⏳ Thanh toán khi nhận hàng (COD)' ?>
                            </span>
                        </div>

                        <?php if ($order['has_prescription'] || $order['prescription_verified']): ?>
                        <div class="info-item" style="padding: 10px; border-radius: 10px; background: #f0fdf4; border: 1px solid #dcfce7; margin-top: 15px;">
                            <label>Kiểm duyệt y tế</label>
                            <?php if ($order['prescription_verified']): ?>
                                <span style="color: var(--green); display: block; font-size: 0.9rem;">✅ Đã được Dược sĩ phê duyệt</span>
                                <small style="color: #666; font-size: 0.75rem;">Thời gian: <?= date('d/m/Y H:i', strtotime($order['verified_at'])) ?></small>
                            <?php elseif ($order['status'] == 'cancelled' && strpos($order['admin_note'], 'Đơn thuốc không hợp lệ') !== false): ?>
                                <span style="color: var(--red); display: block; font-size: 0.9rem;">❌ Bị từ chối (Đơn thuốc không hợp lệ)</span>
                            <?php else: ?>
                                <span style="color: var(--orange); display: block; font-size: 0.9rem;">⏳ Đang chờ Dược sĩ kiểm tra</span>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                        
                        <div class="info-item">
                            <label>Ngày đặt hàng</label>
                            <span><?= date('d/m/Y - H:i', strtotime($order['order_date'])) ?></span>
                        </div>

                        <?php if ($order['status'] == 'pending'): ?>
                        <form action="<?= BASE_URL ?>account/cancel_order" method="POST" onsubmit="return confirm('Bạn có chắc muốn hủy đơn này?');" style="margin-top: 20px;">
                            <?= $csrf_field ?>
                            <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                            <button type="submit" class="btn btn-outline" style="width: 100%; border-color: var(--red); color: var(--red); border-radius: 12px; font-weight: 700;">Hủy đơn hàng</button>
                        </form>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
        </main>
    </div>
</div>

<div style="margin-bottom: 80px;"></div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
