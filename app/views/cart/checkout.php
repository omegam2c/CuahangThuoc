<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="container section animate-fade-up">
    <h1 class="section-title">Thanh toán đơn hàng</h1>
    
    <?php if (isset($_SESSION['error_message'])): ?>
        <div style="background: #ffebee; color: #c62828; padding: 15px; border-radius: var(--radius-sm); margin-bottom: 20px; border-left: 4px solid #c62828;">
            <?= htmlspecialchars($_SESSION['error_message']) ?>
            <?php unset($_SESSION['error_message']); ?>
        </div>
    <?php endif; ?>
    
    <div class="checkout-grid" style="display: grid; grid-template-columns: 2fr 1.2fr; gap: 30px; margin-top: 30px;">
        <!-- Form thông tin giao hàng -->
        <div class="glass-card" style="padding: 30px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="margin: 0;">Thông tin giao hàng</h3>
                <?php if (isset($_COOKIE['last_shipping_info']) && empty($user['address'])): ?>
                    <span style="font-size: 0.75rem; color: var(--green); background: var(--green-light); padding: 4px 8px; border-radius: 20px;">
                        ✨ Đã tự động điền thông tin cũ
                    </span>
                <?php endif; ?>
            </div>
            <form action="<?= BASE_URL ?>order/placeOrder" method="POST" id="checkoutForm" enctype="multipart/form-data">
                <?= $csrf_field ?>
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px;">Họ và tên</label>
                    <input type="text" name="full_name" required minlength="2" placeholder="Ví dụ: Nguyễn Văn A" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: var(--radius-sm);" value="<?= htmlspecialchars($oldInput['full_name'] ?? $user['full_name'] ?? $_SESSION['full_name'] ?? '') ?>">
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px;">Số điện thoại</label>
                    <input type="text" name="phone" required pattern="^(0|84)(3|5|7|8|9)([0-9]{8})$" title="Vui lòng nhập số điện thoại Việt Nam hợp lệ (10 chữ số, bắt đầu bằng 0 hoặc 84)" placeholder="Ví dụ: 0912345678" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: var(--radius-sm);" value="<?= htmlspecialchars($oldInput['phone'] ?? $user['phone'] ?? '') ?>">
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px;">Email nhận thông báo</label>
                    <input type="email" name="email" required placeholder="Ví dụ: nva@gmail.com" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: var(--radius-sm);" value="<?= htmlspecialchars($oldInput['email'] ?? $user['email'] ?? $_SESSION['user_email'] ?? '') ?>">
                    <small style="color: #666;">Chúng tôi sẽ gửi mã đơn hàng và thông tin cập nhật vào email này.</small>
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px;">Địa chỉ nhận hàng</label>
                    <textarea name="address" required minlength="10" placeholder="Số nhà, tên đường, phường/xã, quận/huyện..." style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: var(--radius-sm); min-height: 100px;"><?= htmlspecialchars($oldInput['address'] ?? $user['address'] ?? '') ?></textarea>
                </div>

                <?php if ($hasRx): ?>
                <div style="margin-bottom: 20px; padding: 20px; background: #fff8e1; border-radius: var(--radius-sm); border: 2px dashed #ffb300;">
                    <h4 style="color: #ff8f00; margin-bottom: 10px; display: flex; align-items: center; gap: 8px;">
                        <span>⚠️</span> Thuốc cần kê đơn
                    </h4>
                    <p style="font-size: 0.85rem; color: #666; margin-bottom: 15px;">Trong giỏ hàng của bạn có sản phẩm yêu cầu đơn thuốc của bác sĩ. Vui lòng tải ảnh đơn thuốc lên để được xử lý.</p>
                    <input type="file" name="prescription" id="prescriptionInput" accept="image/*" required style="width: 100%; padding: 8px; border: 1px solid #ffb300; background: white; border-radius: 5px;">
                    <div id="previewContainer" style="margin-top: 15px; display: none;">
                        <p style="font-size: 0.8rem; color: #666; margin-bottom: 5px;">Ảnh đã chọn:</p>
                        <img id="prescriptionPreview" src="#" alt="Preview" style="max-width: 100%; border-radius: 5px; box-shadow: var(--shadow-sm);">
                    </div>
                </div>
                <script>
                    document.getElementById('prescriptionInput').onchange = function(evt) {
                        const [file] = this.files;
                        if (file) {
                            const preview = document.getElementById('prescriptionPreview');
                            const container = document.getElementById('previewContainer');
                            preview.src = URL.createObjectURL(file);
                            container.style.display = 'block';
                        }
                    }
                </script>
                <?php endif; ?>
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 10px; font-weight: 500;">💳 Phương thức thanh toán</label>
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <label style="padding: 15px; border: 1px solid var(--border); border-radius: var(--radius-sm); cursor: pointer; display: flex; align-items: center; gap: 10px; transition: all 0.2s;" class="payment-option">
                            <input type="radio" name="payment_method" value="cod" checked style="width: 18px; height: 18px;">
                            <span style="flex: 1;"><strong>Thanh toán khi nhận hàng (COD)</strong><br><small style="color: #666;">Thanh toán bằng tiền mặt khi giao hàng</small></span>
                        </label>
                        <label style="padding: 15px; border: 1px solid var(--border); border-radius: var(--radius-sm); cursor: pointer; display: flex; align-items: center; gap: 10px; transition: all 0.2s;" class="payment-option">
                            <input type="radio" name="payment_method" value="payos" style="width: 18px; height: 18px;">
                            <span style="flex: 1;"><strong>Chuyển khoản QR (PayOS)</strong><br><small style="color: #666;">Thanh toán trực tuyến an toàn qua mã QR</small></span>
                        </label>
                    </div>
                </div>
                <style>
                    .payment-option:hover { background: #f8f9fa; border-color: var(--primary); }
                    input[type="radio"]:checked + span { color: var(--primary); }
                </style>
                
                <div style="margin-bottom: 15px; margin-top: 20px;">
                    <label style="display: block; margin-bottom: 5px;">Ghi chú</label>
                    <textarea name="note" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: var(--radius-sm);"></textarea>
                </div>

                <input type="hidden" name="total_amount" value="<?= $total ?? 0 ?>">
            </form>
        </div>
        
        <!-- Tóm tắt đơn hàng -->
        <div class="glass-card" style="padding: 30px; height: fit-content;">
            <h3 style="margin-bottom: 20px;">Tóm tắt đơn hàng</h3>
            <div class="cart-summary">
                <?php $subtotal = 0; foreach ($cartItems as $item): 
                    $itemTotal = $item['current_price'] * $item['quantity'];
                    $subtotal += $itemTotal;
                ?>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 0.9rem;">
                        <span>
                            <?= htmlspecialchars($item['product_name']) ?> x <?= $item['quantity'] ?>
                            <?php if (isset($item['is_prescription_required']) && ($item['is_prescription_required'] == 1 || $item['is_prescription_required'] === true)): ?>
                                <span style="color: var(--red); font-weight: bold; margin-left: 5px;">(Rx)</span>
                            <?php endif; ?>
                        </span>
                        <span><?= number_format($itemTotal) ?>đ</span>
                    </div>
                <?php endforeach; ?>
                <hr style="margin: 15px 0; border: 0; border-top: 1px solid var(--border);">
                <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 1.1rem; color: var(--green);">
                    <span>Tổng cộng:</span>
                    <span><?= number_format($subtotal) ?>đ</span>
                </div>
                
                <button type="submit" form="checkoutForm" id="btnPlaceOrder" class="btn btn-premium" style="width: 100%; margin-top: 25px;">
                    Xác nhận đặt hàng
                </button>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
