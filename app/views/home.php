<?php
// home.php - Trang chủ Nhà thuốc 1985
// Giả sử $featuredProducts, $categories được truyền từ controller
?>

<?php include __DIR__ . '/layout/header.php'; ?>

<!-- ===== HERO BANNER ===== -->
<section class="hero-banner">
  <div class="hero-slider" id="heroSlider">

    <div class="hero-slide active" style="--bg: url('../public/images/banner1.jpg')">
      <div class="hero-content">
        <span class="hero-tag">🌿 Sản phẩm mới</span>
        <h1>Chăm sóc sức khỏe<br><strong>toàn diện</strong> mỗi ngày</h1>
        <p>Hơn 10,000+ sản phẩm chính hãng từ các thương hiệu uy tín toàn cầu</p>
        <div class="hero-actions">
          <a href="<?= BASE_URL ?>products" class="btn btn-premium">Mua ngay</a>
          <a href="<?= BASE_URL ?>prescription" class="btn btn-outline">Tư vấn thuốc</a>
        </div>
      </div>
      <div class="hero-badge">
        <span class="badge-circle">
          <strong>100%</strong>
          <small>Chính hãng</small>
        </span>
      </div>
    </div>

    <div class="hero-slide" style="--bg: url('../public/images/banner2.jpg')">
      <div class="hero-content">
        <span class="hero-tag">💊 Ưu đãi hôm nay</span>
        <h1>Giảm đến <strong>40%</strong><br>vitamin & thực phẩm chức năng</h1>
        <p>Chương trình khuyến mãi giới hạn — chỉ trong tuần này</p>
        <div class="hero-actions">
          <a href="<?= BASE_URL ?>products?category=8" class="btn btn-primary">Xem ưu đãi</a>
        </div>
      </div>
    </div>

    <div class="hero-slide" style="--bg: url('../public/images/banner3.jpg')">
      <div class="hero-content">
        <span class="hero-tag">📋 Dịch vụ mới</span>
        <h1>Upload đơn thuốc —<br>nhận hàng <strong>tận nhà</strong></h1>
        <p>Giao nhanh 2 giờ trong nội thành TP.HCM và Hà Nội</p>
        <div class="hero-actions">
          <a href="<?= BASE_URL ?>prescription/upload" class="btn btn-primary">Upload đơn thuốc</a>
        </div>
      </div>
    </div>

  </div>

  <!-- Slider dots -->
  <div class="slider-dots">
    <button class="dot active" onclick="goSlide(0)"></button>
    <button class="dot" onclick="goSlide(1)"></button>
    <button class="dot" onclick="goSlide(2)"></button>
  </div>

  <!-- Slider arrows -->
  <button class="slider-arrow prev" onclick="changeSlide(-1)">&#8249;</button>
  <button class="slider-arrow next" onclick="changeSlide(1)">&#8250;</button>
</section>

<!-- ===== TRUST BAR ===== -->
<section class="trust-bar container animate-fade-up" style="margin-top: -50px; position: relative; z-index: 10; border-radius: var(--radius-lg); box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
  <div class="container">
    <div class="trust-grid">
      <div class="trust-item">
        <span class="trust-icon">🏥</span>
        <div>
          <strong>Cam kết chính hãng</strong>
          <p>Hoàn tiền 100% nếu hàng giả</p>
        </div>
      </div>
      <div class="trust-item">
        <span class="trust-icon">🚚</span>
        <div>
          <strong>Giao hàng nhanh 2 giờ</strong>
          <p>Nội thành TP.HCM & Hà Nội</p>
        </div>
      </div>
      <div class="trust-item">
        <span class="trust-icon">💬</span>
        <div>
          <strong>Tư vấn dược sĩ 24/7</strong>
          <p>Miễn phí, không cần đặt lịch</p>
        </div>
      </div>
      <div class="trust-item">
        <span class="trust-icon">🔄</span>
        <div>
          <strong>Đổi trả trong 30 ngày</strong>
          <p>Không cần lý do, dễ dàng</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ===== DANH MỤC SẢN PHẨM ===== -->
