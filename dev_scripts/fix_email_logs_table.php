<?php
require_once __DIR__ . '/../config/database.php';

try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Drop existing table if it has wrong schema
    $db->exec("DROP TABLE IF EXISTS email_logs");

    $sql = "CREATE TABLE email_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NULL,
        recipient_email VARCHAR(255) NOT NULL,
        subject VARCHAR(255) NOT NULL,
        type VARCHAR(50) NOT NULL,
        status ENUM('sent', 'failed', 'opened') DEFAULT 'sent',
        sent_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        error_message TEXT NULL,
        tracking_id VARCHAR(100) UNIQUE,
        INDEX (order_id),
        INDEX (recipient_email)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    $db->exec($sql);
    echo "Table email_logs RE-CREATED successfully with correct schema.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
