<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="container animate-fade-up" style="padding: 60px 20px; min-height: 60vh;">
    <div style="max-width: 900px; margin: 0 auto;">
        <h1 style="color: var(--primary); font-weight: 800; font-size: 2.5rem; margin-bottom: 10px;">
            <?= htmlspecialchars($pageName) ?>
        </h1>
        <div style="width: 60px; height: 5px; background: var(--green); border-radius: 10px; margin-bottom: 40px;"></div>

        <div style="background: white; padding: 50px; border-radius: var(--radius-lg); box-shadow: 0 15px 40px rgba(0,0,0,0.05); line-height: 1.8; color: #334155; font-size: 1.1rem;">
            
            <?php if ($page === 'about'): ?>
                <div class="page-content">
                    <p style="font-size: 1.3rem; color: var(--primary); font-weight: 600; margin-bottom: 25px;">Hành trình phụng sự sức khỏe cộng đồng từ năm 1985.</p>
                    <p>Nhà thuốc 1985 được thành lập với mục tiêu duy nhất: <strong>"Mang lại sức khỏe toàn diện bằng tâm huyết và chuyên môn."</strong> Qua hơn 40 năm hình thành và phát triển, chúng tôi tự hào là một trong những hệ thống dược phẩm uy tín hàng đầu tại Việt Nam.</p>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin: 40px 0;">
                        <div style="background: #f8fafc; padding: 25px; border-radius: 15px; border-left: 4px solid var(--green);">
                            <h3 style="color: var(--primary); margin-top: 0;">Sứ mệnh</h3>
                            <p style="font-size: 0.95rem; margin-bottom: 0;">Cung cấp thuốc chính hãng và dịch vụ tư vấn chuyên sâu, giúp mọi gia đình an tâm chăm sóc sức khỏe mỗi ngày.</p>
                        </div>
                        <div style="background: #f8fafc; padding: 25px; border-radius: 15px; border-left: 4px solid var(--primary);">
                            <h3 style="color: var(--primary); margin-top: 0;">Tầm nhìn</h3>
                            <p style="font-size: 0.95rem; margin-bottom: 0;">Trở thành hệ thống nhà thuốc thông minh hàng đầu, kết hợp giữa y tế truyền thống và công nghệ AI hiện đại.</p>
                        </div>
                    </div>

                    <p>Đội ngũ của chúng tôi bao gồm các dược sĩ đại học với nhiều năm kinh nghiệm, luôn sẵn sàng lắng nghe và đưa ra những lời khuyên chuẩn xác nhất cho bạn.</p>
                </div>

            <?php elseif ($page === 'stores'): ?>
                <div class="page-content">
                    <p>Nhà thuốc 1985 hiện đang có mặt tại các vị trí đắc địa tại TP. Hồ Chí Minh và Hà Nội, luôn sẵn sàng phục vụ bạn 24/7.</p>
                    
                    <div style="margin: 30px 0;">
                        <div style="margin-bottom: 25px; padding-bottom: 20px; border-bottom: 1px dashed #e2e8f0;">
                            <h3 style="color: var(--primary); margin-bottom: 5px;">📍 Trụ sở chính (TP.HCM)</h3>
                            <p style="margin: 0; color: #64748b;">123 Cách Mạng Tháng Tám, Quận 1, TP. Hồ Chí Minh</p>
                            <p style="margin: 0; font-weight: 600; color: var(--green);">Mở cửa: 06:00 - 23:00</p>
                        </div>
                        <div style="margin-bottom: 25px; padding-bottom: 20px; border-bottom: 1px dashed #e2e8f0;">
                            <h3 style="color: var(--primary); margin-bottom: 5px;">📍 Chi nhánh Hà Nội</h3>
                            <p style="margin: 0; color: #64748b;">456 Cầu Giấy, Quận Cầu Giấy, Hà Nội</p>
                            <p style="margin: 0; font-weight: 600; color: var(--green);">Mở cửa: 24/7</p>
                        </div>
                    </div>

                    <div style="background: #fff9db; padding: 20px; border-radius: 12px; display: flex; align-items: center; gap: 15px;">
                        <span style="font-size: 2rem;">📞</span>
                        <p style="margin: 0;"><strong>Hotline hỗ trợ nhanh:</strong> 1800 599 921 (Miễn phí cuộc gọi)</p>
                    </div>
                </div>

            <?php elseif ($page === 'careers'): ?>
                <div class="page-content">
                    <p>Chúng tôi luôn chào đón những nhân tài đam mê ngành dược và có tinh thần phụng sự khách hàng gia nhập đội ngũ Nhà thuốc 1985.</p>
                    
                    <h3 style="margin-top: 35px; color: var(--primary);">Vị trí đang tuyển dụng:</h3>
                    <ul style="padding-left: 20px;">
                        <li><strong>Dược sĩ tư vấn (Full-time/Part-time):</strong> Yêu cầu bằng tốt nghiệp Trung cấp/Cao đẳng/Đại học Dược.</li>
                        <li><strong>Nhân viên kho dược:</strong> Am hiểu về quản lý thuốc và quy trình GSP.</li>
                        <li><strong>Quản trị viên hệ thống:</strong> Vận hành nền tảng thương mại điện tử dược phẩm.</li>
                    </ul>

                    <div style="margin-top: 40px; text-align: center; background: #f0fdf4; padding: 30px; border-radius: 20px;">
                        <p style="margin-bottom: 15px;">Gửi CV của bạn về email:</p>
                        <a href="mailto:tuyendung@nhathuoc1985.com" style="font-size: 1.5rem; font-weight: 800; color: var(--green); text-decoration: none;">tuyendung@nhathuoc1985.com</a>
                    </div>
                </div>

            <?php elseif ($page === 'help'): ?>
                <div class="page-content">
                    <p>Bạn cần hỗ trợ? Chúng tôi luôn sẵn lòng giúp bạn với các vấn đề liên quan đến đơn hàng, sản phẩm và tư vấn sức khỏe.</p>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 30px;">
                        <a href="<?= BASE_URL ?>consult" style="display: block; padding: 25px; background: white; border: 1px solid #e2e8f0; border-radius: 15px; text-decoration: none; transition: all 0.2s; text-align: center;">
                            <span style="font-size: 2rem; display: block; margin-bottom: 10px;">💬</span>
                            <strong style="color: var(--primary);">Chat với Dược sĩ</strong>
                            <p style="font-size: 0.85rem; color: #64748b; margin-top: 5px;">Tư vấn trực tiếp 24/7</p>
                        </a>
                        <a href="#" style="display: block; padding: 25px; background: white; border: 1px solid #e2e8f0; border-radius: 15px; text-decoration: none; transition: all 0.2s; text-align: center;">
                            <span style="font-size: 2rem; display: block; margin-bottom: 10px;">📦</span>
                            <strong style="color: var(--primary);">Theo dõi đơn hàng</strong>
                            <p style="font-size: 0.85rem; color: #64748b; margin-top: 5px;">Tra cứu hành trình vận chuyển</p>
                        </a>
                    </div>
                </div>

            <?php else: ?>
                <div class="page-content">
                    <p>Cảm ơn bạn đã quan tâm đến thông tin <strong><?= htmlspecialchars($pageName) ?></strong> của Nhà thuốc 1985.</p>
                    <p>Chúng tôi cam kết minh bạch và tuân thủ các quy định pháp luật hiện hành để bảo vệ quyền lợi cao nhất cho khách hàng khi mua sắm tại hệ thống.</p>
                    <p>Mọi chi tiết thắc mắc về chính sách, vui lòng liên hệ bộ phận CSKH để được giải đáp chi tiết.</p>
                </div>
            <?php endif; ?>

            <div style="margin-top: 40px; padding-top: 30px; border-top: 1px solid #f1f5f9;">
                <a href="<?= BASE_URL ?>" class="btn btn-premium" style="display: inline-flex; align-items: center; gap: 10px; padding: 12px 25px;">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Quay lại Trang Chủ
                </a>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