<section class="section categories-section">
  <div class="container">
    <div class="section-header">
      <h2 class="section-title">Danh mục sản phẩm</h2>
      <a href="<?= BASE_URL ?>products" class="see-all">Xem tất cả →</a>
    </div>
    <div class="categories-grid">
      <?php if (!empty($categories)): ?>
        <?php foreach ($categories as $cat): 
          // Mapping icon và màu cho danh mục
          $icons = [
            'Thuốc kê đơn' => ['icon' => '💊', 'class' => 'cat-red'],
            'Thuốc không kê đơn' => ['icon' => '🧴', 'class' => 'cat-orange'],
            'Thực phẩm chức năng' => ['icon' => '🌿', 'class' => 'cat-green'],
            'Chăm sóc cá nhân' => ['icon' => '✨', 'class' => 'cat-pink'],
            'Thiết bị y tế' => ['icon' => '🩺', 'class' => 'cat-blue'],
          ];
          $catInfo = $icons[$cat['category_name']] ?? ['icon' => '📦', 'class' => 'cat-gray'];
        ?>
        <a href="<?= BASE_URL ?>products?category=<?= $cat['category_id'] ?>" class="category-card glass-card <?= $catInfo['class'] ?>">
          <div class="cat-icon"><?= $catInfo['icon'] ?></div>
          <span><?= htmlspecialchars($cat['category_name']) ?></span>
        </a>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- ===== BANNER UPLOAD ĐƠN THUỐC ===== -->
<section class="promo-banner-strip">
  <div class="container">
    <div class="promo-strip-grid">

      <div class="promo-strip-card promo-rx">
        <div class="promo-strip-text">
          <h3>Upload đơn thuốc</h3>
          <p>Dược sĩ tư vấn & giao thuốc tận nhà</p>
          <a href="<?= BASE_URL ?>prescription/upload" class="btn btn-white-outline">Upload ngay</a>
        </div>
        <div class="promo-strip-img">📋</div>
      </div>

      <div class="promo-strip-card promo-consult">
        <div class="promo-strip-text">
          <h3>Tư vấn sức khỏe</h3>
          <p>Chat trực tiếp với dược sĩ chuyên nghiệp</p>
          <a href="<?= BASE_URL ?>consult" class="btn btn-white-outline">Tư vấn miễn phí</a>
        </div>
        <div class="promo-strip-img">🩺</div>
      </div>

    </div>
  </div>
</section>

