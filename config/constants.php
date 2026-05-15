<?php
if (php_sapi_name() === 'cli-server') {
    define('BASE_URL', '/');
    define('_HOST_URL', '/');
} else {
    // If running from XAMPP or similar with the /Pharmacy/ folder
    define('BASE_URL', '/Pharmacy/');
    define('_HOST_URL', '/Pharmacy/');
}

// Absolute URL for Emails/External links
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
define('SITE_URL', $protocol . '://' . $host . BASE_URL);
