<?php
require_once __DIR__ . '/../config/database.php';
$db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
$stmt = $db->query("DESCRIBE orders");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
