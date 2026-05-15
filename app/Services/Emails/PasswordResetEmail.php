<?php

namespace App\Services\Emails;

class PasswordResetEmail extends BaseEmail {
    private $otp;
    private $userName;

    public function __construct($otp, $userName) {
        $this->otp = $otp;
        $this->userName = $userName;
    }

    protected function getSubject(): string {
        return "Mã xác nhận quên mật khẩu - Nhà thuốc 1985";
    }

    protected function getType(): string {
        return 'password_reset';
    }

    protected function getBody(): string {
        return "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee;'>
            <div style='text-align: center; margin-bottom: 30px;'>
                <h1 style='color: #2c3e50;'>Nhà thuốc 1985</h1>
            </div>
            <p>Chào <strong>{$this->userName}</strong>,</p>
            <p>Chúng tôi nhận được yêu cầu khôi phục mật khẩu cho tài khoản của bạn. Mã xác thực (OTP) của bạn là:</p>
            
            <div style='text-align: center; margin: 30px 0;'>
                <span style='background: #f1f1f1; padding: 15px 30px; font-size: 24px; font-weight: bold; color: #2c3e50; border-radius: 5px; letter-spacing: 5px;'>{$this->otp}</span>
            </div>
            
            <p>Mã này sẽ hết hạn sau 15 phút. Vui lòng không chia sẻ mã này với bất kỳ ai.</p>
            <p>Nếu bạn không yêu cầu đổi mật khẩu, vui lòng bỏ qua email này.</p>
            
            <div style='text-align: center; margin-top: 30px; font-size: 12px; color: #95a5a6;'>
                <p>&copy; 2026 Nhà thuốc 1985. 25 Tựu Liệt, Thanh Trì, Hà Nội.</p>
            </div>
        </div>
        ";
    }
}
