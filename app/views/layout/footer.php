</main><!-- end #pageMain -->

<!-- ===== FOOTER ===== -->
<footer class="main-footer">
  <div class="footer-top">
    <div class="container footer-grid">

      <div class="footer-col footer-brand">
        <a href="<?= BASE_URL ?>" class="footer-logo">
          <svg viewBox="0 0 36 36" fill="none" width="36" height="36">
            <rect width="36" height="36" rx="8" fill="#00904a"/>
            <path d="M18 8v20M8 18h20" stroke="#fff" stroke-width="4" stroke-linecap="round"/>
          </svg>
          <strong>Nhà thuốc 1985</strong>
        </a>
        <p>Nhà thuốc trực tuyến uy tín — 10,000+ sản phẩm chính hãng, giao hàng nhanh 2 giờ, tư vấn dược sĩ 24/7.</p>
        <div class="footer-cert">
          <span class="cert-badge">✓ Bộ Y tế cấp phép</span>
          <span class="cert-badge">✓ Đã đăng ký ĐKKD</span>
        </div>
        <div class="social-links">
          <a href="#" class="social-btn fb" aria-label="Facebook">f</a>
          <a href="#" class="social-btn yt" aria-label="YouTube">▶</a>
          <a href="#" class="social-btn zl" aria-label="Zalo">Z</a>
          <a href="#" class="social-btn ig" aria-label="Instagram">◎</a>
        </div>
      </div>

      <div class="footer-col">
        <h4>Về Nhà thuốc 1985</h4>
        <ul>
          <li><a href="<?= BASE_URL ?>about">Giới thiệu</a></li>
          <li><a href="<?= BASE_URL ?>careers">Tuyển dụng</a></li>
          <li><a href="<?= BASE_URL ?>stores">Hệ thống cửa hàng</a></li>
          <li><a href="<?= BASE_URL ?>franchise">Nhượng quyền</a></li>
          <li><a href="<?= BASE_URL ?>news">Tin tức</a></li>
        </ul>
      </div>

      <div class="footer-col">
        <h4>Hỗ trợ khách hàng</h4>
        <ul>
          <li><a href="<?= BASE_URL ?>help/faq">Câu hỏi thường gặp</a></li>
          <li><a href="<?= BASE_URL ?>help/shipping">Chính sách giao hàng</a></li>
          <li><a href="<?= BASE_URL ?>help/return">Chính sách đổi trả</a></li>
          <li><a href="<?= BASE_URL ?>help/payment">Phương thức thanh toán</a></li>
          <li><a href="<?= BASE_URL ?>prescription">Hướng dẫn mua thuốc kê đơn</a></li>
          <li><a href="<?= BASE_URL ?>consult">Tư vấn dược sĩ</a></li>
        </ul>
      </div>

      <div class="footer-col footer-contact">
        <h4>Liên hệ</h4>
        <div class="contact-item">
          <span>📞</span>
          <div>
            <strong>Hotline: 1800 599 921</strong>
            <small>Miễn phí — 8:00–22:00 mỗi ngày</small>
          </div>
        </div>
        <div class="contact-item">
          <span>✉️</span>
          <div>
            <a href="mailto:cskh@nhathuoc1985.vn">cskh@nhathuoc1985.vn</a>
          </div>
        </div>
        <div class="contact-item">
          <span>📍</span>
          <div>
            <span>123 Nguyễn Thị Minh Khai, Q.1, TP.HCM</span>
          </div>
        </div>
        <div class="footer-app">
          <p>Tải ứng dụng:</p>
          <div class="app-badges">
            <a href="#" class="app-badge">
              <span>🍎</span> App Store
            </a>
            <a href="#" class="app-badge">
              <span>▶</span> Google Play
            </a>
          </div>
        </div>
      </div>

    </div>
  </div>

  <div class="footer-bottom">
    <div class="container footer-bottom-inner">
      <p>© 2025 Nhà thuốc 1985. Giấy phép kinh doanh số: 0312345678 do Sở KH&ĐT TP.HCM cấp.</p>
      <div class="footer-legal">
        <a href="<?= BASE_URL ?>privacy">Bảo mật thông tin</a>
        <a href="<?= BASE_URL ?>terms">Điều khoản sử dụng</a>
        <a href="<?= BASE_URL ?>cookie">Chính sách Cookie</a>
      </div>
    </div>
  </div>
</footer>

<!-- ===== BACK TO TOP ===== -->
<button class="back-to-top" id="backToTop" aria-label="Về đầu trang">↑</button>

<!-- ===== CHAT BUTTON ===== -->
<a href="<?= BASE_URL ?>consult" class="chat-float" title="Chat với dược sĩ">
  <span class="chat-icon">💬</span>
  <span class="chat-label">Tư vấn</span>
</a>

