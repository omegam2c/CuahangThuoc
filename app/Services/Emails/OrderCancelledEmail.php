<?php

namespace App\Services\Emails;

class OrderCancelledEmail extends BaseEmail {
    private $order;
    private $reason;

    public function __construct($order, $reason = "Không rõ lý do") {
        $this->order = $order;
        $this->reason = $reason;
    }

    protected function getSubject(): string {
        return "Thông báo hủy đơn hàng #" . $this->order['order_id'] . " - Nhà thuốc 1985";
    }

    protected function getType(): string {
        return 'order_cancelled';
    }

    protected function getBody(): string {
        return "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; color: #333; background: #f9f9f9; padding: 20px;'>
            <div style='background: #e11d48; color: white; padding: 30px; text-align: center; border-radius: 12px 12px 0 0;'>
                <h1 style='margin: 0; font-size: 24px;'>Thông báo hủy đơn hàng</h1>
                <p style='margin: 10px 0 0; opacity: 0.9;'>Đơn hàng #{$this->order['order_id']} đã được hủy</p>
            </div>
            
            <div style='background: white; padding: 30px; border-radius: 0 0 12px 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);'>
                <p>Xin chào <strong>" . htmlspecialchars($this->order['shipping_name']) . "</strong>,</p>
                <p>Chúng tôi rất tiếc phải thông báo rằng đơn hàng của bạn đã bị hủy.</p>
                
                <div style='background: #fff1f2; border-left: 4px solid #e11d48; padding: 15px; margin: 25px 0; border-radius: 8px;'>
                    <h4 style='margin: 0 0 5px 0; color: #e11d48;'>Lý do hủy:</h4>
                    <p style='margin: 0; font-size: 0.95rem; line-height: 1.5;'>" . htmlspecialchars($this->reason) . "</p>
                </div>

                <p>Nếu bạn đã thanh toán qua thẻ/chuyển khoản, chúng tôi sẽ thực hiện hoàn tiền trong vòng 3-5 ngày làm việc. Nếu bạn có bất kỳ thắc mắc nào, vui lòng liên hệ với bộ phận CSKH.</p>
                
                <div style='text-align: center; margin-top: 40px;'>
                    <a href='" . SITE_URL . "' style='background: #334155; color: white; padding: 12px 30px; text-decoration: none; border-radius: 30px; font-weight: bold; font-size: 0.9rem;'>Tiếp tục mua sắm</a>
                </div>
            </div>
            
            <div style='text-align: center; font-size: 0.8rem; color: #94a3b8; margin-top: 30px;'>
                <p style='margin: 5px 0;'><strong>Nhà thuốc 1985 - Tận tâm vì sức khỏe</strong></p>
                <p style='margin: 5px 0;'>Địa chỉ: 25 Tựu Liệt, Thanh Trì, Hà Nội</p>
                <p style='margin: 5px 0;'>Hotline: 0912 345 678</p>
                <p style='margin: 20px 0 0;'>&copy; " . date('Y') . " Nhà thuốc 1985. Mọi quyền được bảo lưu.</p>
            </div>
        </div>";
    }
}
