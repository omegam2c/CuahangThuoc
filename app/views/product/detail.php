<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="container section animate-fade-up">
    <!-- Breadcrumb -->
    <div style="margin-bottom: 20px; font-size: 0.9rem; color: #666;">
        <a href="<?= BASE_URL ?>" style="color: var(--primary); text-decoration: none;">Trang chủ</a> &raquo;
        <a href="<?= BASE_URL ?>products" style="color: var(--primary); text-decoration: none;">Sản phẩm</a> &raquo;
        <?= htmlspecialchars($product['product_name']) ?>
    </div>

    <div class="glass-card" style="padding: 30px; display: grid; grid-template-columns: 1fr 1.5fr; gap: 40px;">
        <!-- Hình ảnh sản phẩm -->
        <div style="background: #fff; padding: 20px; border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center; overflow: hidden; border: 1px solid var(--border);">
            <?php
                $rawImage = trim((string)($product['image_url'] ?? ''));
                $isRemoteImage = preg_match('#^https?://#i', $rawImage) === 1;
                $localImageFile = __DIR__ . '/../../../public/img/products/' . $rawImage;
                if ($isRemoteImage) {
                    $imageSrc = $rawImage;
                } elseif ($rawImage !== '' && file_exists($localImageFile)) {
                    $imageSrc = BASE_URL . 'public/img/products/' . rawurlencode($rawImage);
                } else {
                    $imageSrc = 'https://picsum.photos/seed/pharmacy-detail-' . (int)($product['product_id'] ?? 0) . '/800/800';
                }
            ?>
            <img src="<?= $imageSrc ?>" alt="<?= htmlspecialchars($product['product_name']) ?>" style="max-width: 100%; max-height: 400px; object-fit: contain;" onerror="this.onerror=null;this.src='<?= BASE_URL ?>public/img/placeholder.png';">
        </div>
        
        <!-- Thông tin chi tiết -->
        <div>
            <h1 style="font-size: 1.8rem; margin-bottom: 10px; color: var(--text-color);"><?= htmlspecialchars($product['product_name']) ?></h1>
            
            <div style="margin-bottom: 15px;">
                <?php if ($product['sale_type'] === 'prescription'): ?>
                    <span class="badge" style="background: #ffebee; color: #c62828; padding: 5px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: bold; border: 1px solid #ffcdd2;">
                        ⚠️ Thuốc kê đơn (Rx)
                    </span>
                <?php else: ?>
                    <span class="badge" style="background: #e8f5e9; color: #2e7d32; padding: 5px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: bold; border: 1px solid #c8e6c9;">
                        ✅ Thuốc không kê đơn (OTC)
                    </span>
                <?php endif; ?>
            </div>

            <div style="margin-bottom: 20px;">
                <?php 
                    $currentPrice = $product['current_price'] ?? $product['price'];
                    $oldPrice = $product['price'];
                    $hasDiscount = $oldPrice > $currentPrice;
                ?>
                <span style="font-size: 2rem; font-weight: bold; color: var(--primary);">
                    <?= number_format($currentPrice) ?>đ
                </span>
                <?php if ($hasDiscount): ?>
                    <span style="text-decoration: line-through; color: #999; margin-left: 15px; font-size: 1.2rem;">
                        <?= number_format($oldPrice) ?>đ
                    </span>
                    <span style="background: #e65100; color: #fff; padding: 3px 8px; border-radius: 4px; font-size: 0.9rem; margin-left: 10px;">
                        -<?= $product['discount_percent'] ?>%
                    </span>
                <?php endif; ?>
            </div>
            
            <div style="margin-bottom: 25px; background: #f9f9f9; padding: 20px; border-radius: var(--radius-sm); border-left: 4px solid var(--primary);">
                <h3 style="font-size: 1rem; margin-bottom: 10px; color: var(--text-color);">Thành phần & Hoạt chất</h3>
                <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                    <?php if (!empty($product['ingredients'])): ?>
                        <?php foreach ($product['ingredients'] as $ing): ?>
                            <span style="background: #fff; border: 1px solid var(--border); padding: 4px 10px; border-radius: 4px; font-size: 0.9rem;">
                                <strong><?= htmlspecialchars($ing['ingredient_name']) ?></strong>: <?= htmlspecialchars($ing['amount']) ?>
                            </span>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <span style="color: #666; font-size: 0.9rem; font-style: italic;">Đang cập nhật thành phần...</span>
                    <?php endif; ?>
                </div>
            </div>

            <div style="margin-bottom: 30px; line-height: 1.6; color: #555;">
                <h3 style="font-size: 1rem; margin-bottom: 8px; color: var(--text-color);">Công dụng / Chỉ định</h3>
                <p><?= nl2br(htmlspecialchars($product['description'] ?? 'Đang cập nhật mô tả...')) ?></p>
            </div>
            
            <div style="display: flex; gap: 15px; margin-bottom: 30px;">
                <?php if (($product['stock_quantity'] ?? 0) > 0): ?>
                    <div style="display: flex; border: 1px solid var(--border); border-radius: var(--radius-sm); overflow: hidden; width: 120px;">
                        <button type="button" onclick="const q=document.getElementById('detailQty'); if(q.value>1) q.value--;" style="width: 40px; background: #f5f5f5; border: none; font-size: 1.2rem; cursor: pointer;">-</button>
                        <input type="number" id="detailQty" value="1" min="1" style="width: 40px; text-align: center; border: none; border-left: 1px solid var(--border); border-right: 1px solid var(--border); font-weight: bold;">
                        <button type="button" onclick="const q=document.getElementById('detailQty'); q.value++;" style="width: 40px; background: #f5f5f5; border: none; font-size: 1.2rem; cursor: pointer;">+</button>
                    </div>
                    <button onclick="addToCart(<?= $product['product_id'] ?>, document.getElementById('detailQty').value)" class="btn btn-premium" style="flex: 1; font-size: 1.1rem;">🛒 Thêm vào giỏ hàng</button>
                <?php else: ?>
                    <button disabled class="btn" style="flex: 1; font-size: 1.1rem; background: #999; color: white; cursor: not-allowed; border: none; padding: 12px;">Sản phẩm hiện đang hết hàng</button>
                <?php endif; ?>
            </div>
            
            <hr style="border: 0; border-top: 1px solid var(--border); margin: 20px 0;">
            
            <ul style="list-style: none; padding: 0; line-height: 2; font-size: 0.95rem; color: #666;">
                <li><strong style="color: #333;">Dạng bào chế:</strong> <?= htmlspecialchars($product['dosage_form'] ?? 'Đang cập nhật') ?></li>
                <li><strong style="color: #333;">Quy cách:</strong> <?= htmlspecialchars($product['unit'] ?? 'Viên') ?></li>
                <li><strong style="color: #333;">Thương hiệu:</strong> <?= htmlspecialchars($product['brand'] ?? 'Đang cập nhật') ?></li>
                <li><strong style="color: #333;">Nhà sản xuất:</strong> <?= htmlspecialchars($product['manufacturer'] ?? 'Đang cập nhật') ?></li>
            </ul>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
