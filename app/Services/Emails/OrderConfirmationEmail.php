<?php

namespace App\Services\Emails;

class OrderConfirmationEmail extends BaseEmail {
    private $order;
    private $details;

    public function __construct($order, $details) {
        $this->order = $order;
        $this->details = $details;
    }

    protected function getSubject(): string {
        return "Xác nhận đơn hàng #" . $this->order['order_id'] . " - Nhà thuốc 1985";
    }

    protected function getType(): string {
        return 'order_confirmation';
    }

    protected function getBody(): string {
        $itemsHtml = '';
        $subtotalItems = 0;
        foreach ($this->details as $item) {
            $itemTotal = $item['quantity'] * $item['unit_price'];
            $subtotalItems += $itemTotal;
            $itemsHtml .= "<tr>
                <td style='padding: 10px; border-bottom: 1px solid #eee;'>" . htmlspecialchars($item['product_name']) . "</td>
                <td style='padding: 10px; border-bottom: 1px solid #eee; text-align: center;'>{$item['quantity']}</td>
                <td style='padding: 10px; border-bottom: 1px solid #eee; text-align: right;'>" . number_format($item['unit_price']) . "đ</td>
                <td style='padding: 10px; border-bottom: 1px solid #eee; text-align: right; font-weight: bold;'>" . number_format($itemTotal) . "đ</td>
            </tr>";
        }

        $shippingFee = ($this->order['total_amount'] ?? 0) - ($this->order['subtotal'] ?? $subtotalItems);
        if ($shippingFee < 0) $shippingFee = 0;

        return "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; color: #333; background: #f9f9f9; padding: 20px;'>
            <div style='background: #1D9E75; color: white; padding: 30px; text-align: center; border-radius: 12px 12px 0 0;'>
                <h1 style='margin: 0; font-size: 24px;'>Cảm ơn bạn đã đặt hàng!</h1>
                <p style='margin: 10px 0 0; opacity: 0.9;'>Đơn hàng #{$this->order['order_id']} đã được tiếp nhận</p>
            </div>
            
            <div style='background: white; padding: 30px; border-radius: 0 0 12px 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);'>
                <p>Xin chào <strong>" . htmlspecialchars($this->order['shipping_name']) . "</strong>,</p>
                <p>Chúng tôi đã nhận được đơn hàng của bạn và đang chuẩn bị để giao đi sớm nhất.</p>
                
                <h3 style='border-bottom: 2px solid #1D9E75; padding-bottom: 8px; color: #1D9E75; margin-top: 30px;'>Chi tiết đơn hàng</h3>
                <table style='width: 100%; border-collapse: collapse; margin-top: 10px;'>
                    <thead>
                        <tr style='background: #f8f9fa;'>
                            <th style='text-align: left; padding: 12px; font-size: 0.9rem;'>Sản phẩm</th>
                            <th style='text-align: center; padding: 12px; font-size: 0.9rem;'>SL</th>
                            <th style='text-align: right; padding: 12px; font-size: 0.9rem;'>Giá</th>
                            <th style='text-align: right; padding: 12px; font-size: 0.9rem;'>Tổng</th>
                        </tr>
                    </thead>
                    <tbody>
                        {$itemsHtml}
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan='3' style='padding: 10px; text-align: right; border-top: 1px solid #eee;'>Tạm tính:</td>
                            <td style='padding: 10px; text-align: right; border-top: 1px solid #eee;'>" . number_format($this->order['subtotal'] ?? $subtotalItems) . "đ</td>
                        </tr>
                        <tr>
                            <td colspan='3' style='padding: 10px; text-align: right;'>Phí vận chuyển:</td>
                            <td style='padding: 10px; text-align: right;'>" . number_format($shippingFee) . "đ</td>
                        </tr>
                        <tr>
                            <td colspan='3' style='padding: 15px; text-align: right; font-weight: bold; font-size: 1.1rem;'>Tổng thanh toán:</td>
                            <td style='padding: 15px; text-align: right; color: #1D9E75; font-weight: bold; font-size: 1.3rem;'>" . number_format($this->order['total_amount']) . "đ</td>
                        </tr>
                    </tfoot>
                </table>

                <div style='margin-top: 30px; display: grid; grid-template-columns: 1fr; gap: 20px;'>
                    <div style='background: #f8f9fa; padding: 20px; border-radius: 8px; border-left: 4px solid #1D9E75;'>
                        <h4 style='margin: 0 0 10px 0; color: #1D9E75;'>📍 Thông tin nhận hàng</h4>
                        <p style='margin: 0; font-size: 0.9rem;'><strong>Người nhận:</strong> " . htmlspecialchars($this->order['shipping_name']) . "</p>
                        <p style='margin: 5px 0; font-size: 0.9rem;'><strong>SĐT:</strong> " . htmlspecialchars($this->order['shipping_phone']) . "</p>
                        <p style='margin: 5px 0; font-size: 0.9rem;'><strong>Địa chỉ:</strong> " . htmlspecialchars($this->order['shipping_address']) . "</p>
                    </div>
                </div>

                <div style='text-align: center; margin-top: 40px;'>
                    <a href='" . SITE_URL . "order/success?id={$this->order['order_id']}' style='background: #1D9E75; color: white; padding: 15px 35px; text-decoration: none; border-radius: 30px; font-weight: bold; font-size: 1rem; box-shadow: 0 4px 10px rgba(29,158,117,0.3);'>Theo dõi đơn hàng</a>
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