<!-- ===== SẢN PHẨM NỔI BẬT ===== -->
<section class="section products-section">
  <div class="container">
    <div class="section-header">
      <h2 class="section-title">Sản phẩm nổi bật</h2>
      <div class="product-tabs">
        <button class="tab-btn active" data-tab="hot" onclick="switchTab('hot')">🔥 Bán chạy</button>
        <button class="tab-btn" data-tab="sale" onclick="switchTab('sale')">💸 Khuyến mãi</button>
        <button class="tab-btn" data-tab="new" onclick="switchTab('new')">🆕 Mới nhất</button>
      </div>
    </div>

    <!-- Tab Bán chạy -->
    <div class="products-grid tab-content" id="tab-hot" style="display: grid;">
      <?php foreach ($bestSellers as $p): 
        $discount = ($p['old_price'] && $p['old_price'] > $p['current_price']) 
                    ? round((1 - $p['current_price']/$p['old_price'])*100) : 0;
      ?>
      <div class="product-card" data-id="<?= $p['product_id'] ?>">
        <?php if (!empty($p['badge'])): ?>
          <span class="product-badge <?= (strpos((string)$p['badge'], 'Sale') !== false) ? 'badge-sale' : 'badge-hot' ?>">
            <?= htmlspecialchars($p['badge']) ?>
          </span>
        <?php endif; ?>
        <?php if ($discount > 0): ?>
          <span class="product-discount">-<?= $discount ?>%</span>
        <?php endif; ?>

        <div class="product-img-wrap">
          <?php
            $rawImage = trim((string)($p['image_url'] ?? ''));
            $isRemoteImage = preg_match('#^https?://#i', $rawImage) === 1;
            $imageSrc = $isRemoteImage
              ? $rawImage
              : (BASE_URL . 'public/img/products/' . htmlspecialchars($rawImage !== '' ? $rawImage : 'placeholder.png'));
          ?>
          <img src="<?= $imageSrc ?>"
               alt="<?= htmlspecialchars($p['product_name']) ?>"
               onerror="this.onerror=null;this.src='https://placehold.co/600x600/f3f4f6/374151?text=No+Image';">
          <div class="product-actions-hover">
            <?php if (($p['stock_quantity'] ?? 0) > 0): ?>
              <button class="btn-quick-add" onclick="addToCart(<?= $p['product_id'] ?>)">
                🛒 Thêm vào giỏ
              </button>
            <?php else: ?>
              <button class="btn-quick-add" disabled style="background: #999; cursor: not-allowed; border-color: #999;">
                Hết hàng
              </button>
            <?php endif; ?>
          </div>
        </div>

        <?php if (($p['stock_quantity'] ?? 0) <= 0): ?>
          <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255,255,255,0.4); z-index: 5; pointer-events: none; display: flex; align-items: center; justify-content: center;">
             <span style="background: rgba(0,0,0,0.6); color: white; padding: 5px 15px; border-radius: 20px; font-weight: bold; transform: rotate(-15deg); border: 2px solid white;">HẾT HÀNG</span>
          </div>
        <?php endif; ?>

        <div class="product-info">
          <span class="product-brand"><?= htmlspecialchars($p['brand'] ?? 'N/A') ?></span>
          <h3 class="product-name">
            <a href="<?= BASE_URL ?>product/<?= $p['product_id'] ?>"><?= htmlspecialchars($p['product_name']) ?></a>
          </h3>
          <div class="product-rating">
            <span class="stars"><?= str_repeat('★', floor($p['rating'] ?? 4.5)) ?></span>
            <span class="review-count">(<?= number_format($p['reviews'] ?? 0) ?>)</span>
          </div>
          <div class="product-price-row">
            <span class="price-current"><?= number_format($p['current_price']) ?>đ</span>
            <?php if ($p['old_price'] && $p['old_price'] > $p['current_price']): ?>
              <span class="price-old"><?= number_format($p['old_price']) ?>đ</span>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Tab Khuyến mãi -->
    <div class="products-grid tab-content" id="tab-sale" style="display: none;">
      <?php foreach ($saleProducts as $p): 
        $discount = ($p['old_price'] && $p['old_price'] > $p['current_price']) 
                    ? round((1 - $p['current_price']/$p['old_price'])*100) : 0;
      ?>
      <div class="product-card" data-id="<?= $p['product_id'] ?>">
        <?php if (!empty($p['badge'])): ?>
          <span class="product-badge badge-sale">
            <?= htmlspecialchars($p['badge']) ?>
          </span>
        <?php endif; ?>
        <?php if ($discount > 0): ?>
          <span class="product-discount">-<?= $discount ?>%</span>
        <?php endif; ?>

        <div class="product-img-wrap">
          <?php
            $rawImage = trim((string)($p['image_url'] ?? ''));
            $isRemoteImage = preg_match('#^https?://#i', $rawImage) === 1;
            $imageSrc = $isRemoteImage
              ? $rawImage
              : (BASE_URL . 'public/img/products/' . htmlspecialchars($rawImage !== '' ? $rawImage : 'placeholder.png'));
          ?>
          <img src="<?= $imageSrc ?>"
               alt="<?= htmlspecialchars($p['product_name']) ?>"
               onerror="this.onerror=null;this.src='https://placehold.co/600x600/f3f4f6/374151?text=No+Image';">
          <div class="product-actions-hover">
            <?php if (($p['stock_quantity'] ?? 0) > 0): ?>
              <button class="btn-quick-add" onclick="addToCart(<?= $p['product_id'] ?>)">
                🛒 Thêm vào giỏ
              </button>
            <?php else: ?>
              <button class="btn-quick-add" disabled style="background: #999; cursor: not-allowed; border-color: #999;">
                Hết hàng
              </button>
            <?php endif; ?>
          </div>
        </div>

        <?php if (($p['stock_quantity'] ?? 0) <= 0): ?>
          <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255,255,255,0.4); z-index: 5; pointer-events: none; display: flex; align-items: center; justify-content: center;">
             <span style="background: rgba(0,0,0,0.6); color: white; padding: 5px 15px; border-radius: 20px; font-weight: bold; transform: rotate(-15deg); border: 2px solid white;">HẾT HÀNG</span>
          </div>
        <?php endif; ?>

        <div class="product-info">
          <span class="product-brand"><?= htmlspecialchars($p['brand'] ?? 'N/A') ?></span>
          <h3 class="product-name">
            <a href="<?= BASE_URL ?>product/<?= $p['product_id'] ?>"><?= htmlspecialchars($p['product_name']) ?></a>
          </h3>
          <div class="product-rating">
            <span class="stars"><?= str_repeat('★', floor($p['rating'] ?? 4.5)) ?></span>
            <span class="review-count">(<?= number_format($p['reviews'] ?? 0) ?>)</span>
          </div>
          <div class="product-price-row">
            <span class="price-current"><?= number_format($p['current_price']) ?>đ</span>
            <?php if ($p['old_price'] && $p['old_price'] > $p['current_price']): ?>
              <span class="price-old"><?= number_format($p['old_price']) ?>đ</span>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Tab Mới nhất -->
    <div class="products-grid tab-content" id="tab-new" style="display: none;">
      <?php foreach ($newProducts as $p): 
        $discount = ($p['old_price'] && $p['old_price'] > $p['current_price']) 
                    ? round((1 - $p['current_price']/$p['old_price'])*100) : 0;
      ?>
      <div class="product-card" data-id="<?= $p['product_id'] ?>">
        <span class="product-badge badge-new">Mới</span>
        <?php if ($discount > 0): ?>
          <span class="product-discount">-<?= $discount ?>%</span>
        <?php endif; ?>

        <div class="product-img-wrap">
          <?php
            $rawImage = trim((string)($p['image_url'] ?? ''));
            $isRemoteImage = preg_match('#^https?://#i', $rawImage) === 1;
            $imageSrc = $isRemoteImage
              ? $rawImage
              : (BASE_URL . 'public/img/products/' . htmlspecialchars($rawImage !== '' ? $rawImage : 'placeholder.png'));
          ?>
          <img src="<?= $imageSrc ?>"
               alt="<?= htmlspecialchars($p['product_name']) ?>"
               onerror="this.onerror=null;this.src='https://placehold.co/600x600/f3f4f6/374151?text=No+Image';">
          <div class="product-actions-hover">
            <?php if (($p['stock_quantity'] ?? 0) > 0): ?>
              <button class="btn-quick-add" onclick="addToCart(<?= $p['product_id'] ?>)">
                🛒 Thêm vào giỏ
              </button>
            <?php else: ?>
              <button class="btn-quick-add" disabled style="background: #999; cursor: not-allowed; border-color: #999;">
                Hết hàng
              </button>
            <?php endif; ?>
          </div>
        </div>

        <?php if (($p['stock_quantity'] ?? 0) <= 0): ?>
          <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255,255,255,0.4); z-index: 5; pointer-events: none; display: flex; align-items: center; justify-content: center;">
             <span style="background: rgba(0,0,0,0.6); color: white; padding: 5px 15px; border-radius: 20px; font-weight: bold; transform: rotate(-15deg); border: 2px solid white;">HẾT HÀNG</span>
          </div>
        <?php endif; ?>

        <div class="product-info">
          <span class="product-brand"><?= htmlspecialchars($p['brand'] ?? 'N/A') ?></span>
          <h3 class="product-name">
            <a href="<?= BASE_URL ?>product/<?= $p['product_id'] ?>"><?= htmlspecialchars($p['product_name']) ?></a>
          </h3>
          <div class="product-rating">
            <span class="stars"><?= str_repeat('★', floor($p['rating'] ?? 4.5)) ?></span>
            <span class="review-count">(<?= number_format($p['reviews'] ?? 0) ?>)</span>
          </div>
          <div class="product-price-row">
            <span class="price-current"><?= number_format($p['current_price']) ?>đ</span>
            <?php if ($p['old_price'] && $p['old_price'] > $p['current_price']): ?>
              <span class="price-old"><?= number_format($p['old_price']) ?>đ</span>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<script>
