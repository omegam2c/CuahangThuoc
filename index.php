<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');
session_start();
ob_start();

// Load Helpers & .env (Ref point 🔄)
require_once __DIR__ . '/app/Helpers/functions.php';
loadEnv(__DIR__ . '/.env');

// Định tuyến tĩnh cho built-in server (Xử lý các file CSS, JS, Image)
if (php_sapi_name() === 'cli-server') {
    $path = realpath(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    if ($path && is_file($path)) {
        return false;
    }
}

require_once('config/app_config.php');
require_once('config/constants.php');
require_once('config/database.php');
require_once __DIR__ . '/vendor/autoload.php';
if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
    die("Autoloader failed to load PHPMailer");
}

// Lấy URL hiện tại và xử lý subdirectory
$scriptName = $_SERVER['SCRIPT_NAME']; // e.g. /Pharmacy/index.php
$requestUri = $_SERVER['REQUEST_URI']; // e.g. /Pharmacy/index.php?url=...

// Loại bỏ query string
$path = parse_url($requestUri, PHP_URL_PATH);

// Loại bỏ phần folder gốc (e.g. /Pharmacy/)
$basePath = dirname($scriptName);
if ($basePath === DIRECTORY_SEPARATOR || $basePath === '.') $basePath = '';
$url = substr($path, strlen($basePath));
$url = trim($url, '/');

// Nếu truy cập trực tiếp file index.php
if ($url === 'index.php' || $url === '') {
    $url = isset($_GET['url']) ? trim($_GET['url'], '/') : 'home';
}

// Phân tích URL
$urlParts = explode('/', $url);

$controllerName = $urlParts[0];
$actionName = isset($urlParts[1]) ? $urlParts[1] : 'index';

// Xử lý routing cơ bản
if ($controllerName === 'home' || $controllerName === '') {
    require_once 'app/controllers/HomeController.php';
    $controller = new HomeController();
    $controller->index();
} elseif ($controllerName === 'auth') {
    require_once 'app/controllers/AuthController.php';
    $controller = new AuthController();

    // Gọi phương thức nếu tồn tại trong AuthController
    if (method_exists($controller, $actionName)) {
        $controller->$actionName();
    } else {
        echo "404 - Phương thức xác thực không tồn tại!";
    }
} else {
    // Các route khác
    switch ($controllerName) {
        case 'products':
            require_once 'app/controllers/ProductController.php';
            $controller = new ProductController();
            $controller->$actionName();
            break;
        case 'product':
            require_once 'app/controllers/ProductController.php';
            $controller = new ProductController();
            $_GET['id'] = $actionName; // product/123
            $controller->detail();
            break;
        case 'cart':
            require_once 'app/controllers/CartController.php';
            $controller = new CartController();
            $controller->$actionName();
            break;
        case 'order':
            require_once 'app/controllers/OrderController.php';
            $controller = new OrderController();
            $controller->$actionName();
            break;
        case 'webhook':
            require_once 'app/controllers/WebhookController.php';
            $controller = new WebhookController();
            if (method_exists($controller, $actionName)) {
                $controller->$actionName();
            } else {
                echo "404 - Webhook action not found!";
            }
            break;
        case 'prescription':
            require_once 'app/controllers/PrescriptionController.php';
            $controller = new PrescriptionController();
            if (method_exists($controller, $actionName)) {
                $controller->$actionName();
            } else {
                $controller->index();
            }
            break;
        case 'account':
            require_once 'app/controllers/AccountController.php';
            $controller = new AccountController();
            if (method_exists($controller, $actionName)) {
                $controller->$actionName();
            } else {
                $controller->index();
            }
            break;
        case 'track':
            require_once 'app/controllers/TrackController.php';
            $controller = new TrackController();
            if (method_exists($controller, $actionName)) {
                $controller->$actionName();
            }
            break;
        case 'admin':
            $subController = isset($urlParts[1]) ? $urlParts[1] : 'dashboard';
            $subAction = isset($urlParts[2]) ? $urlParts[2] : 'index';

            if ($subController === 'dashboard') {
                require_once 'app/controllers/admin/AdminDashboardController.php';
                $controller = new AdminDashboardController();
            } elseif ($subController === 'chatbot') {
                require_once 'app/controllers/admin/AdminChatbotController.php';
                $controller = new AdminChatbotController();
            } elseif ($subController === 'inventory') {
                require_once 'app/controllers/admin/AdminInventoryController.php';
                $controller = new AdminInventoryController();
            } elseif ($subController === 'product') {
                require_once 'app/controllers/admin/AdminProductController.php';
                $controller = new AdminProductController();
            } elseif ($subController === 'order') {
                require_once 'app/controllers/admin/AdminOrderController.php';
                $controller = new AdminOrderController();
            } elseif ($subController === 'emails') {
                require_once 'app/controllers/admin/AdminEmailController.php';
                $controller = new AdminEmailController();
            } elseif ($subController === 'users') {
                require_once 'app/controllers/admin/AdminUserController.php';
                $controller = new AdminUserController();
            } elseif ($subController === 'general') {
                require_once 'app/controllers/admin/AdminGeneralController.php';
                $controller = new AdminGeneralController();
            } elseif ($subController === 'settings') {
                require_once 'app/controllers/admin/AdminSettingsController.php';
                $controller = new AdminSettingsController();
            } elseif ($subController === 'log') {
                require_once 'app/controllers/admin/AdminLogController.php';
                $controller = new AdminLogController();
            } else {
                echo "404 - Admin module not found!";
                exit();
            }

            if (method_exists($controller, $subAction)) {
                $controller->$subAction();
            } else {
                echo "404 - Admin action not found!";
            }
            break;
        case 'doctor':
            require_once 'app/controllers/PharmacistController.php';
            $controller = new PharmacistController();
            $method = $actionName;
            if ($actionName == 'prescriptions') {
                $method = 'pendingPrescriptions';
            }


            if (method_exists($controller, $method)) {
                $controller->$method();
            } else {
                echo "404 - Pharmacist action not found! (Method: $method)";
            }
            break;
        case 'blog':
            require_once 'app/controllers/BlogController.php';
            $controller = new BlogController();
            $method = ($actionName == 'index' || $actionName == '') ? 'index' : $actionName;
            if (method_exists($controller, $method)) {
                $controller->$method();
            } else {
                $controller->index();
            }
            break;
        case 'about':
        case 'stores':
        case 'careers':
        case 'franchise':
        case 'news':
        case 'help':
        case 'privacy':
        case 'terms':
        case 'cookie':
            require_once 'app/controllers/PageController.php';
            $controller = new PageController();
            $controller->show($controllerName, $actionName);
            break;
        case 'consult':
            require_once 'public/chat.php';
            break;
        default:
            echo "404 - Trang không tồn tại!";
            break;
    }
}

ob_end_flush();
