<?php include __DIR__ . '/../layout/header.php'; ?>

<?php
// Chuẩn bị dữ liệu biểu đồ (JSON cho Chart.js)
$revenueLabels   = json_encode(array_column($revenueChartData, 'month'));
$revenueValues   = json_encode(array_column($revenueChartData, 'revenue'));      
$completedValues = json_encode(array_column($revenueChartData, 'completed'));    

$statusValues = json_encode([
    $orderStatusData['completed']   ?? 0,
    $orderStatusData['processing']  ?? 0,
    $orderStatusData['pending']     ?? 0,
    $orderStatusData['cancelled']   ?? 0,
]);

$weekLabels    = json_encode(array_column($weeklyNewUsers, 'week'));
$weeklyValues  = json_encode(array_column($weeklyNewUsers, 'count'));

$catLabels = json_encode(array_column($topCategories, 'name'));
$catValues = json_encode(array_column($topCategories, 'sales'));

function statusBadge(string $status): string {
    $map = [
        'pending'    => ['cls' => 'badge-pending',    'label' => 'Chờ xác nhận'],
        'confirmed'  => ['cls' => 'badge-processing', 'label' => 'Đã xác nhận'],
        'preparing'  => ['cls' => 'badge-processing', 'label' => 'Đang chuẩn bị'],
        'shipping'   => ['cls' => 'badge-processing', 'label' => 'Đang giao hàng'],
        'completed'  => ['cls' => 'badge-completed',  'label' => 'Hoàn thành'],
        'cancelled'  => ['cls' => 'badge-cancelled',  'label' => 'Đã huỷ'],
    ];
    $s = $map[$status] ?? ['cls' => 'badge-pending', 'label' => ucfirst($status)];
    return '<span class="badge ' . $s['cls'] . '">' . $s['label'] . '</span>';
}
?>

