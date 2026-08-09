<?php
// includes/mailer.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Path to vendor autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// --- Configuration Settings ---
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USER', 'your_email@gmail.com');           // Your Gmail
define('SMTP_PASS', 'abcdefghijklmnop');               // Your 16-character App Password
define('SMTP_PORT', 587);
define('FROM_EMAIL', 'your_email@gmail.com');
define('FROM_NAME', 'CertiChain System');

/**
 * Reusable email function
 *
 * @param string $toEmail Recipient's email address
 * @param string $toName  Recipient's name
 * @param string $subject Email subject line
 * @param string $htmlBody Email body (HTML allowed)
 * @param string|null $attachmentPath Optional file path to attach (e.g., PDF certificate)
 * @return bool|string Returns true on success, or an error message string on failure.
 */
function sendEmail($toEmail, $toName, $subject, $htmlBody, $attachmentPath = null) {
    $mail = new PHPMailer(true);

    try {
        // Server configuration
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;

        // Sender & Recipient
        $mail->setFrom(FROM_EMAIL, FROM_NAME);
        $mail->addAddress($toEmail, $toName);

        // Optional File Attachment
        if ($attachmentPath && file_exists($attachmentPath)) {
            $mail->addAttachment($attachmentPath);
        }

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = strip_tags($htmlBody); // Fallback for plain-text email clients

        $mail->send();
        return true;
    } catch (Exception $e) {
        return "Mailer Error: " . $mail->ErrorInfo;
    }
}