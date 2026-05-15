<?php
// api/bot_service.php
require_once __DIR__ . '/../config/gemini_config.php';

class AntigravityBot
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function processMessage($conversationId, $userMessage)
    {
        $productsContext = $this->getProductsContext();
        $chatHistory     = $this->getChatHistory($conversationId);

        $prompt = "Bạn là chuyên gia tư vấn ảo của 'Nhà Thuốc 1985', là một dược sĩ chuyên môn cao. "
            . "Khi tư vấn, hãy luôn ưu tiên an toàn. Nếu triệu chứng nguy hiểm, hãy yêu cầu người dùng đi khám bác sĩ. "
            . "Chỉ tư vấn các loại thuốc không kê đơn (OTC) cho các triệu chứng nhẹ.\n\n"
            . "DỮ LIỆU SẢN PHẨM HIỆN CÓ:\n" . $productsContext . "\n"
            . "NGUYÊN TẮC TƯ VẤN (RẤT QUAN TRỌNG):\n"
            . "1. CHỈ ĐƯỢC gợi ý tối đa 3 loại thuốc có tên chính xác trong DỮ LIỆU SẢN PHẨM ở trên. KHÔNG tự bịa ra thuốc.\n"
            . "2. Nếu không có thuốc phù hợp, nói: 'Nhà Thuốc 1985 hiện không có thuốc đặc trị phù hợp, vui lòng bấm nút Kết nối Dược sĩ.'\n"
            . "3. Nếu gợi ý thuốc kê đơn (kê_đơn=1), BẮT BUỘC kèm: 'Đây là thuốc kê đơn, vui lòng kết nối Dược sĩ để được duyệt đơn.'\n"
            . "4. Phong cách: Chuyên nghiệp, thấu cảm, tiếng Việt tự nhiên, ngắn gọn. KHÔNG dùng markdown phức tạp.\n\n"
            . "LỊCH SỬ CHAT:\n" . $chatHistory . "\n"
            . "KHÁCH HÀNG: " . $userMessage;

        return $this->callGeminiApi($prompt);
    }

    private function getProductsContext()
    {
        try {
            $stmt = $this->pdo->query(
                "SELECT product_name, indications, dosage_instructions, is_prescription_required 
                 FROM products WHERE is_active = 1 LIMIT 50"
            );
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return "(Không thể tải danh sách sản phẩm)";
        }

        $context = "";
        foreach ($products as $p) {
            $context .= "- Tên: " . $p['product_name']
                . " | Công dụng: " . $p['indications']
                . " | Liều dùng: " . $p['dosage_instructions']
                . " | Kê đơn: " . ($p['is_prescription_required'] ? "1" : "0") . "\n";
        }
        return $context ?: "(Kho hàng trống)";
    }

    private function getChatHistory($conversationId)
    {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT sender_id, content FROM messages 
                 WHERE conversation_id = ? ORDER BY created_at ASC LIMIT 10"
            );
            $stmt->execute([$conversationId]);
            $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return "";
        }

        $history = "";
        foreach ($messages as $m) {
            $role     = ($m['sender_id'] == 1) ? "TRỢ LÝ" : "KHÁCH";
            $history .= $role . ": " . $m['content'] . "\n";
        }
        return $history;
    }

    private function callGeminiApi($prompt)
    {
        $apiKey = env('GEMINI_API_KEY');
        if (empty($apiKey)) {
            return "Tôi là Trợ lý Ảo Nhà Thuốc 1985. Bạn đang có triệu chứng gì? Nếu cần gặp người thật, hãy bấm 'Kết nối Dược sĩ'.";
        }

        $url  = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . $apiKey;
        $data = [
            "contents"         => [["parts" => [["text" => $prompt]]]],
            "generationConfig" => [
                "temperature"    => 0.3,
                "maxOutputTokens"=> 600,
                // Tắt chế độ "thinking" của gemini-2.5-flash để tránh lỗi parts[0] là thought
                "thinkingConfig" => ["thinkingBudget" => 0]
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($curlErr) {
            return "Lỗi kết nối AI: " . $curlErr;
        }

        $resObj = json_decode($response, true);

        if ($httpCode !== 200) {
            $apiError = $resObj['error']['message'] ?? "HTTP $httpCode";
            error_log("[Bot] Gemini API error ($httpCode): " . $apiError);
            return "Dịch vụ AI tạm thời không khả dụng (lỗi $httpCode). Vui lòng bấm 'Kết nối Dược sĩ' để được hỗ trợ trực tiếp.";
        }

        // Duyệt qua tất cả parts để tìm phần text thực (bỏ qua parts kiểu 'thought' của thinking mode)
        $parts = $resObj['candidates'][0]['content']['parts'] ?? [];
        foreach ($parts as $part) {
            if (!empty($part['text']) && empty($part['thought'])) {
                return $part['text'];
            }
        }

        return "Tôi không thể xử lý yêu cầu lúc này. Vui lòng kết nối Dược sĩ.";
    }
}
