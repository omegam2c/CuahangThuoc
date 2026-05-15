<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="container section animate-fade-up">
    <div style="max-width: 800px; margin: 0 auto;">
        
        <div style="text-align: center; margin-bottom: 30px;">
            <h1 class="section-title" style="margin-bottom: 10px;">Tải lên đơn thuốc</h1>
            <p style="color: #666; font-size: 1.1rem;">Dược sĩ của chúng tôi sẽ tư vấn và báo giá trong thời gian sớm nhất.</p>
        </div>

        <?php if (!empty($error)): ?>
            <div style="background-color: #ffebee; color: #c62828; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 5px solid #c62828;">
                ⚠️ <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($_SESSION['error_message'])): ?>
            <div style="background-color: #ffebee; color: #c62828; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 5px solid #c62828;">
                ⚠️ <?= htmlspecialchars($_SESSION['error_message']) ?>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <div class="glass-card" style="padding: 40px;">
            <form action="<?= BASE_URL ?>prescription/upload" method="POST" enctype="multipart/form-data">
                <?= $csrf_field ?>
                
                <h3 style="margin-bottom: 20px; border-bottom: 2px solid var(--primary); padding-bottom: 10px; display: inline-block;">1. Hình ảnh đơn thuốc</h3>
                
                <div style="margin-bottom: 30px;">
                    <div style="border: 2px dashed var(--border); padding: 40px; text-align: center; border-radius: var(--radius-lg); background: #f9f9f9; position: relative;">
                        <input type="file" name="prescription_image" id="prescription_image" accept="image/*" required style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer;" onchange="previewImage(this)">
                        <div id="upload-placeholder">
                            <div style="font-size: 3rem; margin-bottom: 10px;">📸</div>
                            <strong style="color: var(--primary); font-size: 1.1rem;">Bấm vào đây để chọn ảnh</strong>
                            <p style="color: #888; font-size: 0.9rem; margin-top: 10px;">Hỗ trợ JPG, PNG, WEBP (Tối đa 5MB)</p>
                        </div>
                        <img id="image-preview" src="#" alt="Preview" style="display: none; max-width: 100%; max-height: 300px; margin: 0 auto; border-radius: var(--radius-sm); box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                    </div>
                </div>

                <h3 style="margin-bottom: 20px; border-bottom: 2px solid var(--primary); padding-bottom: 10px; display: inline-block;">2. Thông tin liên hệ</h3>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div>
                        <label style="display: block; font-weight: bold; margin-bottom: 8px;">Họ tên người nhận <span style="color: red;">*</span></label>
                        <input type="text" name="receiver_name" value="<?= htmlspecialchars($_SESSION['full_name'] ?? '') ?>" required style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-family: inherit;">
                    </div>
                    <div>
                        <label style="display: block; font-weight: bold; margin-bottom: 8px;">Số điện thoại <span style="color: red;">*</span></label>
                        <input type="tel" name="receiver_phone" required style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-family: inherit;">
                    </div>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: bold; margin-bottom: 8px;">Địa chỉ giao hàng <span style="color: red;">*</span></label>
                    <input type="text" name="shipping_address" required placeholder="Số nhà, tên đường, phường/xã, quận/huyện..." style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-family: inherit;">
                </div>

                <div style="margin-bottom: 30px;">
                    <label style="display: block; font-weight: bold; margin-bottom: 8px;">Ghi chú thêm cho Dược sĩ (Tùy chọn)</label>
                    <textarea name="note" rows="3" placeholder="Ví dụ: Lấy thêm 1 hộp Panadol đỏ, dị ứng với Penicillin..." style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-family: inherit; resize: vertical;"></textarea>
                </div>

                <div style="text-align: center;">
                    <button type="submit" class="btn btn-premium" style="width: 100%; max-width: 300px; padding: 15px; font-size: 1.1rem; text-transform: uppercase;">Gửi đơn thuốc</button>
                    <p style="margin-top: 15px; color: #666; font-size: 0.9rem;">Bằng việc gửi đơn thuốc, bạn đồng ý với các <a href="#" style="color: var(--primary);">Điều khoản dịch vụ</a> của chúng tôi.</p>
                </div>

            </form>
        </div>
        
    </div>
</div>

<script>
function previewImage(input) {
    var placeholder = document.getElementById('upload-placeholder');
    var preview = document.getElementById('image-preview');
    
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
            placeholder.style.display = 'none';
        }
        
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.src = '#';
        preview.style.display = 'none';
        placeholder.style.display = 'block';
    }
}
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
