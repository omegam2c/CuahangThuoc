<?php

// Self-contained env loader — ensures env() is always available
// regardless of which entry point calls this file
if (!function_exists('env')) {
    require_once __DIR__ . '/../app/Helpers/functions.php';
}

// Load .env if not already loaded
if (!getenv('DB_HOST')) {
    if (function_exists('loadEnv')) {
        loadEnv(__DIR__ . '/../.env');
    }
}

define('DB_HOST', env('DB_HOST', 'localhost'));
define('DB_USER', env('DB_USER', 'root'));
define('DB_PASS', env('DB_PASS', ''));
define('DB_NAME', env('DB_NAME', 'pharmacy_db'));
define('DB_PORT', env('DB_PORT', '3306'));
