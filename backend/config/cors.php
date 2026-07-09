<?php
// ── WHO IS ALLOWED TO TALK TO THIS BACKEND? ───────────────────────────────────
// During development your Next.js runs on port 3000
// Change this to your real domain when you deploy to internet
$allowed_origins = ['http://localhost:3000', 'http://localhost:3001'];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

// If the request is coming from Next.js, allow it
if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins)) {
    header("Access-Control-Allow-Origin: $origin");
}

// What methods can Next.js use?
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

// What headers can Next.js send?
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Allow cookies/tokens to be sent
header("Access-Control-Allow-Credentials: true");

// All our responses will be JSON
header("Content-Type: application/json; charset=UTF-8");

// ── PREFLIGHT REQUEST ─────────────────────────────────────────────────────────
// Before every real request, the browser sends a "preflight" OPTIONS request
// asking "is it okay if I send this?" — we just say yes and exit
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}