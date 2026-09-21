<?php

define('BREVO_API_KEY', Env::get('BREVO_API_KEY', ''));
define('BREVO_FROM_EMAIL', Env::get('BREVO_FROM_EMAIL', ''));
define('BREVO_FROM_NAME', Env::get('BREVO_FROM_NAME', 'CrewSync'));

/**
 * Sends an HTML email via the Brevo (Sendinblue) Transactional API
 * (works on Render free tier — no SMTP required). Returns true on success,
 * false on failure. Failures are written to PHP's error log, not shown
 * to the end user.
 *
 * Note: BREVO_FROM_EMAIL must be a sender that is VERIFIED in your Brevo
 * account (Settings → Senders) or a verified sending domain, otherwise the
 * API returns 400 and every send fails.
 */
function sendMail(string $toEmail, string $toName, string $subject, string $htmlBody): bool {
    if (BREVO_API_KEY === '') {
        error_log('Brevo Error: BREVO_API_KEY is not configured.');
        return false;
    }
    if (BREVO_FROM_EMAIL === '') {
        error_log('Brevo Error: BREVO_FROM_EMAIL is not configured.');
        return false;
    }

    $toName = $toName !== '' ? $toName : $toEmail;

    $payload = [
        'sender'      => [
            'email' => BREVO_FROM_EMAIL,
            'name'  => BREVO_FROM_NAME,
        ],
        'to'          => [
            [
                'email' => $toEmail,
                'name'  => $toName,
            ],
        ],
        'subject'     => $subject,
        'htmlContent' => $htmlBody,
    ];

    $ch = curl_init('https://api.brevo.com/v3/smtp/email');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_HTTPHEADER     => [
            'api-key: ' . BREVO_API_KEY,
            'x-api-key: ' . BREVO_API_KEY,
            'Content-Type: application/json',
            'Accept: application/json',
        ],
        CURLOPT_TIMEOUT        => 20,
    ]);

    $response = curl_exec($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    // Brevo returns HTTP 201 on success. A decodable body may be returned on
    // 4xx (e.g. sender not verified / invalid API key), so log it for debugging.
    if ($response === false || $httpCode < 200 || $httpCode >= 300) {
        error_log('Brevo Error: HTTP ' . $httpCode . ' ' . ($curlErr ?: (string) $response));
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