<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Helper: mail_helper.php
 * Send an email with dynamic name, email, subject, message, and optional attachment.
 */
function mail_helper($name, $email, $subject, $message, $attachmentPath = null)
{
    // Ensure Composer autoload is available for PHPMailer classes
    if (file_exists(APP_DIR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php')) {
        require_once APP_DIR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
    }

    $mail = new PHPMailer(true);

    try {
        // SMTP config
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'chongmiranda21@gmail.com'; // your Gmail
        $mail->Password   = 'ylhe ufic nuff vmtw'; // your App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        // From and To
        $mail->setFrom('chongmiranda21@gmail.com', 'System Admin'); // sender
        $mail->addAddress($email, $name); // receiver = user input

        // Attachment (optional)
        if ($attachmentPath && file_exists($attachmentPath)) {
            $mail->addAttachment($attachmentPath);
        }

        // Email content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message; // Already HTML formatted
        $mail->AltBody = strip_tags($message);

        $mail->send();
        return true;
    } catch (Exception $e) {
        return "Mailer Error: {$mail->ErrorInfo}";
    }
}
