<?php

require_once __DIR__ . '/../config/jwt.php';

// ── AUTH MIDDLEWARE ───────────────────────────────────────────────────────────
// Call requireAuth() at the top of any protected controller method
// It reads the httpOnly cookie, validates the JWT, and returns the user payload
// If the token is missing or invalid, it kills the request with a 401 response

function requireAuth(): array {

    // Read the JWT from the httpOnly cookie
    $token = $_COOKIE[JWT_COOKIE_NAME] ?? null;

    if (!$token) {
        http_response_code(401);
        echo json_encode([
            "success" => false,
            "message" => "Unauthenticated. Please log in."
        ]);
        exit(); // stop everything — no further code runs
    }

    // Verify the token (checks signature + expiry)
    $payload = verifyToken($token);

    if (!$payload) {
        http_response_code(401);
        echo json_encode([
            "success" => false,
            "message" => "Session expired. Please log in again."
        ]);
        exit();
    }

    // Token is valid — return the payload so the controller knows who's calling
    // $payload contains: user_id, name, role, iat, exp
    return $payload;
}


// ── ROLE GUARD ────────────────────────────────────────────────────────────────
// Call requireRole('property_owner') to restrict a route to one role
// Pass an array requireRole(['admin', 'property_owner']) for multiple roles

function requireRole(string|array $allowedRoles): array {

    // First validate the token
    $payload = requireAuth();

    $allowedRoles = (array) $allowedRoles; // normalize to array

    if (!in_array($payload['role'], $allowedRoles)) {
        http_response_code(403);
        echo json_encode([
            "success" => false,
            "message" => "Access denied. You don't have permission for this."
        ]);
        exit();
    }

    return $payload;
}
