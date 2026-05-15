<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="container section animate-fade-up">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h1 class="section-title">Danh sách sản phẩm</h1>
        <a href="<?= BASE_URL ?>admin/product/create" class="btn btn-premium">Thêm sản phẩm mới</a>
    </div>

    <div class="glass-card" style="padding: 20px; margin-bottom: 20px;">
        <form action="<?= BASE_URL ?>admin/product" method="GET" style="display: flex; gap: 15px;">
            <input type="text" name="q" placeholder="Tìm tên sản phẩm hoặc ID..." class="form-control" style="flex: 2;" value="<?= htmlspecialchars($q ?? '') ?>">
            <select name="category_id" class="form-control" style="flex: 1;">
                <option value="">Tất cả danh mục</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['category_id'] ?>" <?= (isset($category_id) && $category_id == $cat['category_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['category_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <select name="is_rx" class="form-control" style="flex: 1;">
                <option value="">Tất cả loại</option>
                <option value="1" <?= (isset($is_rx) && $is_rx === '1') ? 'selected' : '' ?>>Thuốc kê đơn (Rx)</option>
                <option value="0" <?= (isset($is_rx) && $is_rx === '0') ? 'selected' : '' ?>>Thuốc không kê đơn (OTC)</option>
            </select>
            <button type="submit" class="btn btn-primary">Tìm kiếm</button>
        </form>
    </div>


    <div class="glass-card" style="padding: 20px;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="text-align: left; border-bottom: 1px solid var(--border);">
                    <th style="padding: 10px;">ID</th>
                    <th style="padding: 10px;">Sản phẩm</th>
                    <th style="padding: 10px;">Danh mục</th>
                    <th style="padding: 10px;">Giá bán</th>
                    <th style="padding: 10px;">Loại</th>
                    <th style="padding: 10px;">Trạng thái</th>
                    <th style="padding: 10px;">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                    <tr style="border-bottom: 1px solid var(--border);">
                        <td style="padding: 10px;"><?= $p['product_id'] ?></td>
                        <td style="padding: 10px;">
                            <strong><?= htmlspecialchars($p['product_name']) ?></strong><br>
                            <small style="color: var(--text-muted);"><?= htmlspecialchars($p['generic_name']) ?></small>
                        </td>
                        <td style="padding: 10px;"><?= htmlspecialchars($p['category_name']) ?></td>
                        <td style="padding: 10px;"><?= number_format($p['price']) ?>đ</td>
                        <td style="padding: 10px;">
                            <?php if ($p['is_prescription_required']): ?>
                                <span style="color: var(--red); font-size: 0.8rem; font-weight: bold;">[Rx] Kê đơn</span>
                            <?php else: ?>
                                <span style="color: var(--green); font-size: 0.8rem;">OTC</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 10px;">
                            <?php if ($p['is_active']): ?>
                                <span style="color: var(--green); font-weight: bold;">● Đang bán</span>
                            <?php else: ?>
                                <span style="color: var(--red); font-weight: bold;">○ Đã ẩn</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 10px;">
                            <a href="<?= BASE_URL ?>admin/product/edit?id=<?= $p['product_id'] ?>" style="color: var(--blue);">Sửa</a> |
                            <a href="<?= BASE_URL ?>admin/product/toggleStatus?id=<?= $p['product_id'] ?>" style="color: <?= $p['is_active'] ? 'var(--orange)' : 'var(--green)' ?>;" onclick="return confirm('Bạn có chắc chắn muốn <?= $p['is_active'] ? 'ẨN' : 'HIỂN THỊ LẠI' ?> sản phẩm này?')">
                                <?= $p['is_active'] ? 'Ẩn đi' : 'Hiện lại' ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../../layout/footer.php'; ?>
