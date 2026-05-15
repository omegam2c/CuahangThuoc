<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="container section animate-fade-up" style="text-align: center; max-width: 600px; margin: 0 auto;">
    <div class="glass-card" style="padding: 50px;">
        <div style="font-size: 5rem; margin-bottom: 20px;" class="float-icon">🎉</div>
        <h1 style="color: var(--green); margin-bottom: 15px;">Đặt hàng thành công!</h1>
        <p style="margin-bottom: 30px;">
            <?php if (isset($order) && $order['has_prescription']): ?>
                Cảm ơn bạn đã tin tưởng Nhà thuốc 1985. Đơn hàng của bạn đã được tiếp nhận và **đang chờ Dược sĩ kiểm tra đơn thuốc**.
            <?php else: ?>
                Cảm ơn bạn đã tin tưởng Nhà thuốc 1985. Đơn hàng của bạn đã được tiếp nhận và đang chờ xử lý.
            <?php endif; ?>
        </p>
        
        <?php if (isset($order)): ?>
            <div style="background: var(--bg-page); padding: 20px; border-radius: var(--radius); border: 1px solid var(--border); margin-bottom: 30px; text-align: left;">
                <p><strong>Mã đơn hàng:</strong> #<?= htmlspecialchars($order['order_id']) ?></p>
                <p><strong>Tổng thanh toán:</strong> <span style="color: var(--red); font-weight: bold;"><?= number_format($order['total_amount']) ?>đ</span></p>
                <p><strong>Phương thức:</strong> 
                    <?= $order['payment_method'] === 'cod' ? 'Thanh toán khi nhận hàng (COD)' : 
                       ($order['payment_method'] === 'e_wallet' ? 'Ví điện tử VNPAY' : 'Chuyển khoản Ngân hàng (SePay)') ?>
                </p>
                <p><strong>Trạng thái thanh toán:</strong> 
                    <span id="payment-status-badge" style="padding: 4px 10px; border-radius: 20px; font-size: 0.85rem; font-weight: bold; background: <?= ($order['payment_status'] ?? 'pending') === 'paid' ? '#e8f5e9; color: #2e7d32;' : '#fff3e0; color: #ef6c00;' ?>">
                        <?= ($order['payment_status'] ?? 'pending') === 'paid' ? '✅ Đã thanh toán' : '⏳ Chờ thanh toán' ?>
                    </span>
                </p>

                <?php if ($order['has_prescription']): ?>
                    <div style="margin-top: 15px; padding: 15px; background: #fff3e0; border: 1px solid #ffe0b2; border-radius: var(--radius-sm); display: flex; align-items: flex-start; gap: 10px;">
                        <span style="font-size: 1.2rem;">👨‍⚕️</span>
                        <div>
                            <p style="margin: 0; color: #e65100; font-weight: 700; font-size: 0.9rem;">Đơn hàng cần Dược sĩ duyệt</p>
                            <p style="margin: 0; color: #666; font-size: 0.8rem; line-height: 1.4;">Vì đơn hàng có thuốc kê đơn, Dược sĩ của chúng tôi sẽ kiểm tra ảnh đơn thuốc bạn đã gửi và liên hệ xác nhận trong thời gian sớm nhất.</p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($order['payment_method'] === 'bank_transfer' && ($order['payment_status'] ?? 'pending') !== 'paid'): ?>
                    <div style="margin-top: 25px; padding: 20px; background: #fff; border-radius: var(--radius); border: 1px dashed var(--primary); text-align: center;">
                        <h4 style="color: var(--primary); margin-bottom: 15px;">Quét mã QR để thanh toán</h4>
                        <img src="https://qr.sepay.vn/img?acc=0968623156&bank=MBBank&amount=<?= $order['total_amount'] ?>&des=DH<?= $order['order_id'] ?>" 
                             alt="QR Thanh toán" style="max-width: 250px; border: 1px solid #eee; padding: 10px; border-radius: 10px;">
                        <div style="margin-top: 15px; font-size: 0.9rem; line-height: 1.6;">
                            <p>Nội dung chuyển khoản: <strong style="color: var(--red); font-size: 1.1rem;">DH<?= $order['order_id'] ?></strong></p>
                            <p style="color: #666; font-size: 0.8rem;">(Hệ thống sẽ tự động xác nhận sau khi bạn chuyển khoản thành công)</p>
                        </div>
                    </div>
                    <script>
                        // Tự động reload trang sau 10 giây để cập nhật trạng thái nếu cần
                        setTimeout(() => {
                            if (document.getElementById('payment-status-badge').innerText.includes('Chờ thanh toán')) {
                                window.location.reload();
                            }
                        }, 10000);
                    </script>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <div style="display: flex; gap: 15px; justify-content: center;">
            <a href="<?= BASE_URL ?>home" class="btn btn-premium">Tiếp tục mua sắm</a>
            <a href="<?= BASE_URL ?>account/orders" class="btn btn-outline">Xem đơn hàng</a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
