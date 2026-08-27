<?php

// ───────────────────────── JWT CONFIGURATION ──────────────────────────────────
// Secret key used to sign and verify tokens
// WARNING: Change this before deploying to production
// WARNING: Changing this will log out ALL users (all existing tokens become invalid)
define('JWT_SECRET', 'CrewSync_JWT_S3cr3t#K9mP2qL8xZ4vN7wR1yT6!2026');

// How long the token is valid (7 days in seconds)
define('JWT_EXPIRY', 7 * 24 * 60 * 60);

// Cookie name stored in the browser
define('JWT_COOKIE_NAME', 'crewsync_token');


// ───────────────────────── GENERATE TOKEN ────────────────────────────────────────
// Takes user data, returns a signed JWT string
// $payload should contain: user_id, role, name
function generateToken(array $payload): string {

    // Part 1: Header — tells what algorithm we're using
    $header = base64UrlEncode(json_encode([
        'alg' => 'HS256',
        'typ' => 'JWT'
    ]));

    // Part 2: Payload — the actual user data + timestamps
    $payload['iat'] = time();                    // issued at (now)
    $payload['exp'] = time() + JWT_EXPIRY;       // expires at (7 days from now)

    $payload = base64UrlEncode(json_encode($payload));

    // Part 3: Signature — HMAC-SHA256 of "header.payload" using our secret key
    // This is what prevents anyone from tampering with the token
    $signature = base64UrlEncode(
        hash_hmac('sha256', "$header.$payload", JWT_SECRET, true)
    );

    // Final JWT = header.payload.signature
    return "$header.$payload.$signature";
}
    // ───────────────────────── GENERATE SHORT-LIVED TOKEN ────────────────────────
// Same as generateToken(), but with a custom expiry in seconds instead of
// the default 7-day JWT_EXPIRY. Used for things like OTP-verification proof,
// which should only be valid for a few minutes.
function generateShortToken(array $payload, int $expirySeconds): string {
    $header = base64UrlEncode(json_encode([
        'alg' => 'HS256',
        'typ' => 'JWT'
    ]));

    $payload['iat'] = time();
    $payload['exp'] = time() + $expirySeconds;

    $payload = base64UrlEncode(json_encode($payload));

    $signature = base64UrlEncode(
        hash_hmac('sha256', "$header.$payload", JWT_SECRET, true)
    );

    return "$header.$payload.$signature";
}



// ───────────────────────── VERIFY TOKEN ──────────────────────────────────────────
// Reads a JWT string, checks signature + expiry
// Returns the payload array if valid, false if invalid/expired
function verifyToken(string $token): array|false {

    // JWT must have exactly 3 parts separated by dots
    $parts = explode('.', $token);
    if (count($parts) !== 3) return false;

    [$header, $payload, $signature] = $parts;

    // Recompute the signature using the same secret key
    $expectedSignature = base64UrlEncode(
        hash_hmac('sha256', "$header.$payload", JWT_SECRET, true)
    );

    // If signatures don't match — token was tampered with, reject it
    if (!hash_equals($expectedSignature, $signature)) return false;

    // Decode the payload
    $data = json_decode(base64UrlDecode($payload), true);
    if (!$data) return false;

    // Check if token has expired
    if (isset($data['exp']) && time() > $data['exp']) return false;

    return $data;
}


// ───────────────────────── SET AUTH COOKIE ────────────────────────────────────────────
// Sends the JWT to the browser as a secure httpOnly cookie
// httpOnly = JS cannot read it (XSS protection)
// SameSite=None + Secure = required for cross-origin requests (Next.js → PHP)

function setAuthCookie(string $token): void {
    setcookie(JWT_COOKIE_NAME, $token, [
        'expires'  => time() + JWT_EXPIRY,   // matches token expiry (7 days)
        'path'     => '/',                    // available on all routes
        'domain'   => '',                     // current domain only
        'secure'   => false,                   // HTTPS only (required for SameSite=None)
        'httponly' => true,                   // JS cannot access this cookie
        'samesite' => 'Lax',                 // allows cross-origin (Next.js on diff port/domain)
    ]);
}


// ───────────────────────── CLEAR AUTH COOKIE ───────────────────────────────────────────────────────
// Called on logout — expires the cookie immediately
function clearAuthCookie(): void {
    setcookie(JWT_COOKIE_NAME, '', [
        'expires'  => time() - 3600,         // set in the past = browser deletes it
        'path'     => '/',
        'domain'   => '',
        'secure'   => false,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}
/*
A Secure cookie is only supposed to be sent over HTTPS — but your backend is plain http://localhost. Some browsers make an exception for localhost, others don't (and yours clearly doesn't send it). This combo (SameSite=None; Secure) is the production-grade setting, but it breaks local HTTP development.
Fix: for local dev, set the cookie as SameSite=Lax, not Secure. Find the setcookie() call in your login code (probably AuthController or config/jwt.php) — it'll look something like:
phpsetcookie(JWT_COOKIE_NAME, $token, [
    'expires'  => time() + 604800,
    'path'     => '/',
    'secure'   => true,      // ← problem
    'httponly' => true,
    'samesite' => 'None',    // ← problem
]);
 */

// ── HELPER: BASE64 URL ENCODING ───────────────────────────────────────────────
// Standard base64 uses +, /, = which are not URL-safe
// JWT uses a URL-safe variant: + → -, / → _, = removed
function base64UrlEncode(string $data): string {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64UrlDecode(string $data): string {
    return base64_decode(strtr($data, '-_', '+/'));
}