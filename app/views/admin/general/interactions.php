<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="container section animate-fade-up">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h1 class="section-title">Quản lý Tương tác thuốc</h1>
        <button class="btn btn-premium">+ Thêm tương tác mới</button>
    </div>

    <div class="glass-card" style="padding: 20px;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="text-align: left; border-bottom: 2px solid var(--border);">
                    <th style="padding: 15px;">Thuốc A</th>
                    <th style="padding: 15px;">Thuốc B</th>
                    <th style="padding: 15px;">Mức độ</th>
                    <th style="padding: 15px;">Cảnh báo</th>
                    <th style="padding: 15px;">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($interactions as $i): ?>
                    <tr style="border-bottom: 1px solid var(--border);">
                        <td style="padding: 15px;"><strong><?= htmlspecialchars($i['drug_a']) ?></strong></td>
                        <td style="padding: 15px;"><strong><?= htmlspecialchars($i['drug_b']) ?></strong></td>
                        <td style="padding: 15px;">
                            <span style="padding: 4px 10px; border-radius: 20px; font-size: 0.8rem; background: <?= $i['severity'] === 'critical' ? '#ffebee; color: #c62828;' : '#fff3e0; color: #ef6c00;' ?>">
                                <?= strtoupper($i['severity']) ?>
                            </span>
                        </td>
                        <td style="padding: 15px; font-size: 0.9rem; color: var(--text-muted);"><?= htmlspecialchars($i['description'] ?? 'N/A') ?></td>
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
