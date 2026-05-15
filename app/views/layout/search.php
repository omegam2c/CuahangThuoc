<?php $this->layout("layouts/default", ["title" => APPNAME]) ?>

<?php $this->start("page") ?>

<div class="container-fluid bg-light py-3 border-bottom mb-0">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="home" class="text-decoration-none text-primary">Trang chủ</a></li>
                <li class="breadcrumb-item active" aria-current="page">Kết quả tìm kiếm</li>
            </ol>
        </nav>
    </div>
</div>

<section class="py-5">
    <div class="container">

        <!-- Tiêu đề kết quả -->
        <div class="py-3 mx-auto mb-4 text-center bg-info bg-opacity-10 rounded-3">
            <?php
            $keyword = isset($_POST['search']) ? $_POST['search'] : (isset($_GET['search']) ? $_GET['search'] : '');
            $count = isset($result) ? count($result) : 0;
            ?>
            <h3 class="fs-4 m-0">
                🔍 Kết quả tìm kiếm cho <span class="text-primary">"<?= htmlspecialchars($keyword) ?>"</span>
            </h3>
            <p class="text-muted mb-0 mt-1">Tìm thấy <strong><?= $count ?></strong> sản phẩm</p>
        </div>

        <?php if ($count === 0): ?>
            <!-- Không tìm thấy -->
            <div class="text-center py-5">
                <div style="font-size: 80px;">📭</div>
                <h4 class="mt-3 text-muted">Không tìm thấy sản phẩm nào</h4>
                <p class="text-muted">Vui lòng thử từ khóa khác hoặc khám phá thêm sản phẩm bên dưới.</p>
                <a href="product_all" class="btn btn-primary mt-2">Xem tất cả sản phẩm</a>
            </div>
        <?php else: ?>
            <!-- Lưới sản phẩm -->
            <div class="row g-3" id="search-results">
                <?php foreach ($result as $rs): ?>
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="card h-100 rounded-3 shadow-sm border-0 product-card position-relative">

                            <?php if ($rs->khuyen_mai > 0): ?>
                                <div class="badge position-absolute top-0 start-0 m-2">
                                    -<?= $rs->khuyen_mai ?>%
                                </div>
                            <?php endif; ?>

                            <a href="detail?masp=<?= $rs->ma_sach ?>">
                                <img class="card-img-top rounded-top-3"
                                     src="img/product/<?= $rs->hinh_anh ?>"
                                     alt="<?= htmlspecialchars($rs->ten_sach) ?>"
                                     style="height: 220px; object-fit: cover;">
                            </a>

                            <div class="card-body d-flex flex-column">
                                <h6 class="card-title mb-2" style="min-height: 42px;">
                                    <a style="color:#111111; text-decoration:none;" href="detail?masp=<?= $rs->ma_sach ?>">
                                        <?php
                                        if (mb_strlen($rs->ten_sach) > 50) {
                                            echo mb_substr($rs->ten_sach, 0, 47) . '...';
                                        } else {
                                            echo htmlspecialchars($rs->ten_sach);
                                        }
                                        ?>
                                    </a>
                                </h6>

                                <div class="mt-auto">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <span class="fw-bold text-danger fs-6">
                                            <?= number_format($rs->gia_sach * (100 - $rs->khuyen_mai) / 100, 0, '.', ',') ?>đ
                                        </span>
                                        <?php if ($rs->khuyen_mai > 0): ?>
                                            <span class="text-muted text-decoration-line-through small">
                                                <?= number_format($rs->gia_sach, 0, '.', ',') ?>đ
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <p class="text-muted small mb-2">
                                        <i class="fas fa-shopping-bag me-1"></i> Đã bán: <?= $rs->sold ?>
                                    </p>

                                    <form action="addCart" method="POST">
                                        <input type="hidden" value="1" name="so-luong">
                                        <input type="hidden" name="masp" value="<?= $rs->ma_sach ?>">
                                        <div class="d-grid">
                                            <button class="btn btn-danger btn-sm">
                                                👜 Chọn mua
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Gợi ý danh mục -->
        <div class="mt-5 pt-4 border-top">
            <div class="py-3 mx-auto mb-3 text-center bg-info bg-opacity-10 rounded-3">
                <h5 class="m-0">🔖 Khám phá thêm theo danh mục</h5>
            </div>
            <form action="product" method="POST">
                <div class="row justify-content-center g-3 text-center">
                    <div class="col-4 col-md-2">
                        <button name="sale" class="btn btn-link p-0 m-0 d-flex flex-column align-items-center w-100 text-decoration-none">
                            <img src="img/khuyenmai.png" width="55px" alt="">
                            <span class="mt-1 text-dark small">Khuyến mãi</span>
                        </button>
                    </div>
                    <div class="col-4 col-md-2">
                        <button name="all" class="btn btn-link p-0 m-0 d-flex flex-column align-items-center w-100 text-decoration-none">
                            <img src="img/newproduct.png" width="55px" alt="">
                            <span class="mt-1 text-dark small">Sản Phẩm Mới</span>
                        </button>
                    </div>
                    <div class="col-4 col-md-2">
                        <button name="sgk" class="btn btn-link p-0 m-0 d-flex flex-column align-items-center w-100 text-decoration-none">
                            <img src="img/sgk.png" width="55px" alt="">
                            <span class="mt-1 text-dark small">Sách Giáo Dục</span>
                        </button>
                    </div>
                    <div class="col-4 col-md-2">
                        <button name="truyentranh" class="btn btn-link p-0 m-0 d-flex flex-column align-items-center w-100 text-decoration-none">
                            <img src="img/comic.png" width="55px" alt="">
                            <span class="mt-1 text-dark small">Truyện Tranh</span>
                        </button>
                    </div>
                    <div class="col-4 col-md-2">
                        <button name="kynang" class="btn btn-link p-0 m-0 d-flex flex-column align-items-center w-100 text-decoration-none">
                            <img src="img/kynang.png" width="55px" alt="">
                            <span class="mt-1 text-dark small">Kỹ Năng Sống</span>
                        </button>
                    </div>
                    <div class="col-4 col-md-2">
                        <button name="tieuthuyet" class="btn btn-link p-0 m-0 d-flex flex-column align-items-center w-100 text-decoration-none">
                            <img src="img/vanhoc.png" width="55px" alt="">
                            <span class="mt-1 text-dark small">Tiểu Thuyết</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

    </div>
</section>

<div class="container-fluid p-0 m-0 text-center">
    <img class="w-100" src="img/bg-footer.png" alt="">
</div>

<style>
.product-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.product-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.12) !important;
}
.badge {
    background-color: #ec4276;
    color: white;
    font-size: 0.75rem;
    padding: 4px 8px;
    border-radius: 6px;
    z-index: 2;
}
</style>

<?php $this->stop() ?>