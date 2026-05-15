<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="container section animate-fade-up">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <div>
            <h1 style="font-size: 2rem; margin-bottom: 5px;">📜 Nhật ký hệ thống</h1>
            <p style="color: #666;">Giám sát các hoạt động nhạy cảm và bảo mật của cửa hàng</p>
        </div>
        <div class="glass-card" style="padding: 10px 20px; display: flex; align-items: center; gap: 15px; background: rgba(var(--primary-rgb), 0.05);">
            <div style="text-align: right;">
                <div style="font-size: 0.85rem; color: #666;">Tổng số bản ghi</div>
                <div style="font-size: 1.2rem; font-weight: bold; color: var(--primary);"><?= count($logs) ?></div>
            </div>
            <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                <i class="fas fa-history"></i>
            </div>
        </div>
    </div>

    <div class="glass-card" style="padding: 0; overflow: hidden;">
        <table class="table" style="width: 100%; border-collapse: collapse; margin-bottom: 0;">
            <thead>
                <tr style="background: #f8f9fa; border-bottom: 2px solid var(--border);">
                    <th style="padding: 15px 20px; width: 100px;">Thời gian</th>
                    <th style="padding: 15px 20px; width: 180px;">Người thực hiện</th>
                    <th style="padding: 15px 20px; width: 120px;">Hành động</th>
                    <th style="padding: 15px 20px; width: 120px;">Đối tượng</th>
                    <th style="padding: 15px 20px;">Chi tiết hoạt động</th>
                    <th style="padding: 15px 20px; width: 150px;">IP / Thiết bị</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 40px; color: #999;">Chưa có dữ liệu nhật ký.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($logs as $log): ?>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 15px 20px; width: 100px; white-space: nowrap;">
                                <div style="font-weight: 600; color: #334155;"><?= date('H:i:s', strtotime($log['created_at'])) ?></div>
                                <div style="font-size: 0.8rem; color: var(--text-muted);"><?= date('d/m/Y', strtotime($log['created_at'])) ?></div>
                            </td>
                            <td style="padding: 15px 20px; width: 180px;">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(59, 130, 246, 0.1); display: flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: bold; color: var(--blue);">
                                        <?= strtoupper(substr($log['username'] ?? '?', 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div style="font-weight: 600; color: #1e293b;"><?= htmlspecialchars($log['full_name'] ?? 'Hệ thống') ?></div>
                                        <div style="font-size: 0.75rem; color: var(--text-muted);">@<?= htmlspecialchars($log['username'] ?? 'system') ?></div>
                                    </div>
                                </div>
                            </td>
                            <td style="padding: 15px 20px; width: 120px;">
                                <?php
                                    $actionStyle = 'padding: 6px 12px; border-radius: 6px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.02em;';
                                    
                                    if (strpos($log['action'], 'login') !== false) {
                                        $actionStyle .= 'background: #e0f2fe; color: #0369a1;';
                                    } elseif (strpos($log['action'], 'create') !== false || strpos($log['action'], 'import') !== false) {
                                        $actionStyle .= 'background: #f0fdf4; color: #15803d;';
                                    } elseif (strpos($log['action'], 'delete') !== false || strpos($log['action'], 'cancel') !== false) {
                                        $actionStyle .= 'background: #fef2f2; color: #b91c1c;';
                                    } elseif (strpos($log['action'], 'update') !== false) {
                                        $actionStyle .= 'background: #fff7ed; color: #9a3412;';
                                    } else {
                                        $actionStyle .= 'background: #f8fafc; color: #475569; border: 1px solid #e2e8f0;';
                                    }
                                ?>
                                <span style="<?= $actionStyle ?>"><?= htmlspecialchars($log['action']) ?></span>
                            </td>
                            <td style="padding: 15px 20px; width: 120px;">
                                <div style="font-weight: 600; color: #475569; font-size: 0.9rem;"><?= strtoupper(htmlspecialchars($log['table_name'] ?? '-')) ?></div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">ID: #<?= $log['record_id'] ?? 'N/A' ?></div>
                            </td>
                            <td style="padding: 15px 20px;">
                                <div style="font-size: 0.9rem; color: #334155; line-height: 1.5;"><?= htmlspecialchars($log['description'] ?? 'Không có mô tả') ?></div>
                            </td>
                            <td style="padding: 15px 20px; width: 150px;">
                                <div style="font-size: 0.85rem; color: #475569; display: flex; align-items: center; gap: 6px; font-family: monospace;">
                                    <span style="color: var(--blue);">●</span> <?= htmlspecialchars($log['ip_address'] ?? '0.0.0.0') ?>
                                </div>
                                <div style="font-size: 0.7rem; color: var(--text-muted); margin-top: 4px; overflow: hidden; text-overflow: ellipsis; max-width: 140px;" title="<?= htmlspecialchars($log['user_agent'] ?? '') ?>">
                                    <?= htmlspecialchars($log['user_agent'] ?? 'Unknown Agent') ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../../layout/footer.php'; ?>
