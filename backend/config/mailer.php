<?php

define('RESEND_API_KEY', Env::get('RESEND_API_KEY', ''));
define('RESEND_FROM_ADDRESS', Env::get('RESEND_FROM_ADDRESS', 'onboarding@resend.dev'));
define('RESEND_FROM_NAME', Env::get('RESEND_FROM_NAME', 'CrewSync'));

/**
 * Sends an HTML email via the Resend HTTP API (works on Render free tier —
 * no SMTP required). Returns true on success, false on failure.
 * Failures are written to PHP's error log, not shown to the end user.
 */
function sendMail(string $toEmail, string $toName, string $subject, string $htmlBody): bool {
    $from = RESEND_FROM_NAME !== ''
        ? RESEND_FROM_NAME . ' <' . RESEND_FROM_ADDRESS . '>'
        : RESEND_FROM_ADDRESS;

    $to = $toName !== '' ? $toName . ' <' . $toEmail . '>' : $toEmail;

    $payload = [
        'from'    => $from,
        'to'      => [$to],
        'subject' => $subject,
        'html'    => $htmlBody,
    ];

    $ch = curl_init('https://api.resend.com/emails');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . RESEND_API_KEY,
            'Content-Type: application/json',
        ],
        CURLOPT_TIMEOUT        => 20,
    ]);

    $response = curl_exec($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($response === false || $httpCode < 200 || $httpCode >= 300) {
        error_log('Resend Error: HTTP ' . $httpCode . ' ' . ($curlErr ?: $response));
        return false;
    }

    return true;
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
