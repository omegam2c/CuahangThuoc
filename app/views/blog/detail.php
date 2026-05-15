<?php include __DIR__ . '/../layout/header.php'; ?>

<style>
    .article-container { max-width: 800px; margin: 50px auto; background: #fff; padding: 40px; border-radius: 30px; box-shadow: var(--shadow-sm); }
    .article-meta { display: flex; gap: 20px; color: #888; font-size: 0.9rem; margin-bottom: 25px; border-bottom: 1px solid #f0f0f0; padding-bottom: 20px; }
    .article-title { font-size: 2.5rem; font-weight: 800; line-height: 1.3; margin-bottom: 30px; color: #1a1a1a; }
    .article-image { width: 100%; height: 450px; background-size: cover; background-position: center; border-radius: 20px; margin-bottom: 40px; }
    .article-body { font-size: 1.15rem; line-height: 1.8; color: #333; }
    .article-body p { margin-bottom: 25px; }
    .article-sidebar { background: #f8fafc; padding: 25px; border-radius: 20px; margin-top: 50px; border-left: 5px solid var(--green); }
</style>

<div class="container section">
    <a href="<?= BASE_URL ?>blog" class="btn" style="border: 1px solid #333; color: #333; background: #fff; margin-bottom: 30px; font-weight: 600;">⬅️ Quay lại Góc sức khỏe</a>
    
    <article class="article-container animate-fade-up">
        <div class="article-meta">
            <span>👤 Bởi: <?= htmlspecialchars($blog['author']) ?></span>
            <span>📅 Cập nhật: <?= date('d/m/Y', strtotime($blog['created_at'])) ?></span>
        </div>
        
        <h1 class="article-title"><?= htmlspecialchars($blog['title']) ?></h1>
        
        <?php if ($blog['image_url']): ?>
            <div class="article-image" style="background-image: url('<?= $blog['image_url'] ?>')"></div>
        <?php endif; ?>
        
        <div class="article-body">
            <?= nl2br(htmlspecialchars($blog['content'])) ?>
        </div>
        
        <div class="article-sidebar">
            <h4 style="color: var(--green); margin-bottom: 10px;">Lời khuyên từ Dược sĩ</h4>
            <p style="margin: 0; font-style: italic;">Các bài viết tại Góc Sức Khỏe chỉ mang tính chất tham khảo. Nếu có triệu chứng bệnh, quý khách vui lòng đến trực tiếp nhà thuốc hoặc cơ sở y tế gần nhất để được tư vấn chính xác.</p>
        </div>
    </article>
</div>

<div style="margin-bottom: 80px;"></div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
