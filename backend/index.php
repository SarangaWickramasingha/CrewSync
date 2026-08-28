<?php

require_once __DIR__ . '/config/Env.php';
Env::load();
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

elseif ($uri === '/api/auth/send-otp' && $method === 'POST') {
    require_once __DIR__ . '/routes/auth.php';
    sendOtp();
}
elseif ($uri === '/api/auth/verify-otp' && $method === 'POST') {
    require_once __DIR__ . '/routes/auth.php';
    verifyOtp();
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
elseif ($uri === '/api/tasks/unassigned' && $method === 'GET') {
    require_once __DIR__ . '/routes/tasks.php';
    getUnassignedTasks();
}
elseif (preg_match('#^/api/tasks/(\d+)$#', $uri, $matches) && $method === 'PUT') {
    require_once __DIR__ . '/routes/tasks.php';
    updateTask($matches[1]);
}
elseif (preg_match('#^/api/tasks/(\d+)/toggle-finish$#', $uri, $matches) && $method === 'PUT') {
    require_once __DIR__ . '/routes/tasks.php';
    toggleTaskFinish($matches[1]);
}
elseif (preg_match('#^/api/tasks/(\d+)/daily-status$#', $uri, $matches) && $method === 'PUT') {
    require_once __DIR__ . '/routes/tasks.php';
    saveTaskDailyStatus($matches[1]);
}
elseif (preg_match('#^/api/tasks/(\d+)$#', $uri, $matches) && $method === 'DELETE') {
    require_once __DIR__ . '/routes/tasks.php';
    deleteTask($matches[1]);
}

// ── FEEDBACK ─────────────────────────────────────────────────────────────────
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

// ── REPORTS ───────────────────────────────────────────────────────────────────
elseif (preg_match('#^/api/reports/project/(\d+)$#', $uri, $matches) && $method === 'GET') {
    require_once __DIR__ . '/routes/reports.php';
    listProjectReports($matches[1]);
}
elseif (preg_match('#^/api/reports/task/(\d+)/generate$#', $uri, $matches) && $method === 'POST') {
    require_once __DIR__ . '/routes/reports.php';
    generateTaskReport($matches[1]);
}
elseif (preg_match('#^/api/reports/project/(\d+)/generate$#', $uri, $matches) && $method === 'POST') {
    require_once __DIR__ . '/routes/reports.php';
    generateProjectReport($matches[1]);
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

// ── SERVICE REQUESTS ─────────────────────────────────────────────────────────
elseif ($uri === '/api/service-requests' && $method === 'POST') {
    require_once __DIR__ . '/routes/service_requests.php';
    createServiceRequest();
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
elseif ($uri === '/api/provider/current-work' && $method === 'GET') {
    require_once __DIR__ . '/routes/provider.php';
    getProviderCurrentWork();
}
elseif ($uri === '/api/provider/recent-reviews' && $method === 'GET') {
    require_once __DIR__ . '/routes/provider.php';
    getProviderRecentReviews();
}
elseif ($uri === '/api/provider/job-requests' && $method === 'GET') {
    require_once __DIR__ . '/routes/provider.php';
    getProviderJobRequests();
}
elseif (preg_match('#^/api/provider/job-requests/(\d+)/respond$#', $uri, $matches) && $method === 'PUT') {
    require_once __DIR__ . '/routes/provider.php';
    respondToProviderJobRequest($matches[1]);
}
elseif ($uri === '/api/provider/timeline' && $method === 'GET') {
    require_once __DIR__ . '/routes/provider.php';
    getProviderTimeline();
}
elseif ($uri === '/api/provider/reviews/all' && $method === 'GET') {
    require_once __DIR__ . '/routes/provider.php';
    getProviderAllReviews();
}
elseif (preg_match('#^/api/reviews/(\d+)/photos$#', $uri, $matches) && $method === 'POST') {
    require_once __DIR__ . '/routes/provider.php';
    uploadProviderReviewPhotos($matches[1]);
}
elseif (preg_match('#^/api/review-photos/(\d+)$#', $uri, $matches) && $method === 'DELETE') {
    require_once __DIR__ . '/routes/provider.php';
    deleteProviderReviewPhoto($matches[1]);
}
elseif ($uri === '/api/provider/profile' && $method === 'GET') {
    require_once __DIR__ . '/routes/provider.php';
    getProviderProfile();
}
elseif ($uri === '/api/provider/profile' && $method === 'PUT') {
    require_once __DIR__ . '/routes/provider.php';
    updateProviderPersonalInfo();
}
elseif ($uri === '/api/provider/skills' && $method === 'POST') {
    require_once __DIR__ . '/routes/provider.php';
    upsertProviderSkill();
}
elseif (preg_match('#^/api/provider/skills/(\d+)$#', $uri, $matches) && $method === 'DELETE') {
    require_once __DIR__ . '/routes/provider.php';
    removeProviderSkill($matches[1]);
}


// ── SEARCH ────────────────────────────────────────────────────────────────────
elseif ($uri === '/api/search/providers' && $method === 'GET') {
    require_once __DIR__ . '/routes/search.php';
    searchProviders();
}
elseif ($uri === '/api/search/materials' && $method === 'GET') {
    require_once __DIR__ . '/routes/search.php';
    searchMaterials();
}

// ── PUBLIC PROVIDER PROFILE ──────────────────────────────────────────────────
elseif (preg_match('#^/api/providers/(\d+)$#', $uri, $matches) && $method === 'GET') {
    require_once __DIR__ . '/routes/provider.php';
    getPublicProviderProfile($matches[1]);
}

// ── MATERIAL SUPPLIER ─────────────────────────────────────────────────────────
elseif ($uri === '/api/supplier/products' && $method === 'GET') {
    require_once __DIR__ . '/routes/supplier.php';
    getSupplierProducts();
}
elseif ($uri === '/api/supplier/products' && $method === 'POST') {
    require_once __DIR__ . '/routes/supplier.php';
    upsertSupplierProduct();
}
elseif (preg_match('#^/api/supplier/products/(\d+)$#', $uri, $matches) && $method === 'DELETE') {
    require_once __DIR__ . '/routes/supplier.php';
    removeSupplierProduct($matches[1]);
}
elseif ($uri === '/api/supplier/orders' && $method === 'GET') {
    require_once __DIR__ . '/routes/supplier.php';
    getSupplierOrders();
}
elseif (preg_match('#^/api/supplier/orders/(\d+)/status$#', $uri, $matches) && $method === 'PUT') {
    require_once __DIR__ . '/routes/supplier.php';
    updateSupplierOrderStatus($matches[1]);
}
elseif ($uri === '/api/supplier/profile' && $method === 'GET') {
    require_once __DIR__ . '/routes/supplier.php';
    getSupplierProfile();
}
elseif ($uri === '/api/supplier/profile' && $method === 'PUT') {
    require_once __DIR__ . '/routes/supplier.php';
    updateSupplierProfile();
}

// ── NOTIFICATIONS ────────────────────────────────────────────────────────────
elseif ($uri === '/api/notifications' && $method === 'GET') {
    require_once __DIR__ . '/routes/notifications.php';
    getUserNotifications();
}
elseif ($uri === '/api/notifications' && $method === 'POST') {
    require_once __DIR__ . '/routes/notifications.php';
    createNotification();
}
elseif ($uri === '/api/notifications/read' && $method === 'PUT') {
    require_once __DIR__ . '/routes/notifications.php';
    markNotificationsRead();
}
elseif (preg_match('#^/api/notifications/(\d+)$#', $uri, $matches) && $method === 'DELETE') {
    require_once __DIR__ . '/routes/notifications.php';
    deleteNotification($matches[1]);
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

function sendOtp() {
    $controller = new AuthController();
    $controller->sendOtp();
}

function verifyOtp() {
    $controller = new AuthController();
    $controller->verifyOtp();
}