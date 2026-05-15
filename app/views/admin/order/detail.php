<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="container section animate-fade-up">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h1 class="section-title">Chi tiết đơn hàng #<?= $order['order_id'] ?></h1>
        <div style="display: flex; gap: 10px;">
            <a href="<?= BASE_URL ?>" class="btn" style="background: var(--blue); color: white;">🏠 Trang chính</a>
            <a href="<?= BASE_URL ?>admin/order" class="btn" style="border: 1px solid #666; color: #333; background: #fff;">⬅️ Quay lại danh sách</a>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
        <!-- Cột trái: Chi tiết sản phẩm -->
        <div>
            <div class="glass-card" style="padding: 20px;">
                <h3 style="margin-bottom: 20px;">Danh sách sản phẩm</h3>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="text-align: left; border-bottom: 1px solid var(--border);">
                            <th style="padding: 10px;">Sản phẩm</th>
                            <th style="padding: 10px;">Lô (FEFO)</th>
                            <th style="padding: 10px;">Đơn giá</th>
                            <th style="padding: 10px;">SL</th>
                            <th style="padding: 10px;">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($details as $d): ?>
                            <tr style="border-bottom: 1px solid var(--border);">
                                <td style="padding: 10px;"><strong><?= htmlspecialchars($d['product_name']) ?></strong></td>
                                <td style="padding: 10px; color: var(--blue);"><?= htmlspecialchars($d['batch_number'] ?? 'N/A') ?></td>
                                <td style="padding: 10px;"><?= number_format($d['unit_price']) ?>đ</td>
                                <td style="padding: 10px;"><?= $d['quantity'] ?></td>
                                <td style="padding: 10px; font-weight: bold;"><?= number_format($d['unit_price'] * $d['quantity']) ?>đ</td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($details)): ?>
                            <tr>
                                <td colspan="5" style="padding: 20px; text-align: center;">Đơn hàng này không có sản phẩm (chỉ có đơn thuốc).</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <div style="margin-top: 20px; text-align: right; font-size: 1.1rem;">
                    Tổng phụ: <strong><?= number_format($order['subtotal']) ?>đ</strong><br>
                    Phí ship: <strong><?= number_format($order['total_amount'] - $order['subtotal']) ?>đ</strong><br>
                    <h3 style="color: var(--green); margin-top: 10px;">Tổng cộng: <?= number_format($order['total_amount']) ?>đ</h3>
                </div>
            </div>

            <?php if ($order['has_prescription']): ?>
            <div class="glass-card" style="padding: 20px; margin-top: 30px; border: 2px solid <?= $order['prescription_verified'] ? 'var(--green)' : 'var(--orange)' ?>;">
                <h3 style="margin-bottom: 20px;">Đơn thuốc đính kèm</h3>
                <div style="display: flex; gap: 20px;">
                    <a href="<?= BASE_URL ?>prescription/view?file=<?= htmlspecialchars($order['prescription_image']) ?>" target="_blank">
                        <img src="<?= BASE_URL ?>prescription/view?file=<?= htmlspecialchars($order['prescription_image']) ?>" style="max-width: 300px; border-radius: 8px; border: 1px solid var(--border);">
                    </a>
                    <div>
                        <p><strong>Trạng thái đơn thuốc:</strong> 
                            <?php if ($order['prescription_verified']): ?>
                                <span style="display: inline-block; padding: 4px 12px; background: #e8f5e9; color: #2e7d32; border-radius: 20px; font-weight: bold; font-size: 0.85rem;">✅ ĐÃ PHÊ DUYỆT</span>
                                <br><small class="text-muted">Duyệt bởi: Dược sĩ (ID: <?= $order['verified_by'] ?>) vào <?= date('d/m/Y H:i', strtotime($order['verified_at'])) ?></small>
                            <?php elseif ($order['status'] == 'cancelled' && strpos($order['admin_note'], 'Đơn thuốc không hợp lệ') !== false): ?>
                                <span style="display: inline-block; padding: 4px 12px; background: #ffebee; color: #c62828; border-radius: 20px; font-weight: bold; font-size: 0.85rem;">❌ BỊ TỪ CHỐI</span>
                                <br><small style="color: var(--red);">Lý do: Đơn thuốc không hợp lệ</small>
                            <?php else: ?>
                                <span style="display: inline-block; padding: 4px 12px; background: #fff3e0; color: #ef6c00; border-radius: 20px; font-weight: bold; font-size: 0.85rem;">⏳ CHỜ DUYỆT</span>
                            <?php endif; ?>
                        </p>
                        
                        <?php if (!$order['prescription_verified'] && $_SESSION['role_id'] == 2): ?>
                        <form action="<?= BASE_URL ?>admin/order/verifyPrescription" method="POST" style="margin-top: 20px;">
                            <?= $csrf_field ?>
                            <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                            <button type="submit" class="btn btn-premium" style="background: var(--green);">Xác nhận Đơn thuốc hợp lệ</button>
                        </form>
                        <?php elseif (!$order['prescription_verified'] && $_SESSION['role_id'] != 2): ?>
                            <p style="margin-top: 20px; color: var(--orange); font-style: italic;">⚠️ Chỉ Dược sĩ mới có quyền phê duyệt đơn thuốc này.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Cột phải: Thông tin giao hàng -->
        <div class="glass-card" style="padding: 20px; height: fit-content;">
            <h3 style="margin-bottom: 20px;">Thông tin khách hàng</h3>
            <p><strong>Tài khoản:</strong> <?= htmlspecialchars($order['customer_name']) ?> (<?= htmlspecialchars($order['customer_email']) ?>)</p>
            <p><strong>Người nhận:</strong> <?= htmlspecialchars($order['shipping_name']) ?></p>
            <p><strong>SĐT:</strong> <?= htmlspecialchars($order['shipping_phone']) ?></p>
            <p><strong>Địa chỉ:</strong> <?= htmlspecialchars($order['shipping_address']) ?></p>
            <p><strong>Ghi chú:</strong> <?= htmlspecialchars($order['shipping_note']) ?: 'Không có' ?></p>
            
            <hr style="margin: 20px 0; border: none; border-top: 1px solid var(--border);">
            
            <h3 style="margin-bottom: 20px;">Trạng thái đơn hàng</h3>
            <form action="<?= BASE_URL ?>admin/order/updateStatus" method="POST">
                <?= $csrf_field ?>
                <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                <div class="form-group">
                    <select name="status" class="form-control">
                        <option value="pending" <?= $order['status'] == 'pending' ? 'selected' : '' ?>>Chờ xử lý</option>
                        <option value="confirmed" <?= $order['status'] == 'confirmed' ? 'selected' : '' ?>>Đã xác nhận</option>
                        <option value="shipping" <?= $order['status'] == 'shipping' ? 'selected' : '' ?>>Đang giao hàng</option>
                        <option value="completed" <?= $order['status'] == 'completed' ? 'selected' : '' ?>>Đã hoàn thành</option>
                        <option value="cancelled" <?= $order['status'] == 'cancelled' ? 'selected' : '' ?>>Đã hủy</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 10px;">Cập nhật trạng thái</button>
            </form>
            
            <p style="margin-top: 20px;"><strong>Trạng thái thanh toán:</strong> 
                <span style="color: <?= $order['payment_status'] == 'paid' ? 'var(--green)' : 'var(--red)' ?>; font-weight: bold;">
                    <?= strtoupper($order['payment_status']) ?>
                </span>
            </p>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../layout/footer.php'; ?>
