<?php

namespace App\Services\Emails;

class OrderCompletedEmail extends BaseEmail {
    private $order;

    public function __construct($order) {
        $this->order = $order;
    }

    protected function getSubject(): string {
        return "Đơn hàng #" . $this->order['order_id'] . " đã hoàn thành - Nhà thuốc 1985";
    }

    protected function getType(): string {
        return 'order_completed';
    }

    protected function getBody(): string {
        return "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; color: #333; background: #f9f9f9; padding: 20px;'>
            <div style='background: #1565c0; color: white; padding: 30px; text-align: center; border-radius: 12px 12px 0 0;'>
                <h2 style='margin: 0;'>Giao hàng thành công!</h2>
                <p style='margin: 10px 0 0; opacity: 0.9;'>Đơn hàng #{$this->order['order_id']} đã hoàn tất</p>
            </div>
            <div style='background: white; padding: 30px; border-radius: 0 0 12px 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);'>
                <p>Xin chào <strong>" . htmlspecialchars($this->order['shipping_name']) . "</strong>,</p>
                <p>Đơn hàng <strong>#{$this->order['order_id']}</strong> của bạn đã được giao thành công.</p>
                <p>Hy vọng bạn hài lòng với các sản phẩm từ Nhà thuốc 1985. Nếu có bất kỳ câu hỏi nào, đừng ngần ngại liên hệ với chúng tôi.</p>
                
                <div style='text-align: center; margin-top: 30px;'>
                    <a href='" . SITE_URL . "products' style='background: #1565c0; color: white; padding: 12px 30px; text-decoration: none; border-radius: 30px; font-weight: bold;'>Tiếp tục mua sắm</a>
                </div>

                <div style='margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;'>
                    <p style='font-size: 0.9rem; color: #666;'>Bạn có thể đánh giá sản phẩm để nhận ưu đãi cho lần mua sau.</p>
                </div>
            </div>
            
            <div style='text-align: center; font-size: 0.8rem; color: #94a3b8; margin-top: 30px;'>
                <p style='margin: 5px 0;'><strong>Nhà thuốc 1985 - Tận tâm vì sức khỏe</strong></p>
                <p style='margin: 5px 0;'>Địa chỉ: 25 Tựu Liệt, Thanh Trì, Hà Nội</p>
                <p style='margin: 5px 0;'>&copy; " . date('Y') . " Nhà thuốc 1985. Mọi quyền được bảo lưu.</p>
            </div>
        </div>";
    }
}
