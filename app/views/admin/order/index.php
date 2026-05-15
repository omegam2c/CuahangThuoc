<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="container section animate-fade-up">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h1 class="section-title">Quản lý đơn hàng</h1>
        <a href="<?= BASE_URL ?>" class="btn" style="background: var(--blue); color: white; box-shadow: 0 4px 6px rgba(59,130,246,0.3);">🏠 Trang chính</a>
    </div>

    <div class="glass-card" style="padding: 20px; margin-top: 30px; margin-bottom: 20px;">
        <form action="<?= BASE_URL ?>admin/order" method="GET" style="display: flex; gap: 15px;">
            <input type="text" name="q" placeholder="Tìm theo Mã ĐH, Tên KH, hoặc SĐT..." class="form-control" style="flex: 2;" value="<?= htmlspecialchars($q ?? '') ?>">
            <select name="status" class="form-control" style="flex: 1;">
                <option value="">Tất cả trạng thái</option>
                <option value="pending" <?= (isset($status) && $status == 'pending') ? 'selected' : '' ?>>Chờ xử lý</option>
                <option value="confirmed" <?= (isset($status) && $status == 'confirmed') ? 'selected' : '' ?>>Đã xác nhận</option>
                <option value="shipping" <?= (isset($status) && $status == 'shipping') ? 'selected' : '' ?>>Đang giao</option>
                <option value="completed" <?= (isset($status) && $status == 'completed') ? 'selected' : '' ?>>Đã hoàn thành</option>
                <option value="cancelled" <?= (isset($status) && $status == 'cancelled') ? 'selected' : '' ?>>Đã hủy</option>
            </select>
            <button type="submit" class="btn btn-primary">Tìm kiếm</button>
        </form>
    </div>

    <div class="glass-card" style="padding: 20px;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="text-align: left; border-bottom: 1px solid var(--border);">
                    <th style="padding: 10px;">Mã ĐH</th>
                    <th style="padding: 10px;">Khách hàng</th>
                    <th style="padding: 10px;">Ngày đặt</th>
                    <th style="padding: 10px;">Tổng tiền</th>
                    <th style="padding: 10px;">Trạng thái</th>
                    <th style="padding: 10px;">Thanh toán</th>
                    <th style="padding: 10px;">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $o): ?>
                    <tr style="border-bottom: 1px solid var(--border);">
                        <td style="padding: 10px;">
                            <strong>#<?= $o['order_id'] ?></strong>
                            <?php if ($o['has_prescription']): ?>
                                <br>
                                <span style="font-size: 0.7rem; padding: 2px 5px; border-radius: 4px; background: <?= $o['prescription_verified'] ? '#e8f5e9; color: #2e7d32;' : '#fff3e0; color: #e65100;' ?>">
                                    <?= $o['prescription_verified'] ? '✅ Đã duyệt Rx' : '⏳ Chờ duyệt Rx' ?>
                                </span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 10px;"><?= htmlspecialchars($o['customer_name']) ?></td>
                        <td style="padding: 10px;"><?= date('d/m/Y H:i', strtotime($o['order_date'])) ?></td>
                        <td style="padding: 10px; color: var(--green); font-weight: bold;"><?= number_format($o['total_amount']) ?>đ</td>
                        <td style="padding: 10px;">
                            <form action="<?= BASE_URL ?>admin/order/updateStatus" method="POST">
                                <?= $csrf_field ?>
                                <input type="hidden" name="order_id" value="<?= $o['order_id'] ?>">
                                <select name="status" onchange="this.form.submit()" style="padding: 5px; border-radius: 5px;">
                                    <option value="pending" <?= $o['status'] == 'pending' ? 'selected' : '' ?>>Chờ xử lý</option>
                                    <option value="confirmed" <?= $o['status'] == 'confirmed' ? 'selected' : '' ?>>Đã xác nhận</option>
                                    <option value="shipping" <?= $o['status'] == 'shipping' ? 'selected' : '' ?>>Đang giao</option>
                                    <option value="completed" <?= $o['status'] == 'completed' ? 'selected' : '' ?>>Đã hoàn thành</option>
                                    <option value="cancelled" <?= $o['status'] == 'cancelled' ? 'selected' : '' ?>>Đã hủy</option>
                                </select>
                            </form>
                        </td>
                        <td style="padding: 10px;">
                            <span style="font-size: 0.85rem; padding: 3px 8px; border-radius: 12px; background: <?= $o['payment_status'] == 'paid' ? '#e8f5e9; color: #2e7d32;' : '#ffebee; color: #c62828;' ?>">
                                <?= $o['payment_status'] ?>
                            </span>
                        </td>
                        <td style="padding: 10px;">
                            <a href="<?= BASE_URL ?>admin/order/detail?id=<?= $o['order_id'] ?>" style="color: var(--blue);">Chi tiết</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="7" style="padding: 40px; text-align: center; color: var(--text-muted);">
                            <div style="font-size: 3rem; margin-bottom: 10px;">🔍</div>
                            <p>Không tìm thấy đơn hàng nào phù hợp.</p>
                            <?php if ($_SESSION['role_id'] == 2): ?>
                                <p style="font-size: 0.85rem; color: var(--orange);">
                                    <i class="fas fa-info-circle"></i> Bạn đang đăng nhập với quyền <strong>Dược sĩ</strong>, hệ thống chỉ hiển thị các đơn hàng có toa thuốc (Rx).
                                </p>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../../layout/footer.php'; ?>
