<?php

require_once __DIR__ . '/../BaseController.php';
require_once __DIR__ . '/../../Models/BaseModel.php';
require_once __DIR__ . '/../../Services/InventoryService.php';

class AdminDashboardController extends BaseController {
    private $inventoryService;
    
    public function __construct() {
        parent::__construct();
        if (!isset($_SESSION['user_id'])) {
            $this->redirect(BASE_URL . 'auth/login');
        }
        $this->checkPermission('view_business_stats'); // Ref point 🔴 - Aligned with DB
        $this->inventoryService = new InventoryService();
    }
    
    public function index() {
        $db = (new BaseModel())->db;
        
        // 1. Thống kê tổng quan
        $totalProducts = $db->query("SELECT COUNT(*) FROM products")->fetchColumn();
        $totalOrders = $db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
        $totalUsers = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $expiredCount = $db->query("SELECT COUNT(*) FROM batches WHERE status = 'expired' AND quantity_remaining > 0")->fetchColumn();
        
        $todayOrders = $db->query("SELECT COUNT(*) FROM orders WHERE DATE(order_date) = CURDATE()")->fetchColumn();
        $monthlyRevenue = $db->query("SELECT SUM(total_amount) FROM orders WHERE status='completed' AND MONTH(order_date) = MONTH(CURDATE()) AND YEAR(order_date) = YEAR(CURDATE())")->fetchColumn() ?: 0;
        
        // 2. Doanh thu tháng hiện tại
        $year = date('Y');
        $month = date('m');
        $revSql = "SELECT DATE(order_date) as date, SUM(total_amount) as daily_revenue 
                   FROM orders 
                   WHERE YEAR(order_date) = :year AND MONTH(order_date) = :month AND status = 'completed' 
                   GROUP BY DATE(order_date) 
                   ORDER BY DATE(order_date)";
        $stmt = $db->prepare($revSql);
        $stmt->execute(['year' => $year, 'month' => $month]);
        $revenueData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 3. Sản phẩm bán chạy (Từ View v_bestsellers)
        $bestSellers = $db->query("SELECT * FROM v_bestsellers LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

        // 4. Tối ưu N+1 Query: Doanh thu 6 tháng gần nhất (Ref point 10 - N+1 Problem)
        $revenueChartData = [];
        $sixMonthsAgo = date('Y-m-01', strtotime('-5 months'));
        
        $revSql = "SELECT DATE_FORMAT(order_date, '%m/%Y') as month_year, 
                          SUM(total_amount) as rev, 
                          COUNT(order_id) as cnt 
                   FROM orders 
                   WHERE status='completed' AND order_date >= :since
                   GROUP BY DATE_FORMAT(order_date, '%m/%Y')
                   ORDER BY MIN(order_date) ASC";
        $revStmt = $db->prepare($revSql);
        $revStmt->execute(['since' => $sixMonthsAgo]);
        $revResults = $revStmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($revResults as $row) {
            $revenueChartData[] = [
                'month' => 'T' . explode('/', $row['month_year'])[0],
                'revenue' => (float)$row['rev'] / 1000000,
                'completed' => (int)$row['cnt']
            ];
        }

        // 5. Tối ưu N+1 Query: Người dùng mới (Ref point 10)
        $fourWeeksAgo = date('Y-m-d', strtotime('-4 weeks'));
        $userSql = "SELECT DATE_FORMAT(created_at, '%v') as week_num, 
                           COUNT(*) as count,
                           MIN(DATE(created_at)) as week_start
                    FROM users 
                    WHERE created_at >= :since
                    GROUP BY week_num
                    ORDER BY week_start ASC";
        $userStmt = $db->prepare($userSql);
        $userStmt->execute(['since' => $fourWeeksAgo]);
        $userResults = $userStmt->fetchAll(PDO::FETCH_ASSOC);

        $weeklyNewUsers = [];
        foreach ($userResults as $row) {
            $weeklyNewUsers[] = [
                'week' => date('d/m', strtotime($row['week_start'])),
                'count' => (int)$row['count']
            ];
        }

        // 6. Trạng thái đơn hàng tháng này (Optimized - Ref point 10)
        $month = date('m');
        $year = date('Y');
        $statusStmt = $db->query("SELECT status, COUNT(*) as cnt FROM orders WHERE MONTH(order_date) = $month AND YEAR(order_date) = $year GROUP BY status");
        $statusCounts = $statusStmt->fetchAll(PDO::FETCH_KEY_PAIR);
        $orderStatusData = [
            'completed' => $statusCounts['completed'] ?? 0,
            'processing' => ($statusCounts['confirmed'] ?? 0) + ($statusCounts['preparing'] ?? 0) + ($statusCounts['shipping'] ?? 0),
            'pending' => $statusCounts['pending'] ?? 0,
            'cancelled' => $statusCounts['cancelled'] ?? 0,
        ];

        // Top danh mục
        $topCategories = $db->query("
            SELECT c.category_name as name, SUM(oi.quantity * oi.unit_price) as sales
            FROM order_details oi
            JOIN products p ON oi.product_id = p.product_id
            JOIN categories c ON p.category_id = c.category_id
            JOIN orders o ON oi.order_id = o.order_id
            WHERE o.status = 'completed'
            GROUP BY c.category_id
            ORDER BY sales DESC LIMIT 5
        ")->fetchAll(PDO::FETCH_ASSOC);

        $recentOrders = $db->query("SELECT * FROM orders ORDER BY order_id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

        // Lấy cảnh báo tồn kho (Ref point 🚩)
        $inventoryAlerts = $this->inventoryService->getAllStockAlerts();

        $this->loadView('admin/dashboard', [
            'stats' => [
                'products' => $totalProducts,
                'orders' => $totalOrders,
                'users' => $totalUsers,
                'expired' => $expiredCount,
                'todayOrders' => $todayOrders,
                'monthlyRevenue' => $monthlyRevenue,
                'low_stock_count' => count($inventoryAlerts['low_stock']),
                'near_expiry_count' => count($inventoryAlerts['expiry'])
            ],
            'inventoryAlerts' => $inventoryAlerts,
            'revenueData' => $revenueData,
            'bestSellers' => $bestSellers,
            'revenueChartData' => $revenueChartData,
            'orderStatusData' => $orderStatusData,
            'weeklyNewUsers' => $weeklyNewUsers,
            'topCategories' => $topCategories,
            'recentOrders' => $recentOrders,
            'pageTitle' => 'Dashboard Quản trị'
        ]);
    }
}
