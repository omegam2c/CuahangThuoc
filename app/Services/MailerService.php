<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use App\Services\Emails\BaseEmail;

class MailerService {
    private $db;
    private $mailer;

    public function __construct() {
        // Database connection
        try {
            $this->db = new \PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
            $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            error_log("Mailer DB Error: " . $e->getMessage());
        }

        $this->mailer = new PHPMailer(true);
        
        // SMTP settings
        $this->mailer->isSMTP();
        $this->mailer->Host       = SMTP_HOST;
        $this->mailer->SMTPAuth   = true;
        $this->mailer->Username   = SMTP_USER;
        $this->mailer->Password   = SMTP_PASS;
        $this->mailer->SMTPSecure = SMTP_SECURE;
        $this->mailer->Port       = SMTP_PORT;
        $this->mailer->CharSet    = 'UTF-8';
        $this->mailer->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
    }

    /**
     * Dispatch an email using the provided BaseEmail object
     */
    public function dispatch(BaseEmail $email, string $toEmail, string $toName = '', int $orderId = null): bool {
        $trackingId = bin2hex(random_bytes(16));
        $data = $email->getEmailData();
        $type = $data['type'] ?? 'general';
        
        // Add tracking pixel to body
        $trackingPixel = '<img src="' . SITE_URL . 'track/email?id=' . $trackingId . '" width="1" height="1" style="display:none">';
        $data['body'] .= $trackingPixel;

        try {
            $this->mailer->addAddress($toEmail, $toName);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = $data['subject'];
            $this->mailer->Body    = $data['body'];
            
            if (!empty($data['attachments'])) {
                foreach ($data['attachments'] as $attachment) {
                    $this->mailer->addAttachment($attachment['path'], $attachment['name'] ?? '');
                }
            }

            $this->mailer->send();
            $this->logEmail($orderId, $toEmail, $data['subject'], $type, 'sent', null, $trackingId);
            return true;
        } catch (Exception $e) {
            $errorMessage = $this->mailer->ErrorInfo;
            error_log("Mailer Error: " . $errorMessage);
            $this->logEmail($orderId, $toEmail, $data['subject'], $type, 'failed', $errorMessage, $trackingId);
            return false;
        } finally {
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();
        }
    }

    private function logEmail($orderId, $recipient, $subject, $type, $status, $error = null, $trackingId = null) {
        if (!$this->db) return;
        try {
            $sql = "INSERT INTO email_logs (order_id, recipient_email, subject, type, status, error_message, tracking_id) 
                    VALUES (:order_id, :recipient, :subject, :type, :status, :error, :tracking_id)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':order_id' => $orderId,
                ':recipient' => $recipient,
                ':subject' => $subject,
                ':type' => $type,
                ':status' => $status,
                ':error' => $error,
                ':tracking_id' => $trackingId
            ]);
        } catch (\PDOException $e) {
            error_log("Log Email Error: " . $e->getMessage());
        }
    }
}