<!-- ===== TOAST NOTIFICATION ===== -->
<div class="toast" id="cartToast">
  <span class="toast-icon">✅</span>
  <span id="toastMsg">Đã thêm vào giỏ hàng!</span>
</div>

<script src="<?= BASE_URL ?>public/js/main.js"></script>
<?php if (isset($extraJs)): foreach ($extraJs as $js): ?>
  <script src="<?= htmlspecialchars($js) ?>"></script>
<?php endforeach; endif; ?>

<!-- SweetAlert2 cho UI mượt mà trên toàn trang -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        <?php if (!empty($_SESSION['success_message'])): ?>
            Swal.fire({
                icon: 'success',
                title: 'Thành công',
                text: '<?= addslashes(htmlspecialchars($_SESSION['success_message'])) ?>',
                timer: 2500,
                showConfirmButton: false
            });
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['error_message'])): ?>
            Swal.fire({
                icon: 'error',
                title: 'Có lỗi xảy ra',
                text: '<?= addslashes(htmlspecialchars($_SESSION['error_message'])) ?>',
                confirmButtonText: 'Đóng'
            });
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
    });

    // Global Add to Cart function
    function addToCart(productId, quantity = 1) {
        fetch('<?= BASE_URL ?>cart/addAjax', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-Token': window.csrfToken
            },
            body: JSON.stringify({ product_id: productId, quantity: parseInt(quantity) })
        })
        .then(r => r.json())
        .then(data => {
            const payload = data.payload || data;
            if(data.success) {
                showToast(payload.message || data.message || 'Đã thêm vào giỏ hàng!');
                // Update all cart count elements
                document.querySelectorAll('.cart-count').forEach(el => {
                    const count = parseInt(payload.cart_count) || 0;
                    el.textContent = count;
                    el.style.display = count > 0 ? 'flex' : 'none';
                });
                // Update total price
                const cartTotalElem = document.getElementById('cartTotal');
                if(cartTotalElem && payload.cart_total) cartTotalElem.textContent = payload.cart_total;
            } else {
                showToast(data.message || payload.message || 'Có lỗi xảy ra!', 'error');
            }
        })
        .catch(() => showToast('Có lỗi xảy ra, vui lòng thử lại!', 'error'));
    }

    function showToast(msg, type = 'success') {
        const t = document.getElementById('cartToast');
        if (!t) return;
        const icon = t.querySelector('.toast-icon');
        if (icon) icon.textContent = type === 'success' ? '✅' : '❌';
        const msgElem = document.getElementById('toastMsg');
        if (msgElem) msgElem.textContent = msg;
        t.classList.add('show');
        setTimeout(() => t.classList.remove('show'), 3000);
    }
</script>

<script>
// Search suggestions (debounce)
const searchInput = document.getElementById('globalSearch');
const suggestions = document.getElementById('searchSuggestions');
let searchTimer;
if (searchInput) {
  searchInput.addEventListener('input', function() {
    clearTimeout(searchTimer);
    const q = this.value.trim();
    if (q.length < 2) { suggestions.innerHTML = ''; suggestions.style.display = 'none'; return; }
    searchTimer = setTimeout(() => {
      fetch('<?= BASE_URL ?>products/search-suggest?q=' + encodeURIComponent(q))
        .then(r => r.json())
        .then(items => {
          if (!items.length) { suggestions.style.display = 'none'; return; }
          suggestions.innerHTML = items.map(i =>
            `<a href="<?= BASE_URL ?>product/${i.id}" class="suggestion-item">
              <span class="sug-name">${i.name}</span>
              <span class="sug-price">${i.price}</span>
            </a>`
          ).join('');
          suggestions.style.display = 'block';
        }).catch(() => {});
    }, 280);
  });
  document.addEventListener('click', e => {
    if (!searchInput.contains(e.target)) { suggestions.style.display = 'none'; }
  });
}

// Account dropdown
const accDrop = document.getElementById('accountDropdown');
if (accDrop) {
  accDrop.addEventListener('mouseenter', () => accDrop.classList.add('open'));
  accDrop.addEventListener('mouseleave', () => accDrop.classList.remove('open'));
}

// Sticky header
const header = document.getElementById('mainHeader');
window.addEventListener('scroll', () => {
  header.classList.toggle('sticky', window.scrollY > 80);
});

// Back to top
const btt = document.getElementById('backToTop');
window.addEventListener('scroll', () => btt.classList.toggle('visible', window.scrollY > 400));
btt.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));

// Mega menu
const megaTrigger = document.getElementById('megaTrigger');
const megaMenu = document.getElementById('megaMenu');
if (megaTrigger) {
  megaTrigger.addEventListener('mouseenter', () => megaMenu.classList.add('open'));
  megaTrigger.addEventListener('mouseleave', () => megaMenu.classList.remove('open'));
}
</script>

<?php 
// Include doctor alert for pharmacists on all pages using footer
include __DIR__ . '/doctor_alert.php'; 
?>
</body>
</html>