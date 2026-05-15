<?php include __DIR__ . '/../layout/header.php'; ?>

<style>
    .blog-header { background: linear-gradient(135deg, var(--green) 0%, #00b894 100%); color: white; padding: 60px 0; border-radius: 0 0 50px 50px; text-align: center; margin-bottom: 50px; }
    .blog-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 30px; }
    .blog-card { background: #fff; border-radius: 20px; overflow: hidden; box-shadow: var(--shadow-sm); transition: all 0.3s; border: 1px solid #eee; display: flex; flex-direction: column; }
    .blog-card:hover { transform: translateY(-10px); box-shadow: var(--shadow-lg); border-color: var(--green-light); }
    .blog-image { height: 220px; background-size: cover; background-position: center; }
    .blog-content { padding: 25px; flex-grow: 1; display: flex; flex-direction: column; }
    .blog-category { color: var(--green); font-size: 0.8rem; font-weight: 700; text-transform: uppercase; margin-bottom: 10px; }
    .blog-title { font-size: 1.25rem; font-weight: 700; margin-bottom: 15px; line-height: 1.4; color: #1a1a1a; }
    .blog-summary { color: #666; font-size: 0.95rem; margin-bottom: 20px; line-height: 1.6; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
    .blog-footer { display: flex; justify-content: space-between; align-items: center; padding-top: 15px; border-top: 1px solid #f5f5f5; margin-top: auto; }
</style>

<div class="blog-header animate-fade-down">
    <div class="container">
        <h1 style="font-size: 3rem; font-weight: 800; margin-bottom: 10px;">Góc Sức Khỏe</h1>
        <p style="font-size: 1.1rem; opacity: 0.9;">Kiến thức y khoa tin cậy từ các dược sĩ hàng đầu</p>
    </div>
</div>

<div class="container section">
    <div class="blog-grid">
        <?php if (empty($blogs)): ?>
            <div style="grid-column: span 3; text-align: center; padding: 100px;">
                <p style="font-size: 1.2rem; color: #888;">Chưa có bài viết nào trong mục này.</p>
            </div>
        <?php else: ?>
            <?php foreach ($blogs as $b): ?>
                <article class="blog-card animate-fade-up">
                    <?php 
                        $img = $b['image_url'] ?: 'https://via.placeholder.com/600x400?text=Health+Tips';
                    ?>
                    <div class="blog-image" style="background-image: url('<?= $img ?>')"></div>
                    <div class="blog-content">
                        <span class="blog-category">Kiến thức y khoa</span>
                        <h2 class="blog-title"><?= htmlspecialchars($b['title']) ?></h2>
                        <p class="blog-summary"><?= htmlspecialchars($b['summary']) ?></p>
                        <div class="blog-footer">
                            <span style="font-size: 0.85rem; color: #888;">📅 <?= date('d/m/Y', strtotime($b['created_at'])) ?></span>
                            <a href="<?= BASE_URL ?>blog/detail?id=<?= $b['blog_id'] ?>" class="btn btn-premium" style="padding: 8px 20px; font-size: 0.85rem;">Đọc tiếp</a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<div style="margin-bottom: 80px;"></div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
