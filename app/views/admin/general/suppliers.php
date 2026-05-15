<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="container section animate-fade-up">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h1 class="section-title">Quản lý Nhà cung cấp</h1>
        <button class="btn btn-premium">+ Thêm nhà cung cấp</button>
    </div>

    <div class="glass-card" style="padding: 20px;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="text-align: left; border-bottom: 2px solid var(--border);">
                    <th style="padding: 15px;">Tên nhà cung cấp</th>
                    <th style="padding: 15px;">Liên hệ</th>
                    <th style="padding: 15px;">Số điện thoại</th>
                    <th style="padding: 15px;">Địa chỉ</th>
                    <th style="padding: 15px;">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($suppliers as $s): ?>
                    <tr style="border-bottom: 1px solid var(--border);">
                        <td style="padding: 15px;"><strong><?= htmlspecialchars($s['supplier_name']) ?></strong></td>
                        <td style="padding: 15px;"><?= htmlspecialchars($s['contact_name'] ?? 'N/A') ?></td>
                        <td style="padding: 15px;"><?= htmlspecialchars($s['phone'] ?? 'N/A') ?></td>
                        <td style="padding: 15px; font-size: 0.9rem;"><?= htmlspecialchars($s['address'] ?? 'N/A') ?></td>
                        <td style="padding: 15px;">
                            <button class="btn" style="color: var(--blue);">Sửa</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../../layout/footer.php'; ?>
