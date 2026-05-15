<?php
require_once __DIR__ . '/../config/gemini_config.php';
$ch = curl_init('https://generativelanguage.googleapis.com/v1beta/models?key='.GEMINI_API_KEY);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
curl_close($ch);
$data = json_decode($response, true);
$names = [];
if (isset($data['models'])) {
    foreach ($data['models'] as $m) {
        $names[] = $m['name'];
    }
}
file_put_contents('scratch_models.txt', implode("\n", $names));
