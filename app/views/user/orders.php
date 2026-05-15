<?php include __DIR__ . '/../layout/header.php'; ?>

<style>
    /* Profile & Orders Shared Styles */
    .profile-container {
        display: grid;
        grid-template-columns: 280px 1fr;
        gap: 30px;
        margin-top: -80px;
        position: relative;
        z-index: 2;
    }

    .profile-header-banner {
        height: 250px;
        background: var(--gradient-primary);
        border-radius: 0 0 40px 40px;
        margin-bottom: 0;
    }

    .profile-sidebar {
        height: fit-content;
        padding: 30px 20px;
    }

    .user-avatar-wrap {
        position: relative;
        width: 120px;
        height: 120px;
        margin: -90px auto 20px;
    }

    .user-avatar-main {
        width: 100%;
        height: 100%;
        background: white;
        color: var(--green);
        border-radius: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3.5rem;
        font-weight: 800;
        box-shadow: var(--shadow-lg);
        border: 4px solid white;
    }

    .sidebar-menu {
        list-style: none;
        padding: 0;
        margin-top: 30px;
    }

    .sidebar-menu li {
        margin-bottom: 12px;
    }

    .sidebar-menu a {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 18px;
        border-radius: 12px;
        color: var(--text-body);
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .sidebar-menu a:hover {
        background: var(--green-light);
        color: var(--green);
        transform: translateX(5px);
    }

    .sidebar-menu a.active {
        background: var(--green);
        color: white;
        box-shadow: 0 4px 12px rgba(0, 144, 74, 0.2);
    }

    .orders-main-content {
        display: flex;
        flex-direction: column;
        gap: 30px;
    }

    .orders-section-card {
        padding: 35px;
        border-radius: 24px;
    }

    .section-title-premium {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 25px;
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-main);
    }

    .section-title-premium i {
        color: var(--green);
        background: var(--green-light);
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
    }

    /* Table Styles */
    .premium-table-wrap {
        overflow-x: auto;
    }

    .premium-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 12px;
        margin-top: -12px;
    }

    .premium-table th {
        padding: 15px 20px;
        text-align: left;
        color: var(--text-muted);
        font-weight: 600;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .premium-table tbody tr {
        background: white;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(0,0,0,0.02);
    }

    .premium-table tbody tr:hover {
        transform: scale(1.01);
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        z-index: 10;
    }

    .premium-table td {
        padding: 20px;
        vertical-align: middle;
    }

    .premium-table td:first-child { border-radius: 15px 0 0 15px; }
    .premium-table td:last-child { border-radius: 0 15px 15px 0; }

    .order-id-badge {
        font-weight: 700;
        color: var(--text-main);
        background: #f0f2f5;
        padding: 6px 12px;
        border-radius: 8px;
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 50px;
        font-size: 0.85rem;
        font-weight: 700;
    }

    .status-pending { background: #fff8e1; color: #ffa000; }
    .status-confirmed { background: #e3f2fd; color: #1976d2; }
    .status-shipping { background: #f3e5f5; color: #7b1fa2; }
    .status-completed { background: #e8f5e9; color: #388e3c; }
    .status-cancelled { background: #ffebee; color: #d32f2f; }

    .btn-view-detail {
        background: var(--bg-page);
        color: var(--text-main);
        padding: 10px 20px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.9rem;
        border: 1px solid var(--border);
        transition: all 0.3s ease;
    }

    .btn-view-detail:hover {
        background: var(--text-main);
        color: white;
        border-color: var(--text-main);
    }

    @media (max-width: 992px) {
        .profile-container {
            grid-template-columns: 1fr;
        }
    }
</style>

<!-- Top Banner Background -->
<div class="profile-header-banner"></div>

<div class="container animate-fade-up">
    <div class="profile-container">
        
        <!-- Sidebar -->
        <aside class="glass-card profile-sidebar">
            <div class="user-avatar-wrap">
                <div class="user-avatar-main">
                    <?= strtoupper(substr($_SESSION['user']['name'], 0, 1)) ?>
                </div>
            </div>
            <div style="text-align: center;">
                <h3 style="font-weight: 700; margin-bottom: 5px;"><?= htmlspecialchars($_SESSION['user']['name']) ?></h3>
                <p style="color: var(--text-muted); font-size: 0.9rem;"><?= htmlspecialchars($_SESSION['email'] ?? 'Khách hàng') ?></p>
            </div>
            
            <ul class="sidebar-menu">
                <li>
                    <a href="<?= BASE_URL ?>account">
                        <span>👤</span> Thông tin cá nhân
                    </a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>account/orders" class="active">
                        <span>📦</span> Lịch sử đơn hàng
                    </a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>cart">
                        <span>🛒</span> Giỏ hàng của tôi
                    </a>
                </li>
                <li style="margin-top: 20px; border-top: 1px solid var(--border); padding-top: 20px;">
                    <a href="<?= BASE_URL ?>auth/logout" style="color: var(--red);">
                        <span>🚪</span> Đăng xuất
                    </a>
                </li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="orders-main-content">
            
            <section class="glass-card orders-section-card">
                <div class="section-title-premium">
                    <i>📦</i>
                    <span>Lịch sử đơn hàng</span>
                </div>
                
                <?php if (empty($orders)): ?>
                    <div style="text-align: center; padding: 60px 20px;">
                        <div style="font-size: 5rem; margin-bottom: 20px; filter: grayscale(1);">📦</div>
                        <h3 style="margin-bottom: 10px;">Bạn chưa có đơn hàng nào</h3>
                        <p style="color: var(--text-muted); margin-bottom: 30px;">Các đơn hàng bạn đã mua sẽ xuất hiện tại đây.</p>
                        <a href="<?= BASE_URL ?>products" class="btn-premium">🛒 Bắt đầu mua sắm</a>
                    </div>
                <?php else: ?>
                    <div class="premium-table-wrap">
                        <table class="premium-table">
                            <thead>
                                <tr>
                                    <th>Mã Đơn Hàng</th>
                                    <th>Ngày Đặt</th>
                                    <th>Tổng Tiền</th>
                                    <th>Trạng Thái</th>
                                    <th style="text-align: right;">Hành Động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $o): ?>
                                    <tr>
                                        <td>
                                            <span class="order-id-badge">#<?= $o['order_id'] ?></span>
                                        </td>
                                        <td>
                                            <div style="font-weight: 500;"><?= date('d/m/Y', strtotime($o['order_date'])) ?></div>
                                            <small style="color: var(--text-muted);"><?= date('H:i', strtotime($o['order_date'])) ?></small>
                                        </td>
                                        <td>
                                            <div style="font-weight: 700; color: var(--green); font-size: 1.1rem;">
                                                <?= number_format($o['total_amount']) ?>đ
                                            </div>
                                        </td>
                                        <td>
                                            <?php 
                                                $statusClasses = [
                                                    'pending' => 'status-pending',
                                                    'confirmed' => 'status-confirmed',
                                                    'shipping' => 'status-shipping',
                                                    'completed' => 'status-completed',
                                                    'cancelled' => 'status-cancelled'
                                                ];
                                                $statusTexts = [
                                                    'pending' => 'Chờ xử lý',
                                                    'confirmed' => 'Đã xác nhận',
                                                    'shipping' => 'Đang giao',
                                                    'completed' => 'Đã giao',
                                                    'cancelled' => 'Đã hủy'
                                                ];
                                                $class = $statusClasses[$o['status']] ?? '';
                                                $text = $statusTexts[$o['status']] ?? ucfirst($o['status']);
                                            ?>
                                            <span class="status-pill <?= $class ?>">
                                                <span style="font-size: 1.2rem;">•</span> <?= $text ?>
                                            </span>
                                        </td>
                                        <td style="text-align: right;">
                                            <a href="<?= BASE_URL ?>account/order_detail?id=<?= $o['order_id'] ?>" class="btn-view-detail">
                                                Chi tiết
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </section>

        </main>
    </div>
</div>

<div style="margin-bottom: 80px;"></div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
