<?php
declare(strict_types=1);

namespace App\Core;

use PHPMailer\PHPMailer\Exception as MailException;
use PHPMailer\PHPMailer\PHPMailer;
use RuntimeException;

final class Mailer
{
    public function sendPasswordResetCode(string $recipient, string $code): void
    {
        if (!class_exists(PHPMailer::class)) {
            throw new RuntimeException('PHPMailer is not installed. Run composer install.');
        }

        $config = require dirname(__DIR__, 2) . '/config/mail.php';
        if (
            $config['username'] === ''
            || $config['password'] === ''
            || $config['from_email'] === ''
            || !filter_var($config['from_email'], FILTER_VALIDATE_EMAIL)
            || !in_array($config['encryption'], ['tls', 'ssl'], true)
        ) {
            throw new RuntimeException('SMTP configuration is incomplete. Check the .env file.');
        }

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = $config['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $config['username'];
            $mail->Password = $config['password'];
            $mail->SMTPSecure = $config['encryption'] === 'ssl'
                ? PHPMailer::ENCRYPTION_SMTPS
                : PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $config['port'];
            $mail->CharSet = PHPMailer::CHARSET_UTF8;
            $mail->setFrom($config['from_email'], $config['from_name']);
            $mail->addAddress($recipient);
            $mail->isHTML(true);
            $mail->Subject = 'GreenPulse | Password Reset Verification Code';
            $mail->Body = '<!doctype html><html lang="en"><body style="margin:0;padding:32px 16px;background:#eef3e8;font-family:Arial,Helvetica,sans-serif;color:#14261c;">'
                . '<div style="max-width:560px;margin:0 auto;padding:32px;background:#ffffff;border:1px solid #d9e3d2;border-radius:12px;">'
                . '<h1 style="margin:0 0 24px;font-size:22px;color:#2f6b3a;">GreenPulse</h1>'
                . '<h2 style="margin:0 0 16px;font-size:20px;">Password reset request</h2>'
                . '<p style="margin:0 0 16px;line-height:1.6;">We received a request to reset the password associated with your GreenPulse account. Enter the verification code below to continue:</p>'
                . '<p style="margin:24px 0;padding:16px;text-align:center;background:#eef3e8;border-radius:8px;font-size:30px;font-weight:700;letter-spacing:8px;color:#14261c;">'
                . $code
                . '</p>'
                . '<p style="margin:0 0 12px;line-height:1.6;"><strong>This code expires in 10 minutes</strong> and may be used only once. For your security, do not share this code with anyone.</p>'
                . '<p style="margin:0 0 20px;line-height:1.6;">If you did not request a password reset, no further action is required. Your password will remain unchanged.</p>'
                . '<p style="margin:0;padding-top:16px;border-top:1px solid #d9e3d2;color:#5b6e61;font-size:13px;">This is an automated message. Please do not reply to this email.</p>'
                . '</div></body></html>';
            $mail->AltBody = "GreenPulse password reset request\n\n"
                . "We received a request to reset the password associated with your GreenPulse account. "
                . "Enter this verification code to continue: {$code}\n\n"
                . "This code expires in 10 minutes and may be used only once. For your security, do not share this code with anyone.\n\n"
                . "If you did not request a password reset, no further action is required. Your password will remain unchanged.\n\n"
                . "This is an automated message. Please do not reply to this email.";
            $mail->send();
        } catch (MailException $exception) {
            throw new RuntimeException('SMTP email delivery failed.', 0, $exception);
        }
    }
}
