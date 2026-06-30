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
elseif ($uri === '/api/auth/me' && $method === 'GET') {
    require_once __DIR__ . '/routes/auth.php';
    me();
}
elseif ($uri === '/api/auth/logout' && $method === 'POST') {
    require_once __DIR__ . '/routes/auth.php';
    logout();
}


// ── PROJECTS ─────────────────────────────────────────────────────────────────
elseif ($uri === '/api/projects' && $method === 'GET') {
    require_once __DIR__ . '/routes/projects.php';
    getAllProjects();
}
elseif (preg_match('#^/api/projects/(\d+)$#', $uri, $matches) && $method === 'GET') {
    require_once __DIR__ . '/routes/projects.php';
    getOneProject($matches[1]);
}
elseif (preg_match('#^/api/projects/(\d+)/toggle-finish$#', $uri, $matches) && $method === 'PUT') {
    require_once __DIR__ . '/routes/projects.php';
    toggleProjectFinish($matches[1]);
}

// ── TASKS ────────────────────────────────────────────────────────────────────
elseif ($uri === '/api/tasks' && $method === 'POST') {
    require_once __DIR__ . '/routes/tasks.php';
    createTask();
}
elseif (preg_match('#^/api/tasks/(\d+)$#', $uri, $matches) && $method === 'PUT') {
    require_once __DIR__ . '/routes/tasks.php';
    updateTask($matches[1]);
}
elseif (preg_match('#^/api/tasks/(\d+)/toggle-finish$#', $uri, $matches) && $method === 'PUT') {
    require_once __DIR__ . '/routes/tasks.php';
    toggleTaskFinish($matches[1]);
}
elseif (preg_match('#^/api/tasks/(\d+)$#', $uri, $matches) && $method === 'DELETE') {
    require_once __DIR__ . '/routes/tasks.php';
    deleteTask($matches[1]);
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