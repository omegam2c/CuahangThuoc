<?php

namespace App\Services\Emails;

class PaymentSuccessEmail extends BaseEmail {
    private $order;

    public function __construct($order) {
        $this->order = $order;
    }

    protected function getSubject(): string {
        return "Xác nhận thanh toán thành công đơn hàng #" . $this->order['order_id'] . " - Nhà thuốc 1985";
    }

    protected function getType(): string {
        return 'payment_success';
    }

    protected function getBody(): string {
        return "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; color: #333; background: #f9f9f9; padding: 20px;'>
            <div style='background: #10b981; color: white; padding: 30px; text-align: center; border-radius: 12px 12px 0 0;'>
                <h2 style='margin: 0;'>Thanh toán thành công!</h2>
                <p style='margin: 10px 0 0; opacity: 0.9;'>Đơn hàng #{$this->order['order_id']} đã sẵn sàng</p>
            </div>
            <div style='background: white; padding: 30px; border-radius: 0 0 12px 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);'>
                <p>Xin chào <strong>" . htmlspecialchars($this->order['shipping_name']) . "</strong>,</p>
                <p>Chúng tôi đã nhận được thanh toán cho đơn hàng <strong>#{$this->order['order_id']}</strong>.</p>
                <div style='background: #f0fdf4; border: 1px solid #bbf7d0; padding: 15px; border-radius: 8px; margin: 20px 0;'>
                    <p style='margin: 0; font-size: 0.9rem;'>Số tiền: <strong>" . number_format($this->order['total_amount']) . "đ</strong></p>
                    <p style='margin: 5px 0 0; font-size: 0.9rem;'>Phương thức: <strong>" . strtoupper($this->order['payment_method']) . "</strong></p>
                </div>
                
                <p style='margin-top: 20px;'>Đơn hàng của bạn đang được chuẩn bị để giao đi sớm nhất.</p>
            </div>
            
            <div style='text-align: center; font-size: 0.8rem; color: #94a3b8; margin-top: 30px;'>
                <p style='margin: 5px 0;'><strong>Nhà thuốc 1985 - Tận tâm vì sức khỏe</strong></p>
                <p style='margin: 5px 0;'>Địa chỉ: 25 Tựu Liệt, Thanh Trì, Hà Nội</p>
                <p style='margin: 5px 0;'>&copy; " . date('Y') . " Nhà thuốc 1985. Mọi quyền được bảo lưu.</p>
            </div>
        </div>";
    }
}