// Chuyển đổi tab sản phẩm
function switchTab(tab) {
  // Ẩn tất cả tabs
  document.querySelectorAll('.tab-content').forEach(el => el.style.display = 'none');
  document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
  
  // Hiện tab được chọn
  document.getElementById('tab-' + tab).style.display = 'grid';
  document.querySelector('[data-tab="' + tab + '"]').classList.add('active');
}
</script>

<!-- ===== THƯƠNG HIỆU ===== -->
<section class="section brands-section">
  <div class="container">
    <div class="section-header">
      <h2 class="section-title">Thương hiệu uy tín</h2>
    </div>
    <div class="brands-track-wrap">
      <div class="brands-track" id="brandsTrack">
        <?php
        $brands = ['Blackmores','Omega','Eucerin','La Roche-Posay','GSK','Sanofi','Pfizer','Bayer','Abbott','Novartis','Nature\'s Way','Sandoz'];
        foreach ($brands as $brand):
        ?>
        <div class="brand-logo">
          <span><?= htmlspecialchars($brand) ?></span>
        </div>
        <?php endforeach; ?>
        <!-- Duplicate for infinite scroll -->
        <?php foreach ($brands as $brand): ?>
        <div class="brand-logo">
          <span><?= htmlspecialchars($brand) ?></span>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<!-- ===== BÀI VIẾT SỨC KHỎE ===== -->
