<?php
// api/notification_service.php
require_once __DIR__ . '/../config/pusher_config.php';

function notifyDoctorIncomingRequest($doctorId, $requestData) {
    try {
        $pusher = getPusher();
        $channelName = 'private-doctor-' . $doctorId;
        $result = $pusher->trigger($channelName, 'incoming-consult', $requestData);
        file_put_contents(__DIR__ . '/../storage/logs/pusher_debug.log', "[" . date('Y-m-d H:i:s') . "] Triggered to $channelName: " . json_encode($result) . "\n", FILE_APPEND);
    } catch (Exception $e) {
        file_put_contents(__DIR__ . '/../storage/logs/pusher_debug.log', "[" . date('Y-m-d H:i:s') . "] Error Pusher: " . $e->getMessage() . "\n", FILE_APPEND);
    }
}

function notifyCustomerStatus($customerId, $statusData) {
    try {
        $pusher = getPusher();
        $channelName = 'private-customer-' . $customerId;
        $pusher->trigger($channelName, 'request-status', $statusData);
    } catch (Exception $e) {
        // Log
    }
}
?>
