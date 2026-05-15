<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Hệ thống Xác thực' ?></title>
    <!-- Thêm font Inter hiện đại -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/auth.css">
</head>
<body class="auth-page">
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-header">
                <!-- Chỗ này có thể để Logo -->
                <a href="<?= BASE_URL ?>"><img src="<?= BASE_URL ?>public/img/logo.jpg" alt="Logo" style="height: 100px; margin-bottom: 20px; object-fit: contain;"></a>
                <h1><?= explode(' —', $pageTitle ?? 'Đăng nhập')[0] ?></h1>
                <p>Hệ thống Nhà thuốc 1985</p>
            </div>

            <!-- Nơi hiển thị thông báo dùng chung (đã chuyển sang SweetAlert2 trong footer) -->
