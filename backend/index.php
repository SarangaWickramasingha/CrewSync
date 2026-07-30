<?php

require_once __DIR__ . '/config/cors.php';
require_once __DIR__ . '/config/database.php';

// Get request URI
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Remove folder path (adjust if needed)
$uri = str_replace('/CrewSync/backend/index.php', '', $uri);
$uri = str_replace('/CrewSync-backend/backend/index.php', '', $uri);

// Debug mode — only runs if you add ?debug=1 to the URL
if (isset($_GET['debug'])) {
    echo json_encode(["debug_uri" => $uri, "method" => $method]);
    exit();
}


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
elseif ($uri === '/api/projects/create' && $method === 'POST') {
    require_once __DIR__ . '/routes/projects.php';
    createProject();
}
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

// ── FEEDBACK (PUBLIC / GENERAL) ──────────────────────────────────────────────
elseif ($uri === '/api/feedback/submit' && $method === 'POST') {
    require_once __DIR__ . '/routes/feedback.php';
    submitFeedback();
}
elseif ($uri === '/api/feedback' && $method === 'GET') {
    require_once __DIR__ . '/routes/feedback.php';
    listFeedback();
}
elseif ($uri === '/api/feedback/status' && $method === 'PUT') {
    require_once __DIR__ . '/routes/feedback.php';
    updateFeedbackStatus();
}

// ── STATS ────────────────────────────────────────────────────────────────────
elseif ($uri === '/api/stats/summary' && $method === 'GET') {
    require_once __DIR__ . '/routes/stats.php';
    getStatsSummary();
}

// ── COMMENTS ─────────────────────────────────────────────────────────────────
elseif (preg_match('#^/api/projects/(\d+)/comments$#', $uri, $matches) && $method === 'GET') {
    require_once __DIR__ . '/routes/comments.php';
    getProjectComments((int)$matches[1]);
}
elseif (preg_match('#^/api/projects/(\d+)/comments$#', $uri, $matches) && $method === 'POST') {
    require_once __DIR__ . '/routes/comments.php';
    createProjectComment((int)$matches[1]);
}


// ── ADMIN ────────────────────────────────────────────────────────────────────
elseif ($uri === '/api/admin/stats' && $method === 'GET') {
    require_once __DIR__ . '/routes/admin.php';
    adminGetStats();
}
elseif ($uri === '/api/admin/users' && $method === 'GET') {
    require_once __DIR__ . '/routes/admin.php';
    adminGetAllUsers();
}
elseif ($uri === '/api/admin/users' && $method === 'POST') {
    require_once __DIR__ . '/routes/admin.php';
    adminCreateUser();
}
elseif (preg_match('#^/api/admin/users/(\d+)$#', $uri, $matches) && $method === 'GET') {
    require_once __DIR__ . '/routes/admin.php';
    adminGetUserById($matches[1]);
}
elseif (preg_match('#^/api/admin/users/(\d+)$#', $uri, $matches) && $method === 'PUT') {
    require_once __DIR__ . '/routes/admin.php';
    adminUpdateUser($matches[1]);
}
elseif (preg_match('#^/api/admin/users/(\d+)$#', $uri, $matches) && $method === 'DELETE') {
    require_once __DIR__ . '/routes/admin.php';
    adminDeleteUser($matches[1]);
}
elseif ($uri === '/api/admin/users/property-owners' && $method === 'GET') {
    require_once __DIR__ . '/routes/admin.php';
    adminGetPropertyOwners();
}
elseif ($uri === '/api/admin/users/service-providers' && $method === 'GET') {
    require_once __DIR__ . '/routes/admin.php';
    adminGetServiceProviders();
}
elseif ($uri === '/api/admin/users/material-suppliers' && $method === 'GET') {
    require_once __DIR__ . '/routes/admin.php';
    adminGetMaterialSuppliers();
}
elseif ($uri === '/api/admin/reviews' && $method === 'GET') {
    require_once __DIR__ . '/routes/admin.php';
    adminGetAllReviews();
}
elseif (preg_match('#^/api/admin/reviews/(\d+)$#', $uri, $matches) && $method === 'DELETE') {
    require_once __DIR__ . '/routes/admin.php';
    adminDeleteReview($matches[1]);
}
elseif ($uri === '/api/admin/feedback' && $method === 'GET') {
    require_once __DIR__ . '/routes/admin.php';
    adminGetAllFeedback();
}
elseif (preg_match('#^/api/admin/feedback/(\d+)$#', $uri, $matches) && $method === 'PUT') {
    require_once __DIR__ . '/routes/admin.php';
    adminUpdateFeedback($matches[1]);
}
elseif ($uri === '/api/admin/projects' && $method === 'GET') {
    require_once __DIR__ . '/routes/admin.php';
    adminGetAllProjects();
}
elseif (preg_match('#^/api/admin/projects/(\d+)$#', $uri, $matches) && $method === 'GET') {
    require_once __DIR__ . '/routes/admin.php';
    adminGetProjectWithTasks($matches[1]);
}


// ── SERVICE PROVIDER ─────────────────────────────────────────────────────────
elseif ($uri === '/api/provider/toggle-availability' && $method === 'PUT') {
    require_once __DIR__ . '/routes/provider.php';
    toggleProviderAvailability();
}
elseif ($uri === '/api/provider/availability' && $method === 'GET') {
    require_once __DIR__ . '/routes/provider.php';
    getProviderAvailability();
}
elseif ($uri === '/api/provider/dashboard-stats' && $method === 'GET') {
    require_once __DIR__ . '/routes/provider.php';
    getProviderDashboardStats();
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