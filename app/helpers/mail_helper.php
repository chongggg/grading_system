<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

/**
 * Helper: mail_helper.php
 * Send an email with dynamic name, email, subject, message, and optional attachment.
 */
function mail_helper($name, $email, $subject, $message, $attachmentPath = null)
{
    // PHPMailer is already autoloaded via config composer_autoload
    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

    try {
        // SMTP config
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'chongmiranda21@gmail.com';
        $mail->Password   = 'ylhe ufic nuff vmtw';
        $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;
        
        // Enable verbose debug output for troubleshooting
        $mail->SMTPDebug  = 0; // 0 = off, 2 = client and server messages
        $mail->Debugoutput = function($str, $level) {
            error_log("PHPMailer: $str");
        };

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
    } catch (\Exception $e) {
        // Log the error for debugging
        error_log("Mail Helper Error: " . $e->getMessage());
        error_log("PHPMailer ErrorInfo: " . $mail->ErrorInfo);
        
        // On production with network restrictions, return true to allow registration to continue
        // The admin will need to manually approve users
        if (strpos($e->getMessage(), 'Network is unreachable') !== false || 
            strpos($e->getMessage(), 'Could not connect to SMTP') !== false) {
            error_log("Mail Helper: SMTP blocked by hosting provider. User registration will proceed without email.");
            return true; // Allow registration to continue
        }
        
        return "Mailer Error: {$mail->ErrorInfo}";
    }
}
