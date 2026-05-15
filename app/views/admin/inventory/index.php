<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="container section animate-fade-up">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h1 class="section-title">Quản lý kho & Lô hàng (FEFO)</h1>
        <?php if (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 3): ?>
            <a href="<?= BASE_URL ?>admin/inventory/import" class="btn btn-premium">Nhập hàng mới</a>
        <?php endif; ?>
    </div>

    <!-- Cảnh báo hạn sử dụng -->
    <div class="glass-card" style="padding: 20px; margin-bottom: 40px; border-left: 5px solid var(--red);">
        <h3 style="color: var(--red); margin-bottom: 15px;">⚠️ Cảnh báo hạn sử dụng (90 ngày)</h3>
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="text-align: left; border-bottom: 1px solid var(--border);">
                    <th style="padding: 10px;">Sản phẩm</th>
                    <th style="padding: 10px;">Số lô</th>
                    <th style="padding: 10px;">Hạn sử dụng</th>
                    <th style="padding: 10px;">Số ngày còn</th>
                    <th style="padding: 10px;">Tồn kho</th>
                    <th style="padding: 10px;">Trạng thái</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($expiry_alerts as $a): ?>
                    <tr style="border-bottom: 1px solid var(--border);">
                        <td style="padding: 10px;"><strong><?= htmlspecialchars($a['product_name']) ?></strong></td>
                        <td style="padding: 10px;"><?= htmlspecialchars($a['batch_number']) ?></td>
                        <td style="padding: 10px;"><?= date('d/m/Y', strtotime($a['expiry_date'])) ?></td>
                        <td style="padding: 10px;"><?= $a['days_to_expiry'] ?> ngày</td>
                        <td style="padding: 10px;"><?= $a['quantity_remaining'] ?></td>
                        <td style="padding: 10px;">
                            <span style="padding: 3px 8px; border-radius: 12px; font-size: 0.8rem; background: <?= $a['alert_level'] == 'danger' ? '#ffebee; color: #c62828;' : '#fff3e0; color: #ef6c00;' ?>">
                                <?= $a['status'] ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Tóm tắt tồn kho -->
    <div class="glass-card" style="padding: 20px;">
        <h3 style="margin-bottom: 20px;">Tóm tắt tồn kho tổng quát</h3>
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="text-align: left; border-bottom: 1px solid var(--border);">
                    <th style="padding: 10px;">Sản phẩm</th>
                    <th style="padding: 10px;">Danh mục</th>
                    <th style="padding: 10px;">Tổng tồn</th>
                    <th style="padding: 10px;">Hạn gần nhất</th>
                    <th style="padding: 10px;">Giá trị kho</th>
                    <th style="padding: 10px;">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($summary as $s): ?>
                    <tr style="border-bottom: 1px solid var(--border);">
                        <td style="padding: 10px;">
                            <strong><?= htmlspecialchars($s['product_name']) ?></strong><br>
                            <small style="color: var(--text-muted);"><?= htmlspecialchars($s['generic_name']) ?></small>
                        </td>
                        <td style="padding: 10px;"><?= htmlspecialchars($s['category_name']) ?></td>
                        <td style="padding: 10px;">
                            <span style="<?= $s['total_quantity'] < 10 ? 'color: var(--red); font-weight: bold;' : '' ?>">
                                <?= $s['total_quantity'] ?>
                            </span>
                        </td>
                        <td style="padding: 10px;"><?= $s['earliest_expiry'] ? date('d/m/Y', strtotime($s['earliest_expiry'])) : 'N/A' ?></td>
                        <td style="padding: 10px;"><?= number_format($s['inventory_value']) ?>đ</td>
                        <td style="padding: 10px;">
                            <a href="<?= BASE_URL ?>admin/inventory/productBatches?id=<?= $s['product_id'] ?>" style="color: var(--blue);">Xem lô hàng</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../../layout/footer.php'; ?>
