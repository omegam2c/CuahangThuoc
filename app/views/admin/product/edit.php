<?php include __DIR__ . '/../../layout/header.php'; ?>

<style>
    .admin-create-container { display: grid; grid-template-columns: 2fr 1fr; gap: 30px; align-items: start; }
    .form-section { background: var(--glass-bg, #fff); border: 1px solid var(--border, #eee); border-radius: 16px; padding: 25px; margin-bottom: 25px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); }
    .form-section-title { font-size: 1.1rem; font-weight: 600; color: var(--blue); margin-bottom: 20px; display: flex; align-items: center; gap: 10px; border-bottom: 1px solid #f0f0f0; padding-bottom: 10px; }
    .input-label { display: block; font-size: 0.9rem; font-weight: 500; color: #555; margin-bottom: 8px; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 15px; }
    .full-row { margin-bottom: 15px; }
    
    .image-preview-container { border: 2px dashed #ddd; border-radius: 12px; padding: 20px; text-align: center; cursor: pointer; transition: all 0.3s; position: relative; overflow: hidden; background: #fcfcfc; }
    .image-preview-container:hover { border-color: var(--blue); background: #f7faf9; }
    .image-preview-container img { max-width: 100%; border-radius: 8px; display: block; margin: 10px auto 0; }
    
    .switch-group { display: flex; align-items: center; justify-content: space-between; padding: 12px; background: #f9f9f9; border-radius: 10px; margin-bottom: 15px; }
</style>

<div class="container section animate-fade-up">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <div>
            <h1 class="section-title" style="margin-bottom: 5px;">Sửa sản phẩm #<?= $product['product_id'] ?></h1>
            <p style="color: #666; margin: 0;">Cập nhật thông tin chi tiết cho sản phẩm: <strong><?= htmlspecialchars($product['product_name']) ?></strong></p>
        </div>
        <a href="<?= BASE_URL ?>admin/product" class="btn btn-outline"> Quay lại danh sách</a>
    </div>

    <form action="<?= BASE_URL ?>admin/product/update" method="POST" enctype="multipart/form-data">
        <?= $csrf_field ?>
        <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
        <input type="hidden" name="old_image" value="<?= $product['image_url'] ?>">

        <div class="admin-create-container">
            
            <!-- CỘT TRÁI: THÔNG TIN CHI TIẾT -->
            <div>
                <div class="form-section">
                    <div class="form-section-title"><span>📝</span> Thông tin cơ bản</div>
                    
                    <div class="full-row">
                        <label class="input-label">Tên sản phẩm (Tên biệt dược) *</label>
                        <input type="text" name="product_name" class="form-control" value="<?= htmlspecialchars($product['product_name']) ?>" required>
                    </div>
                    
                    <div class="form-row">
                        <div>
                            <label class="input-label">Tên hoạt chất (Generic Name)</label>
                            <input type="text" name="generic_name" class="form-control" value="<?= htmlspecialchars($product['generic_name']) ?>">
                        </div>
                        <div>
                            <label class="input-label">Đơn vị tính</label>
                            <input type="text" name="unit" class="form-control" value="<?= htmlspecialchars($product['unit']) ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div>
                            <label class="input-label">Danh mục thuốc *</label>
                            <select name="category_id" class="form-control" required>
                                <?php foreach($categories as $cat): ?>
                                    <option value="<?= $cat['category_id'] ?>" <?= $product['category_id'] == $cat['category_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['category_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="input-label">Nhà sản xuất *</label>
                            <select name="manufacturer_id" class="form-control" required>
                                <?php foreach($manufacturers as $man): ?>
                                    <option value="<?= $man['manufacturer_id'] ?>" <?= $product['manufacturer_id'] == $man['manufacturer_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($man['manufacturer_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-title"><span>🔬</span> Thông tin y khoa</div>
                    
                    <div class="form-row">
                        <div>
                            <label class="input-label">Dạng bào chế</label>
                            <input type="text" name="dosage_form" class="form-control" value="<?= htmlspecialchars($product['dosage_form']) ?>">
                        </div>
                        <div>
                            <label class="input-label">Hàm lượng</label>
                            <input type="text" name="strength" class="form-control" value="<?= htmlspecialchars($product['strength'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="full-row">
                        <label class="input-label">Thành phần chi tiết</label>
                        <textarea name="active_ingredients" class="form-control" rows="3"><?= htmlspecialchars($product['active_ingredients']) ?></textarea>
                    </div>

                    <div class="full-row">
                        <label class="input-label">Chỉ định & Công dụng</label>
                        <textarea name="indications" class="form-control" rows="4"><?= htmlspecialchars($product['indications']) ?></textarea>
                    </div>
                </div>
            </div>

            <!-- CỘT PHẢI: GIÁ CẢ & HÌNH ẢNH -->
            <aside>
                <div class="form-section">
                    <div class="form-section-title"><span>💰</span> Giá cả & Thuế</div>
                    <div class="full-row">
                        <label class="input-label">Giá bán lẻ (VNĐ) *</label>
                        <input type="number" name="price" class="form-control" value="<?= $product['price'] ?>" style="font-weight: bold; font-size: 1.1rem; color: var(--blue);" required>
                    </div>
                    <div class="full-row">
                        <label class="input-label">Giảm giá (%)</label>
                        <input type="number" name="discount_percent" class="form-control" value="<?= $product['discount_percent'] ?>" min="0" max="100">
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-title"><span>🖼️</span> Hình ảnh sản phẩm</div>
                    <div class="image-preview-container" onclick="document.getElementById('productImage').click()">
                        <div id="uploadPlaceholder" style="display: none;">
                            <span style="font-size: 2rem;">📸</span>
                            <p style="margin: 10px 0 0; font-size: 0.85rem; color: #888;">Click để thay đổi ảnh</p>
                        </div>
                        <?php 
                            $imgSrc = $product['image_url'] ? BASE_URL . 'public/img/products/' . $product['image_url'] : 'https://via.placeholder.com/300x300?text=No+Image';
                        ?>
                        <img id="previewImg" src="<?= $imgSrc ?>" alt="Preview">
                        <input type="file" name="image" id="productImage" hidden accept="image/*" onchange="previewFile()">
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-title"><span>⚙️</span> Phân loại quản lý</div>
                    <div class="switch-group">
                        <span style="font-size: 0.9rem;">Yêu cầu đơn thuốc (Rx)</span>
                        <input type="checkbox" name="is_prescription_required" value="1" <?= $product['is_prescription_required'] ? 'checked' : '' ?> style="width: 20px; height: 20px; cursor: pointer;">
                    </div>
                </div>

                <button type="submit" class="btn btn-premium" style="width: 100%; padding: 15px; font-size: 1.1rem; background: var(--blue);">
                    💾 Cập nhật thay đổi
                </button>
            </aside>

        </div>
    </form>
</div>

<script>
function previewFile() {
    const preview = document.getElementById('previewImg');
    const file = document.getElementById('productImage').files[0];
    const reader = new FileReader();

    reader.onloadend = function () {
        preview.src = reader.result;
    }

    if (file) {
        reader.readAsDataURL(file);
    }
}
</script>

<?php include __DIR__ . '/../../layout/footer.php'; ?>
