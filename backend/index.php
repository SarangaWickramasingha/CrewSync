<?php

require_once __DIR__ . '/config/cors.php';
require_once __DIR__ . '/config/database.php';

// Get request URI
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Remove folder path (adjust if needed)
$uri = str_replace('/CrewSync-backend/backend/index.php', '', $uri);


if ($uri === '/api/auth/login' && $method === 'POST') {
    require_once __DIR__ . '/routes/auth.php';
    login();
}
elseif ($uri === '/api/auth/register' && $method === 'POST') {
    require_once __DIR__ . '/routes/auth.php';
    register();
} 
elseif ($uri === '/api/auth/check-email' && $method === 'POST') {
    require_once __DIR__ . '/routes/auth.php';
    checkEmail();
} 
else {
    http_response_code(404);
    echo json_encode([
        "success" => false,
        "message" => "Route not found"
    ]);
}
function checkEmail() {
    $controller = new AuthController();
    $controller->checkEmail();
}