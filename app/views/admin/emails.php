<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="container section animate-fade-up">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h1 class="section-title">Quản lý Email Hệ thống</h1>
        <div style="display: flex; gap: 10px;">
            <a href="<?= BASE_URL ?>admin/dashboard" class="btn" style="background: var(--blue); color: white;">📊 Thống kê</a>
            <a href="<?= BASE_URL ?>admin/order" class="btn" style="border: 1px solid #666; color: #333; background: #fff;">🧾 Đơn hàng</a>
        </div>
    </div>

    <!-- Bộ lọc -->
    <div class="glass-card" style="padding: 20px; margin-bottom: 20px;">
        <form action="<?= BASE_URL ?>admin/emails" method="GET" style="display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: 15px;">
            <input type="text" name="q" placeholder="Tìm kiếm Email, Tiêu đề hoặc Mã ĐH..." class="form-control" value="<?= htmlspecialchars($q ?? '') ?>">
            
            <select name="type" class="form-control">
                <option value="">Tất cả loại Mail</option>
                <option value="order_confirmation" <?= ($type ?? '') == 'order_confirmation' ? 'selected' : '' ?>>Xác nhận đơn hàng</option>
                <option value="order_completed" <?= ($type ?? '') == 'order_completed' ? 'selected' : '' ?>>Đơn hàng hoàn thành</option>
                <option value="payment_success" <?= ($type ?? '') == 'payment_success' ? 'selected' : '' ?>>Thanh toán thành công</option>
            </select>

            <select name="status" class="form-control">
                <option value="">Tất cả trạng thái</option>
                <option value="sent" <?= ($status ?? '') == 'sent' ? 'selected' : '' ?>>Đã gửi</option>
                <option value="failed" <?= ($status ?? '') == 'failed' ? 'selected' : '' ?>>Gửi lỗi</option>
                <option value="opened" <?= ($status ?? '') == 'opened' ? 'selected' : '' ?>>Đã đọc</option>
            </select>

            <button type="submit" class="btn btn-primary">Lọc dữ liệu</button>
        </form>
    </div>

    <!-- Bảng nhật ký Email -->
    <div class="glass-card" style="padding: 20px; overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
            <thead>
                <tr style="text-align: left; border-bottom: 2px solid var(--border);">
                    <th style="padding: 12px;">ID</th>
                    <th style="padding: 12px;">Người nhận</th>
                    <th style="padding: 12px;">Tiêu đề</th>
                    <th style="padding: 12px;">Loại</th>
                    <th style="padding: 12px;">Trạng thái</th>
                    <th style="padding: 12px;">Thời gian</th>
                    <th style="padding: 12px;">Mã ĐH</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                <tr style="border-bottom: 1px solid var(--border);">
                    <td style="padding: 12px; color: #888;">#<?= $log['id'] ?></td>
                    <td style="padding: 12px;">
                        <strong><?= htmlspecialchars($log['recipient_email']) ?></strong>
                    </td>
                    <td style="padding: 12px;"><?= htmlspecialchars($log['subject']) ?></td>
                    <td style="padding: 12px;">
                        <span style="font-size: 0.8rem; padding: 2px 6px; background: #f0f0f0; border-radius: 4px;">
                            <?= $log['type'] ?>
                        </span>
                    </td>
                    <td style="padding: 12px;">
                        <?php if ($log['status'] == 'sent'): ?>
                            <span style="color: var(--blue); font-weight: bold;">📩 Đã gửi</span>
                        <?php elseif ($log['status'] == 'opened'): ?>
                            <span style="color: var(--green); font-weight: bold;">👁️ Đã đọc</span>
                        <?php else: ?>
                            <span style="color: var(--red); font-weight: bold;" title="<?= htmlspecialchars($log['error_message']) ?>">❌ Lỗi</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 12px; color: #666; font-size: 0.85rem;">
                        <?= date('d/m/Y H:i:s', strtotime($log['sent_at'])) ?>
                    </td>
                    <td style="padding: 12px;">
                        <?php if ($log['order_id']): ?>
                            <a href="<?= BASE_URL ?>admin/order/detail?id=<?= $log['order_id'] ?>" style="color: var(--blue); font-weight: bold;">#<?= $log['order_id'] ?></a>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($logs)): ?>
                <tr>
                    <td colspan="7" style="padding: 40px; text-align: center; color: #999;">Không tìm thấy nhật ký email nào.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