<style>
  /* ---- Chart cards ---- */
  .charts-row  { display: grid; grid-template-columns: 1.6fr 1fr; gap: 20px; margin-top: 30px; margin-bottom: 20px; }
  .charts-row2 { display: grid; grid-template-columns: 1fr 1fr;   gap: 20px; margin-bottom: 20px; }
  .chart-card {
    background: var(--glass-bg, #fff);
    border: 1px solid var(--border, rgba(0,0,0,0.1));
    border-radius: 12px;
    padding: 18px 20px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
  }
  .chart-card h2  { font-size: 1.1rem; font-weight: 500; color: var(--text-main, #1a1a1a); margin-bottom: 4px; }
  .chart-sub      { font-size: 0.85rem; color: var(--text-muted, #888); margin-bottom: 14px; }
  .chart-legend   { display: flex; flex-wrap: wrap; gap: 14px; margin-bottom: 10px; font-size: 0.85rem; color: var(--text-muted, #888); }
  .chart-legend span { display: flex; align-items: center; gap: 5px; }
  .legend-dot { width: 10px; height: 10px; border-radius: 2px; display: inline-block; }

  /* ---- Orders table ---- */
  .orders-card {
    background: var(--glass-bg, #fff);
    border: 1px solid var(--border, rgba(0,0,0,0.1));
    border-radius: 12px;
    padding: 18px 20px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    margin-bottom: 30px;
  }
  .orders-card h2 { font-size: 1.1rem; font-weight: 500; margin-bottom: 16px; }
  .orders-table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
  .orders-table th {
    text-align: left; font-weight: 500; color: var(--text-muted, #888);
    padding: 0 12px 10px 0; font-size: 0.8rem; letter-spacing: 0.05em;
    border-bottom: 1px solid var(--border, rgba(0,0,0,0.08));
  }
  .orders-table td {
    padding: 12px 12px 12px 0;
    border-bottom: 1px solid var(--border, rgba(0,0,0,0.06));
    vertical-align: middle;
  }
  .orders-table tr:last-child td { border-bottom: none; }
  .order-id { font-weight: 500; color: var(--green, #1D9E75); }

  /* ---- Badges ---- */
  .badge            { display: inline-block; font-size: 0.75rem; padding: 3px 10px; border-radius: 100px; font-weight: 500; }
  .badge-completed  { background: #EAF3DE; color: #3B6D11; }
  .badge-pending    { background: #FAEEDA; color: #854F0B; }
  .badge-cancelled  { background: #FCEBEB; color: #A32D2D; }
  .badge-processing { background: #E6F1FB; color: #185FA5; }
</style>

<div class="container section animate-fade-up">
    <h1 class="section-title">Bảng điều khiển Quản trị</h1>
    
    <!-- Thống kê nhanh -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 30px;">
        <div class="glass-card" style="padding: 20px; text-align: center; border-bottom: 4px solid var(--blue);">
            <div style="font-size: 2rem; color: var(--blue);">📦</div>
            <h3 style="margin: 10px 0; font-size: 1rem; color: var(--text-muted);">Tổng sản phẩm</h3>
            <p style="font-size: 1.8rem; font-weight: bold; color: var(--text-main);"><?= number_format($stats['products']) ?></p>
        </div>
        
        <div class="glass-card" style="padding: 20px; text-align: center; border-bottom: 4px solid #BA7517;">
            <div style="font-size: 2rem; color: #BA7517;">🛒</div>
            <h3 style="margin: 10px 0; font-size: 1rem; color: var(--text-muted);">Đơn hàng hôm nay</h3>
            <p style="font-size: 1.8rem; font-weight: bold; color: var(--text-main);"><?= number_format($stats['todayOrders']) ?></p>
        </div>
        
        <div class="glass-card" style="padding: 20px; text-align: center; border-bottom: 4px solid var(--green);">
            <div style="font-size: 2rem; color: var(--green);">💰</div>
            <h3 style="margin: 10px 0; font-size: 1rem; color: var(--text-muted);">Doanh thu tháng</h3>
            <p style="font-size: 1.8rem; font-weight: bold; color: var(--text-main);"><?= number_format($stats['monthlyRevenue'] / 1000000, 1) ?>M₫</p>
        </div>
        
        <div class="glass-card" style="padding: 20px; text-align: center; border-bottom: 4px solid var(--red);">
            <div style="font-size: 2rem; color: var(--red);">⚠️</div>
            <h3 style="margin: 10px 0; font-size: 1rem; color: var(--text-muted);">Lô sắp/đã hết hạn</h3>
            <p style="font-size: 1.8rem; font-weight: bold; color: var(--red);"><?= number_format($stats['expired']) ?></p>
        </div>
    </div>

    <!-- Biểu đồ -->
    <div class="charts-row">
        <div class="chart-card">
            <h2>Doanh thu 6 tháng gần nhất</h2>
            <p class="chart-sub">Đơn vị: triệu đồng</p>
            <div class="chart-legend">
                <span><span class="legend-dot" style="background:#7F77DD;"></span>Doanh thu</span>
                <span><span class="legend-dot" style="background:var(--green);"></span>Đơn hoàn thành</span>
            </div>
            <div style="position:relative;width:100%;height:250px;">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <div class="chart-card">
            <h2>Trạng thái đơn hàng</h2>
            <p class="chart-sub">Tháng này</p>
            <div style="position:relative;width:100%;height:250px;">
                <canvas id="statusChart"></canvas>
            </div>
            <div class="chart-legend" style="margin-top:15px;justify-content:center;">
                <span><span class="legend-dot" style="background:#1D9E75;"></span>Hoàn thành</span>
                <span><span class="legend-dot" style="background:#378ADD;"></span>Xử lý</span>
                <span><span class="legend-dot" style="background:#BA7517;"></span>Chờ</span>
                <span><span class="legend-dot" style="background:#E24B4A;"></span>Huỷ</span>
            </div>
        </div>
    </div>

    <div class="charts-row2">
        <div class="chart-card">
            <h2>Người dùng mới theo tuần</h2>
            <p class="chart-sub">4 tuần gần nhất</p>
            <div class="chart-legend">
                <span><span class="legend-dot" style="background:#378ADD;"></span>Người dùng mới</span>
            </div>
            <div style="position:relative;width:100%;height:200px;">
                <canvas id="userChart"></canvas>
            </div>
        </div>

        <div class="chart-card">
            <h2>Top danh mục sản phẩm</h2>
            <p class="chart-sub">Theo doanh số</p>
            <div class="chart-legend">
                <span><span class="legend-dot" style="background:#7F77DD;"></span>Doanh số</span>
            </div>
            <div style="position:relative;width:100%;height:200px;">
                <canvas id="catChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Bảng đơn hàng gần đây -->
    <div class="orders-card">
        <h2>Đơn hàng gần đây</h2>
        <div style="overflow-x: auto;">
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>MÃ ĐƠN</th>
                        <th>KHÁCH HÀNG</th>
                        <th>SỐ TIỀN</th>
                        <th>TRẠNG THÁI</th>
                        <th>NGÀY ĐẶT</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentOrders as $order): ?>
                    <tr>
                        <td><a href="<?= BASE_URL ?>admin/order/detail?id=<?= $order['order_id'] ?>" class="order-id">#<?= htmlspecialchars($order['order_id']) ?></a></td>
                        <td>Khách ID: <?= htmlspecialchars($order['user_id']) ?></td>
                        <td style="font-weight: 500; color: var(--text-main);"><?= number_format($order['total_amount']) ?>đ</td>
                        <td><?= statusBadge($order['status']) ?></td>
                        <td class="text-muted"><?= htmlspecialchars(date('d/m/Y H:i', strtotime($order['order_date']))) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($recentOrders)): ?>
                    <tr><td colspan="5" style="text-align:center;color:var(--text-muted);padding:20px;">Chưa có đơn hàng nào</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div style="margin-top: 15px; text-align: right;">
            <a href="<?= BASE_URL ?>admin/order" style="color: var(--blue); font-size: 0.9rem; text-decoration: none;">Xem tất cả →</a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const gridColor = 'rgba(0,0,0,0.06)';
  const textColor = '#888780';

  // --- Biểu đồ doanh thu ---
  new Chart(document.getElementById('revenueChart'), {
    type: 'bar',
    data: {
      labels: <?= $revenueLabels ?>,
      datasets: [
        {
          label: 'Doanh thu (triệu)',
          data: <?= $revenueValues ?>,
          backgroundColor: '#7F77DD',
          borderRadius: 4,
          yAxisID: 'y',
        },
        {
          label: 'Đơn hoàn thành',
          data: <?= $completedValues ?>,
          type: 'line',
          borderColor: '#1D9E75',
          backgroundColor: 'rgba(29,158,117,0.08)',
          fill: true,
          tension: 0.4,
          pointRadius: 3,
          pointBackgroundColor: '#1D9E75',
          yAxisID: 'y2',
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: { mode: 'index', intersect: false }
      },
      scales: {
        x:  { grid: { color: gridColor }, ticks: { color: textColor, font: { size: 11 } } },
        y:  { grid: { color: gridColor }, ticks: { color: textColor, font: { size: 11 }, callback: v => v + 'M' }, position: 'left' },
        y2: { display: false, position: 'right' }
      }
    }
  });

  // --- Biểu đồ trạng thái đơn ---
  new Chart(document.getElementById('statusChart'), {
    type: 'doughnut',
    data: {
      labels: ['Hoàn thành', 'Đang xử lý', 'Chờ xác nhận', 'Đã huỷ'],
      datasets: [{
        data: <?= $statusValues ?>,
        backgroundColor: ['#1D9E75', '#378ADD', '#BA7517', '#E24B4A'],
        borderWidth: 2,
        borderColor: '#fff',
        hoverOffset: 6
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: '68%',
      plugins: {
        legend: { display: false },
        tooltip: { callbacks: { label: ctx => ' ' + ctx.label + ': ' + ctx.parsed + ' đơn' } }
      }
    }
  });

  // --- Biểu đồ người dùng mới ---
  new Chart(document.getElementById('userChart'), {
    type: 'line',
    data: {
      labels: <?= $weekLabels ?>,
      datasets: [{
        label: 'Người dùng mới',
        data: <?= $weeklyValues ?>,
        borderColor: '#378ADD',
        backgroundColor: 'rgba(55,138,221,0.10)',
        fill: true,
        tension: 0.4,
        pointRadius: 4,
        pointBackgroundColor: '#378ADD',
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        x: { grid: { color: gridColor }, ticks: { color: textColor, font: { size: 11 } } },
        y: { grid: { color: gridColor }, ticks: { color: textColor, font: { size: 11 }, stepSize: 1 } }
      }
    }
  });

  // --- Biểu đồ top danh mục ---
  new Chart(document.getElementById('catChart'), {
    type: 'bar',
    data: {
      labels: <?= $catLabels ?>,
      datasets: [{
        label: 'Doanh số',
        data: <?= $catValues ?>,
        backgroundColor: ['#7F77DD', '#AFA9EC', '#CECBF6', '#D4537E', '#ED93B1'],
        borderRadius: 4,
      }]
    },
    options: {
      indexAxis: 'y',
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        x: { grid: { color: gridColor }, ticks: { color: textColor, font: { size: 11 } } },
        y: { grid: { display: false }, ticks: { color: textColor, font: { size: 11 } } }
      }
    }
  });
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
