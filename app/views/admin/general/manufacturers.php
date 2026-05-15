<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="container section animate-fade-up">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h1 class="section-title">Quản lý Nhà sản xuất</h1>
        <button class="btn btn-premium">+ Thêm nhà sản xuất</button>
    </div>

    <div class="glass-card" style="padding: 20px;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="text-align: left; border-bottom: 2px solid var(--border);">
                    <th style="padding: 15px;">ID</th>
                    <th style="padding: 15px;">Tên nhà sản xuất</th>
                    <th style="padding: 15px;">Quốc gia</th>
                    <th style="padding: 15px;">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($manufacturers as $m): ?>
                    <tr style="border-bottom: 1px solid var(--border);">
                        <td style="padding: 15px;"><?= $m['manufacturer_id'] ?></td>
                        <td style="padding: 15px;"><strong><?= htmlspecialchars($m['manufacturer_name']) ?></strong></td>
                        <td style="padding: 15px;"><?= htmlspecialchars($m['country'] ?? 'N/A') ?></td>
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
