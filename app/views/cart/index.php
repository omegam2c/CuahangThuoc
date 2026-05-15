<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="container section animate-fade-up">
    <h1 class="section-title">Giỏ hàng của bạn</h1>

    <?php if (!empty($interactions)): ?>
        <div class="glass-card" style="padding: 20px; margin-bottom: 30px; border-left: 5px solid var(--orange); background: #fff8f1;">
            <h3 style="color: #e65100; margin-bottom: 10px;">⚠️ Cảnh báo tương tác thuốc</h3>
            <?php foreach ($interactions as $i): ?>
                <div style="margin-bottom: 15px; font-size: 0.95rem;">
                    <p><strong><?= htmlspecialchars($i['drug_1']) ?></strong> và <strong><?= htmlspecialchars($i['drug_2']) ?></strong> có thể tương tác với nhau.</p>
                    <p><strong>Mức độ:</strong> <span style="color: var(--red);"><?= strtoupper($i['severity']) ?></span></p>
                    <p><strong>Mô tả:</strong> <?= htmlspecialchars($i['description']) ?></p>
                    <p><strong>Khuyến nghị:</strong> <em><?= htmlspecialchars($i['recommendation']) ?></em></p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (empty($cartItems)): ?>
        <div class="glass-card" style="padding: 50px; text-align: center;">
            <p>Giỏ hàng của bạn đang trống.</p>
            <a href="<?= BASE_URL ?>home" class="btn btn-premium" style="margin-top: 20px;">Mua sắm ngay</a>
        </div>
    <?php else: ?>
        <div class="cart-grid" style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px; margin-top: 30px;">
            <div class="glass-card" style="padding: 20px;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="text-align: left; border-bottom: 1px solid var(--border);">
                            <th style="padding: 10px;">Sản phẩm</th>
                            <th style="padding: 10px;">Giá</th>
                            <th style="padding: 10px;">Số lượng</th>
                            <th style="padding: 10px;">Thành tiền</th>
                            <th style="padding: 10px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cartItems as $item): ?>
                            <tr style="border-bottom: 1px solid var(--border);">
                                <td style="padding: 15px 10px;">
                                    <div style="display: flex; align-items: center; gap: 15px;">
                                        <img src="public/img/products/<?= htmlspecialchars($item['image_url'] ?? 'placeholder.png') ?>" style="width: 50px; height: 50px; border-radius: var(--radius-sm);">
                                        <strong><?= htmlspecialchars($item['product_name']) ?></strong>
                                    </div>
                                </td>
                                <td style="padding: 10px;"><?= number_format($item['current_price']) ?>đ</td>
                                <td style="padding: 10px;">
                                    <input type="number" class="qty-input" data-id="<?= $item['product_id'] ?>" value="<?= $item['quantity'] ?>" min="1" style="width: 60px; padding: 5px; border-radius: var(--radius-sm); border: 1px solid var(--border);">
                                </td>
                                <td style="padding: 10px;"><strong class="item-total-<?= $item['product_id'] ?>"><?= number_format($item['current_price'] * $item['quantity']) ?>đ</strong></td>
                                <td style="padding: 10px;">
                                    <button class="remove-btn" data-id="<?= $item['product_id'] ?>" style="color: var(--red); background:none; border:none; cursor:pointer;">🗑️</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="glass-card" style="padding: 30px; height: fit-content;">
                <h3 style="margin-bottom: 20px;">Tóm tắt thanh toán</h3>
                <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                    <span>Tạm tính:</span>
                    <span id="cart-subtotal"><?= number_format($total) ?>đ</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                    <span>Phí vận chuyển:</span>
                    <span>Miễn phí</span>
                </div>
                <hr style="margin: 20px 0; border: 0; border-top: 1px solid var(--border);">
                <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 1.2rem; color: var(--green);">
                    <span>Tổng cộng:</span>
                    <span id="cart-total"><?= number_format($total) ?>đ</span>
                </div>
                <a href="<?= BASE_URL ?>order/checkout" class="btn btn-premium" style="width: 100%; margin-top: 30px; text-align: center; display: block;">Tiến hành thanh toán</a>
                <a href="<?= BASE_URL ?>home" class="btn btn-outline" style="width: 100%; margin-top: 15px; text-align: center; display: block;">Tiếp tục mua sắm</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const qtyInputs = document.querySelectorAll('.qty-input');
    const removeBtns = document.querySelectorAll('.remove-btn');
    
    qtyInputs.forEach(input => {
        input.addEventListener('change', function() {
            const productId = this.getAttribute('data-id');
            const qty = this.value;
            if(qty < 1) { this.value = 1; return; }
            
            fetch('<?= BASE_URL ?>cart/updateAjax', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': window.csrfToken
                },
                body: JSON.stringify({ product_id: productId, quantity: qty })
            })
            .then(res => res.json())
            .then(data => {
                const payload = data.payload || data;
                if(data.success) {
                    document.getElementById('cart-subtotal').textContent = payload.total;
                    document.getElementById('cart-total').textContent = payload.total;
                    const itemTotalEl = document.querySelector('.item-total-' + productId);
                    if (itemTotalEl) itemTotalEl.textContent = payload.item_total;
                    
                    // Update all header cart count elements
                    document.querySelectorAll('.cart-count').forEach(el => {
                        const count = parseInt(payload.cart_count) || 0;
                        el.textContent = count;
                        el.style.display = count > 0 ? 'flex' : 'none';
                    });
                    if(document.getElementById('cartTotal')) {
                        document.getElementById('cartTotal').textContent = payload.total;
                    }
                }
            });
        });
    });
    
    removeBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            if(!confirm('Bạn có chắc muốn xóa sản phẩm này?')) return;
            const productId = this.getAttribute('data-id');
            
            fetch('<?= BASE_URL ?>cart/removeAjax', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': window.csrfToken
                },
                body: JSON.stringify({ product_id: productId })
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    window.location.reload();
                }
            });
        });
    });
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
