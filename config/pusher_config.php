<?php
// config/pusher_config.php
require_once __DIR__ . '/../vendor/autoload.php';

// Self-contained env loader — ensures env() is available when called directly as an API endpoint
if (!function_exists('env')) {
    require_once __DIR__ . '/../app/Helpers/functions.php';
}
if (!getenv('PUSHER_APP_KEY')) {
    if (function_exists('loadEnv')) {
        loadEnv(__DIR__ . '/../.env');
    }
}

// Thay thế các cấu hình dưới đây bằng thông tin từ Pusher Dashboard (pusher.com)
define('PUSHER_APP_ID',      env('PUSHER_APP_ID',      ''));
define('PUSHER_APP_KEY',     env('PUSHER_APP_KEY',     ''));
define('PUSHER_APP_SECRET',  env('PUSHER_APP_SECRET',  ''));
define('PUSHER_APP_CLUSTER', env('PUSHER_APP_CLUSTER', 'ap1'));

/**
 * Trả về instance của Pusher để sử dụng ở các file khác
 */
function getPusher() {
    $options = array(
        'cluster' => PUSHER_APP_CLUSTER,
        'useTLS' => true
    );
    
    // Bỏ qua SSL (Chỉ dùng cho Localhost/XAMPP)
    $client = new \GuzzleHttp\Client([
        'verify' => false,
    ]);

    return new Pusher\Pusher(
        PUSHER_APP_KEY,
        PUSHER_APP_SECRET,
        PUSHER_APP_ID,
        $options,
        $client
    );
}
?>
