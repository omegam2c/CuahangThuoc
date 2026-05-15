<?php
require_once __DIR__ . '/BaseModel.php';

class ChatbotModel extends BaseModel
{
    // ── Tổng sản phẩm ──────────────────────────────────────────
    public function getTotalProducts(): int
    {
        return (int) $this->db->query(
            "SELECT COUNT(*) FROM products WHERE is_active = 1"
        )->fetchColumn();
    }

    // ── Tồn kho: tổng số lượng còn lại ───────────────────────
    public function getTotalInventory(): int
    {
        return (int) $this->db->query(
            "SELECT COALESCE(SUM(quantity_remaining), 0) FROM batches WHERE status != 'expired'"
        )->fetchColumn();
    }

    // ── Sản phẩm hết hàng (tồn kho = 0) ─────────────────────
    public function getOutOfStockProducts(): array
    {
        return $this->db->query(
            "SELECT p.product_name, p.unit
             FROM products p
             LEFT JOIN (
                 SELECT product_id, SUM(quantity_remaining) AS total
                 FROM batches WHERE status != 'expired'
                 GROUP BY product_id
             ) b ON p.product_id = b.product_id
             WHERE p.is_active = 1 AND COALESCE(b.total, 0) = 0
             ORDER BY p.product_name
             LIMIT 20"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    // ── Sản phẩm sắp hết hàng (< ngưỡng) ────────────────────
    public function getLowStockProducts(int $threshold = 20): array
    {
        $stmt = $this->db->prepare(
            "SELECT p.product_name, p.unit, COALESCE(SUM(b.quantity_remaining), 0) AS total
             FROM products p
             LEFT JOIN batches b ON p.product_id = b.product_id AND b.status != 'expired'
             WHERE p.is_active = 1
             GROUP BY p.product_id, p.product_name, p.unit
             HAVING total > 0 AND total < ?
             ORDER BY total ASC
             LIMIT 20"
        );
        $stmt->execute([$threshold]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ── Doanh thu hôm nay ─────────────────────────────────────
    public function getTodayRevenue(): float
    {
        return (float) $this->db->query(
            "SELECT COALESCE(SUM(total_amount), 0) FROM orders
             WHERE status = 'completed' AND DATE(order_date) = CURDATE()"
        )->fetchColumn();
    }

    // ── Doanh thu tháng hiện tại ──────────────────────────────
    public function getThisMonthRevenue(): float
    {
        return (float) $this->db->query(
            "SELECT COALESCE(SUM(total_amount), 0) FROM orders
             WHERE status = 'completed'
               AND MONTH(order_date) = MONTH(NOW())
               AND YEAR(order_date)  = YEAR(NOW())"
        )->fetchColumn();
    }

    // ── Doanh thu tháng trước ─────────────────────────────────
    public function getLastMonthRevenue(): float
    {
        return (float) $this->db->query(
            "SELECT COALESCE(SUM(total_amount), 0) FROM orders
             WHERE status = 'completed'
               AND MONTH(order_date) = MONTH(DATE_SUB(NOW(), INTERVAL 1 MONTH))
               AND YEAR(order_date)  = YEAR(DATE_SUB(NOW(), INTERVAL 1 MONTH))"
        )->fetchColumn();
    }

    // ── Doanh thu theo tháng cụ thể (MM/YYYY) ────────────────
    public function getRevenueByMonth(int $month, int $year): float
    {
        $stmt = $this->db->prepare(
            "SELECT COALESCE(SUM(total_amount), 0) FROM orders
             WHERE status = 'completed'
               AND MONTH(order_date) = ? AND YEAR(order_date) = ?"
        );
        $stmt->execute([$month, $year]);
        return (float) $stmt->fetchColumn();
    }

    // ── Tìm kiếm tồn kho 1 sản phẩm ──────────────────────────
    public function searchProductStock(string $keyword): array
    {
        $stmt = $this->db->prepare(
            "SELECT p.product_name, p.unit, p.price,
                    COALESCE(SUM(b.quantity_remaining), 0) AS total_stock
             FROM products p
             LEFT JOIN batches b ON p.product_id = b.product_id AND b.status != 'expired'
             WHERE p.is_active = 1
               AND p.product_name LIKE ?
             GROUP BY p.product_id, p.product_name, p.unit, p.price
             ORDER BY p.product_name
             LIMIT 10"
        );
        $stmt->execute(["%$keyword%"]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ── Thuốc sắp hết hạn (trong N ngày) ─────────────────────
    public function getExpiringProducts(int $days = 90): array
    {
        $stmt = $this->db->prepare(
            "SELECT p.product_name, b.batch_number, b.expiry_date,
                    b.quantity_remaining, p.unit
             FROM batches b
             JOIN products p ON b.product_id = p.product_id
             WHERE b.status != 'expired'
               AND b.quantity_remaining > 0
               AND b.expiry_date <= DATE_ADD(CURDATE(), INTERVAL ? DAY)
               AND b.expiry_date >= CURDATE()
             ORDER BY b.expiry_date ASC
             LIMIT 30"
        );
        $stmt->execute([$days]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ── Thống kê người dùng ──────────────────────────────────
    public function getUserStats(): array
    {
        return $this->db->query(
            "SELECT r.role_name, COUNT(u.user_id) as count
             FROM roles r
             LEFT JOIN users u ON r.role_id = u.role_id
             GROUP BY r.role_id, r.role_name"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    // ── Thống kê đơn hàng theo trạng thái ─────────────────────
    public function getOrderStatusStats(): array
    {
        return $this->db->query(
            "SELECT status, COUNT(*) as count, SUM(total_amount) as total_value
             FROM orders
             GROUP BY status"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    // ── Đơn thuốc chờ duyệt ──────────────────────────────────
    public function getPendingPrescriptionCount(): int
    {
        return (int) $this->db->query(
            "SELECT COUNT(*) FROM orders WHERE has_prescription = 1 AND prescription_verified = 0"
        )->fetchColumn();
    }

    // ── Nhật ký hoạt động gần đây ────────────────────────────
    public function getRecentLogs(int $limit = 5): array
    {
        return $this->db->query(
            "SELECT action, table_name, description, created_at 
             FROM activity_logs 
             ORDER BY created_at DESC 
             LIMIT $limit"
        )->fetchAll(PDO::FETCH_ASSOC);
    }
}
