<?php
const _valid = true;

// Application Debug Mode (Ref point 5 - Security Leak)
define('_DEBUG', env('APP_DEBUG', false));

// SMTP Configuration (Ref point 1 - Hardcoded)
define('SMTP_HOST', env('SMTP_HOST', 'smtp.gmail.com'));
define('SMTP_USER', env('SMTP_USER', ''));
define('SMTP_PASS', env('SMTP_PASS', ''));
define('SMTP_PORT', env('SMTP_PORT', 587));
define('SMTP_SECURE', env('SMTP_SECURE', 'tls'));
define('SMTP_FROM_EMAIL', env('SMTP_USER', ''));
define('SMTP_FROM_NAME', env('SMTP_FROM_NAME', 'Nhà thuốc 1985'));

// PayOS Configuration (Ref point 1 - Hardcoded)
define('PAYOS_CLIENT_ID', env('PAYOS_CLIENT_ID', ''));
define('PAYOS_API_KEY', env('PAYOS_API_KEY', ''));
define('PAYOS_CHECKSUM_KEY', env('PAYOS_CHECKSUM_KEY', ''));

// SSL cURL Certificate
putenv('CURL_CA_BUNDLE=' . __DIR__ . '/cacert.pem');
