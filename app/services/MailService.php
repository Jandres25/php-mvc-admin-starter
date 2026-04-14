<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../libs/PHPMailer-master/src/Exception.php';
require_once __DIR__ . '/../../libs/PHPMailer-master/src/PHPMailer.php';
require_once __DIR__ . '/../../libs/PHPMailer-master/src/SMTP.php';

/**
 * Mail Service
 *
 * Handles sending emails using PHPMailer.
 */
class MailService
{
    /**
     * Sends a password reset email.
     *
     * @param string $email
     * @param string $token
     * @return bool
     */
    public function sendPasswordResetEmail($email, $token)
    {
        $resetLink = $GLOBALS['URL'] . "views/auth/reset_password.php?token=" . $token;

        $subject = "Password Reset - Base System";
        $message = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
            <h2 style='color: #007bff;'>Password Reset Request</h2>
            <p>You have requested to reset your password. Please click the button below to set a new password:</p>
            <div style='text-align: center; margin: 30px 0;'>
                <a href='{$resetLink}' style='background-color: #007bff; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold;'>Reset Password</a>
            </div>
            <p>Or copy and paste this link into your browser:</p>
            <p style='word-break: break-all;'><a href='{$resetLink}'>{$resetLink}</a></p>
            <p style='color: #666; font-size: 0.9em;'>This link will expire in 1 hour.</p>
            <p style='border-top: 1px solid #eee; padding-top: 10px; margin-top: 20px; font-size: 0.8em; color: #888;'>
                If you did not request this, please ignore this email.
            </p>
        </div>
        ";

        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->SMTPDebug = env('DEBUG') ? SMTP::DEBUG_SERVER : SMTP::DEBUG_OFF;
            $mail->isSMTP();
            $mail->Host       = env('MAIL_HOST', 'localhost');
            $mail->SMTPAuth   = true;
            $mail->Username   = env('MAIL_USER', '');
            $mail->Password   = env('MAIL_PASS', '');
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = env('MAIL_PORT', 587);
            $mail->CharSet    = 'UTF-8';

            // Custom error output for debugging
            if (env('DEBUG')) {
                $mail->Debugoutput = function($str, $level) {
                    error_log("SMTP DEBUG [$level]: $str");
                };
            }

            // Recipients
            $fromEmail = env('MAIL_FROM', 'noreply@example.com');
            $fromName  = env('MAIL_FROM_NAME', 'Base System');
            $mail->setFrom($fromEmail, $fromName);
            $mail->addAddress($email);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $message;
            $mail->AltBody = "You have requested to reset your password. Use this link: {$resetLink}";

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("PHPMailer error: " . $mail->ErrorInfo);
            return false;
        }
    }
}
