<?php

require_once __DIR__ . '/config/cors.php';

// Get request URI
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Remove folder path (adjust if needed)
$uri = str_replace('/CrewSync/backend/index.php', '', $uri);

// Debug mode — only runs if you add ?debug=1 to the URL
if (isset($_GET['debug'])) {
    echo json_encode(["debug_uri" => $uri, "method" => $method]);
    exit();
}

if ($uri === '/api/auth/login' && $method === 'POST') {
    require_once __DIR__ . '/routes/auth.php';
    login();
}

else if ($uri === '/api/feedback/submit' && $method === 'POST') {
    require_once __DIR__ . '/routes/feedback.php';
    submitFeedback();
}

else if ($uri === '/api/feedback' && $method === 'GET') {
    require_once __DIR__ . '/routes/feedback.php';
    listFeedback();
}

else if ($uri === '/api/feedback/status' && $method === 'PUT') {
    require_once __DIR__ . '/routes/feedback.php';
    updateFeedbackStatus();
}

else {
    http_response_code(404);
    echo json_encode([
        "success" => false,
        "message" => "Route not found"
    ]);
}