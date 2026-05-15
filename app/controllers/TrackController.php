<?php

require_once __DIR__ . '/BaseController.php';

class TrackController extends BaseController {
    
    public function email() {
        $trackingId = $_GET['id'] ?? null;
        
        if ($trackingId) {
            $db = (new BaseModel())->db;
            $stmt = $db->prepare("UPDATE email_logs SET status = 'opened' WHERE tracking_id = :id AND status = 'sent'");
            $stmt->execute(['id' => $trackingId]);
        }
        
        // Return 1x1 transparent GIF
        header('Content-Type: image/gif');
        echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
        exit;
    }
}
