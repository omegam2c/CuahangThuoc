<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="container section animate-fade-up">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h1 class="section-title">Phê duyệt đơn hàng theo toa</h1>
        <a href="<?= BASE_URL ?>doctor/createPrescription" class="btn btn-premium" style="background: var(--green);">+ Kê đơn thuốc mới</a>
    </div>

    <div>
        <?php if (empty($orders)): ?>
            <div class="glass-card" style="padding: 40px; text-align: center;">
                <p>Hiện không có đơn hàng nào cần xử lý đơn thuốc.</p>
            </div>
        <?php else: ?>
            <div style="display: grid; gap: 20px;">
                <?php foreach ($orders as $o): ?>
                    <div class="glass-card" style="padding: 25px; display: grid; grid-template-columns: 1fr 2fr 1fr; gap: 20px; align-items: center; border-left: 5px solid <?= ($o['status'] == 'cancelled') ? 'var(--red)' : (($o['prescription_verified']) ? 'var(--green)' : 'var(--orange)') ?>;">
                        <!-- Ảnh đơn thuốc -->
                        <div>
                            <img src="<?= BASE_URL ?>prescription/view?file=<?= htmlspecialchars($o['prescription_image']) ?>" alt="Đơn thuốc" style="width: 100%; border-radius: var(--radius); cursor: pointer;" onclick="window.open(this.src)">
                            <small style="display: block; text-align: center; margin-top: 5px; color: var(--text-muted);">Ảnh toa thuốc của khách</small>
                        </div>

                        <!-- Thông tin đơn hàng -->
                        <div>
                            <div style="display: flex; justify-content: space-between;">
                                <h3>Yêu cầu #<?= $o['order_id'] ?></h3>
                                <?php if ($o['status'] == 'cancelled'): ?>
                                    <span style="color: var(--red); font-weight: bold;">❌ ĐÃ TỪ CHỐI</span>
                                <?php elseif ($o['prescription_verified']): ?>
                                    <span style="color: var(--green); font-weight: bold;">✅ ĐÃ PHÊ DUYỆT</span>
                                <?php else: ?>
                                    <span style="color: var(--orange); font-weight: bold;">⏳ CHỜ XỬ LÝ</span>
                                <?php endif; ?>
                            </div>
                            <p><strong>Khách hàng:</strong> <?= htmlspecialchars($o['customer_name']) ?> - <?= htmlspecialchars($o['customer_phone']) ?></p>
                            <p><strong>Ngày gửi:</strong> <?= date('d/m/Y H:i', strtotime($o['order_date'])) ?></p>
                            <p><strong>Địa chỉ:</strong> <?= htmlspecialchars($o['shipping_address']) ?></p>
                        
                            <div style="margin-top: 15px; background: #f8fafc; border-radius: 12px; padding: 15px; border: 1px solid #e2e8f0;">
                                <h4 style="font-size: 0.85rem; color: #64748b; text-transform: uppercase; margin-bottom: 10px;">Sản phẩm khách chọn:</h4>
                                <ul style="list-style: none; padding: 0; margin: 0;">
                                    <?php foreach ($o['items'] as $item): ?>
                                        <li style="display: flex; justify-content: space-between; padding: 5px 0; border-bottom: 1px dashed #cbd5e1; font-size: 0.95rem;">
                                            <span><strong><?= htmlspecialchars($item['product_name']) ?></strong></span>
                                            <span style="color: var(--blue);">x<?= $item['quantity'] ?> <?= $item['unit'] ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                    <?php if (empty($o['items'])): ?>
                                        <li style="color: #94a3b8; font-style: italic; font-size: 0.9rem;">Chưa chọn sản phẩm (Chỉ gửi toa)</li>
                                    <?php endif; ?>
                                </ul>
                                <div style="margin-top: 10px; text-align: right; font-weight: bold; color: var(--green);">
                                    Tổng tiền: <?= number_format($o['total_amount']) ?>đ
                                </div>
                            </div>

                            <?php if ($o['status'] == 'cancelled'): ?>
                                <p style="color: var(--red); font-size: 0.9rem; margin-top: 5px;">Lý do: <?= htmlspecialchars($o['admin_note']) ?></p>
                            <?php endif; ?>
                        </div>

                        <!-- Hành động -->
                        <div style="display: flex; flex-direction: column; gap: 10px;">
                            <?php if (!$o['prescription_verified'] && $o['status'] != 'cancelled'): ?>
                                <!-- Chấp nhận nhanh -->
                                <form action="<?= BASE_URL ?>doctor/verify" method="POST">
                                    <?= $csrf_field ?>
                                    <input type="hidden" name="order_id" value="<?= $o['order_id'] ?>">
                                    <input type="hidden" name="status" value="approved">
                                    <button type="submit" class="btn btn-premium" style="width: 100%; background: var(--green);">✅ Chấp nhận đơn này</button>
                                </form>

                                <!-- Chuyển sang kê toa chi tiết nếu cần -->
                                <a href="<?= BASE_URL ?>doctor/createPrescription?user_id=<?= $o['user_id'] ?>&order_id=<?= $o['order_id'] ?>" class="btn btn-outline" style="width: 100%; text-align: center; text-decoration: none; color: var(--blue); border-color: var(--blue);">📝 Kê toa & Chỉnh sửa</a>

                                <!-- Từ chối -->
                                <form action="<?= BASE_URL ?>doctor/verify" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn TỪ CHỐI đơn thuốc này không?');">
                                    <?= $csrf_field ?>
                                    <input type="hidden" name="order_id" value="<?= $o['order_id'] ?>">
                                    <input type="hidden" name="status" value="rejected">
                                    <button type="submit" class="btn btn-outline" style="width: 100%; border-color: var(--red); color: var(--red); font-size: 0.85rem;">✕ Từ chối đơn</button>
                                </form>
                            <?php else: ?>
                                <button class="btn btn-outline" disabled style="width: 100%; opacity: 0.5;">Đã xử lý</button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>