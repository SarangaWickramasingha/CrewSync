<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ── MAIL SETTINGS ──────────────────────────────────────────────────────────
// Use a Gmail App Password, NOT your normal Gmail password.
// Generate one at: https://myaccount.google.com/apppasswords
// (requires 2-Step Verification to be turned on for your Google account)
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', 'crewsync2027@gmail.com');      // ← your Gmail address
define('MAIL_PASSWORD', 'vvxr bsnf iqhu vmuh');         // ← 16-char App Password
define('MAIL_FROM_ADDRESS', 'crewsync2027@gmail.com');   // ← usually same as username
define('MAIL_FROM_NAME', 'CrewSync');

/**
 * Sends an HTML email via SMTP. Returns true on success, false on failure.
 * Failures are written to PHP's error log, not shown to the end user.
 */
function sendMail(string $toEmail, string $toName, string $subject, string $htmlBody): bool {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = MAIL_PORT;

        $mail->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
        $mail->addAddress($toEmail, $toName);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = strip_tags($htmlBody);

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}

/**
 * Sends the 6-digit OTP email with a simple branded template.
 */
function sendOtpEmail(string $toEmail, string $otp): bool {
    $subject = "Your CrewSync verification code";
    $body = "
        <div style='font-family: sans-serif; max-width: 480px; margin: 0 auto;'>
            <h2 style='color:#1A1D23;'>Verify your email</h2>
            <p style='color:#4A5068;'>Use the code below to verify your email address. This code expires in 10 minutes.</p>
            <div style='font-size: 32px; font-weight: bold; letter-spacing: 8px; background:#F7F6F2; padding: 16px 24px; text-align:center; border-radius: 8px; color:#E8820C;'>
                {$otp}
            </div>
            <p style='color:#8A8FA8; font-size: 12px; margin-top: 24px;'>If you didn't request this, you can safely ignore this email.</p>
        </div>
    ";
    return sendMail($toEmail, '', $subject, $body);
}