<?php

require_once __DIR__ . '/config/cors.php';

// Get request URI
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Remove folder path (adjust if needed)
$uri = str_replace('/CrewSync-backend/backend/index.php', '', $uri);
echo json_encode(["debug_uri" => $uri, "method" => $method]);
exit();

if ($uri === '/api/auth/login' && $method === 'POST') {
    require_once __DIR__ . '/routes/auth.php';
    login();
}

else {
    http_response_code(404);
    echo json_encode([
        "success" => false,
        "message" => "Route not found"
    ]);
}