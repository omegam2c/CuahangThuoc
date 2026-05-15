<?php
require_once __DIR__ . '/../config/database.php';

try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "ALTER TABLE orders ADD COLUMN customer_email VARCHAR(255) NULL AFTER shipping_phone;";
    $db->exec($sql);
    echo "Column customer_email added to orders table successfully.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
