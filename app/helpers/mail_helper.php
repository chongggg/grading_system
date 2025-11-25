<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

/**
 * Helper: mail_helper.php
 * Send an email with dynamic name, email, subject, message, and optional attachment.
 */
function mail_helper($name, $email, $subject, $message, $attachmentPath = null)
{
    // Check if SendGrid API key is available
    $sendgrid_api_key = getenv('SENDGRID_API_KEY') ?: (isset($GLOBALS['sendgrid_api_key']) ? $GLOBALS['sendgrid_api_key'] : null);
    
    // Use SendGrid HTTP API if key is available (works on all hosting providers, no port blocking)
    if ($sendgrid_api_key && $sendgrid_api_key !== 'YOUR_SENDGRID_API_KEY_HERE') {
        try {
            error_log("Mail Helper: Using SendGrid HTTP API");
            
            $sendgrid = new \SendGrid($sendgrid_api_key);
            
            $sendgrid_email = new \SendGrid\Mail\Mail();
            $sendgrid_email->setFrom("noreply@yourdomain.com", "Grading Management System");
            $sendgrid_email->setSubject($subject);
            $sendgrid_email->addTo($email, $name);
            $sendgrid_email->addContent("text/html", $message);
            
            $response = $sendgrid->send($sendgrid_email);
            
            if ($response->statusCode() >= 200 && $response->statusCode() < 300) {
                error_log("Mail Helper: Email sent successfully via SendGrid API");
                return true;
            } else {
                error_log("Mail Helper: SendGrid API error: " . $response->statusCode() . " - " . $response->body());
                return true; // Still allow registration to proceed
            }
        } catch (\Exception $e) {
            error_log("Mail Helper: SendGrid API exception: " . $e->getMessage());
            return true; // Allow registration to continue
        }
    }
    
    // Fallback to PHPMailer (for localhost or if SendGrid not configured)
    error_log("Mail Helper: Using PHPMailer fallback");
    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'chongmiranda21@gmail.com';
        $mail->Password   = 'ylhe ufic nuff vmtw';
        $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        
        $mail->setFrom('chongmiranda21@gmail.com', 'Grading Management System');
        $mail->addAddress($email, $name);
        
        if ($attachmentPath && file_exists($attachmentPath)) {
            $mail->addAttachment($attachmentPath);
        }
        
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message;
        $mail->AltBody = strip_tags($message);

        $mail->send();
        return true;
    } catch (\Exception $e) {
        error_log("Mail Helper: PHPMailer error: " . $e->getMessage());
        return true; // Allow registration to continue even if email fails
    }
}
