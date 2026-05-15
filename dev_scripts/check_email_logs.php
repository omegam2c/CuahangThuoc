<?php
require_once __DIR__ . '/../config/database.php';
$db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
$stmt = $db->query("SELECT COUNT(*) as total FROM email_logs");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Total email logs: " . $row['total'] . "\n";

$stmt = $db->query("SELECT * FROM email_logs LIMIT 5");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