<section class="section blog-section">
  <div class="container">
    <div class="section-header">
      <h2 class="section-title">Góc sức khỏe</h2>
      <a href="<?= BASE_URL ?>blog" class="see-all">Xem tất cả →</a>
    </div>
    <div class="blog-grid">

      <article class="blog-card blog-featured">
        <div class="blog-img" style="background:linear-gradient(135deg,#e8f5e9,#c8e6c9)">
          <span style="font-size:4rem">🩺</span>
        </div>
        <div class="blog-content">
          <span class="blog-tag">Sức khỏe tổng quát</span>
          <h3><a href="<?= BASE_URL ?>blog/1">10 thói quen buổi sáng giúp tăng cường miễn dịch cả ngày</a></h3>
          <p>Khởi đầu ngày mới với những thói quen đơn giản nhưng hiệu quả, được các chuyên gia y tế khuyến nghị...</p>
          <div class="blog-meta">
            <span>👨‍⚕️ Dược sĩ Nguyễn An</span>
            <span>15 thg 3, 2025</span>
          </div>
        </div>
      </article>

      <div class="blog-list">
        <?php
        $blogPosts = [
          ['tag'=>'Vitamin','title'=>'Bổ sung Vitamin D đúng cách — liều lượng và thời điểm vàng','date'=>'12 thg 3'],
          ['tag'=>'Dinh dưỡng','title'=>'Thực phẩm giàu Omega-3 tốt nhất cho não bộ và tim mạch','date'=>'10 thg 3'],
          ['tag'=>'Mẹ & Bé','title'=>'Lịch tiêm chủng 2025 — những mũi vaccine quan trọng cho trẻ','date'=>'8 thg 3'],
        ];
        foreach ($blogPosts as $i => $post):
        ?>
        <article class="blog-list-item">
          <div class="blog-list-num"><?= str_pad($i+1, 2, '0', STR_PAD_LEFT) ?></div>
          <div>
            <span class="blog-tag"><?= htmlspecialchars($post['tag']) ?></span>
            <h4><a href="<?= BASE_URL ?>blog/<?= $i+2 ?>"><?= htmlspecialchars($post['title']) ?></a></h4>
            <span class="blog-date"><?= $post['date'] ?></span>
          </div>
        </article>
        <?php endforeach; ?>
      </div>

    </div>
  </div>
</section>

<!-- ===== INLINE JS ===== -->
<script>
// Hero Slider
let currentSlide = 0;
const slides = document.querySelectorAll('.hero-slide');
const dots = document.querySelectorAll('.dot');

function showSlide(n) {
  if (!slides.length) return;
  slides[currentSlide].classList.remove('active');
  dots[currentSlide].classList.remove('active');
  currentSlide = (n + slides.length) % slides.length;
  slides[currentSlide].classList.add('active');
  dots[currentSlide].classList.add('active');
}
function changeSlide(dir) { showSlide(currentSlide + dir); }
function goSlide(n) { showSlide(n); }

// Auto-advance
if (slides.length) setInterval(() => changeSlide(1), 5000);

// Product Tabs
document.querySelectorAll('.tab-btn').forEach(btn => {
  btn.addEventListener('click', function() {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    this.classList.add('active');
  });
});
</script>

<?php include __DIR__ . '/layout/footer.php'; ?>