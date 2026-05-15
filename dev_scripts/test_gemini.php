<?php
require_once __DIR__ . '/../config/gemini_config.php';
require_once __DIR__ . '/../config/db_connect.php';

$stmt = $pdo->query("SELECT product_name, indications, dosage_instructions, is_prescription_required FROM products WHERE is_active = 1");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$context = "";
foreach ($products as $p) {
    $context .= "- Tên thuốc: " . $p['product_name'] . "\n";
    $context .= "  Công dụng: " . $p['indications'] . "\n";
    $context .= "  Liều dùng: " . $p['dosage_instructions'] . "\n";
    $context .= "  Kê đơn: " . ($p['is_prescription_required'] ? "1" : "0") . "\n\n";
}

$prompt = "Bạn là chuyên gia tư vấn ảo của 'Nhà Thuốc 1985'. Nhiệm vụ của bạn là hỗ trợ khách hàng tìm kiếm thuốc theo triệu chứng.
DỮ LIỆU SẢN PHẨM:
" . $context . "
NGUYÊN TẮC: Gợi ý thuốc từ danh sách trên. Không tự bịa.
LỊCH SỬ CHAT: 
KHÁCH: tôi bị đau đầu nên uống thuốc gì
TIN NHẮN MỚI TỪ KHÁCH HÀNG: sản phẩm nào";

$url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key=' . GEMINI_API_KEY;

$data = [
    "contents" => [
        [
            "parts" => [
                ["text" => $prompt]
            ]
        ]
    ],
    "generationConfig" => [
        "temperature" => 0.4,
        "maxOutputTokens" => 800
    ]
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 

$response = curl_exec($ch);
curl_close($ch);

echo "Response:\n" . $response;


if ($err) {
    echo "cURL Error: " . $err;
} else {
    echo "Response: " . $response;
}
