<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="container section animate-fade-up">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h1 class="section-title">Chi tiết lô hàng: <?= htmlspecialchars($product_name) ?></h1>
        <div style="display: flex; gap: 10px;">
            <?php if (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 3): ?>
                <a href="<?= BASE_URL ?>admin/inventory/import?product_id=<?= $_GET['id'] ?>" class="btn btn-premium" style="background: var(--blue);">➕ Nhập thêm hàng</a>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>admin/inventory" class="btn" style="background: #f1f5f9; border: 1px solid #cbd5e1; color: #475569; font-weight: 600;">⬅️ Quay lại danh sách</a>
        </div>
    </div>

    <div class="glass-card" style="padding: 20px;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="text-align: left; border-bottom: 1px solid var(--border);">
                    <th style="padding: 10px;">Số lô</th>
                    <th style="padding: 10px;">NSX</th>
                    <th style="padding: 10px;">HSD</th>
                    <th style="padding: 10px;">SL Nhập</th>
                    <th style="padding: 10px;">SL Còn lại</th>
                    <th style="padding: 10px;">Giá nhập</th>
                    <th style="padding: 10px;">Vị trí</th>
                    <th style="padding: 10px;">Trạng thái</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($batches as $b): ?>
                    <tr style="border-bottom: 1px solid var(--border);">
                        <td style="padding: 10px;"><strong><?= htmlspecialchars($b['batch_number']) ?></strong></td>
                        <td style="padding: 10px;"><?= date('d/m/Y', strtotime($b['manufacture_date'])) ?></td>
                        <td style="padding: 10px; color: <?= strtotime($b['expiry_date']) < time() ? 'var(--red)' : 'inherit' ?>;">
                            <?= date('d/m/Y', strtotime($b['expiry_date'])) ?>
                        </td>
                        <td style="padding: 10px;"><?= $b['quantity_received'] ?></td>
                        <td style="padding: 10px;">
                            <span style="font-weight: bold; color: <?= $b['quantity_remaining'] == 0 ? 'var(--red)' : 'var(--green)' ?>">
                                <?= $b['quantity_remaining'] ?>
                            </span>
                        </td>
                        <td style="padding: 10px;"><?= number_format($b['purchase_price']) ?>đ</td>
                        <td style="padding: 10px;"><?= htmlspecialchars($b['storage_location']) ?></td>
                        <td style="padding: 10px;">
                            <span style="padding: 3px 8px; border-radius: 12px; font-size: 0.8rem; background: <?= $b['status'] == 'active' ? '#e8f5e9; color: #2e7d32;' : ($b['status'] == 'expired' ? '#ffebee; color: #c62828;' : '#eceff1; color: #546e7a;') ?>">
                                <?= $b['status'] ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($batches)): ?>
                    <tr>
                        <td colspan="8" style="padding: 20px; text-align: center; color: var(--text-muted);">Sản phẩm này chưa có lô hàng nào.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../../layout/footer.php'; ?>
