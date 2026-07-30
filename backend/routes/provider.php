<?php
require_once __DIR__ . '/../controllers/ProviderController.php';

function toggleProviderAvailability() {
    $controller = new ProviderController();
    $controller->toggleAvailability();
}
function getProviderAvailability() {
    $controller = new ProviderController();
    $controller->getAvailability();
}
function getProviderDashboardStats() {
    $controller = new ProviderController();
    $controller->getDashboardStats();
}
function getProviderCurrentWork() {
    $controller = new ProviderController();
    $controller->getCurrentWork();
}

function getProviderRecentReviews() {
    $controller = new ProviderController();
    $controller->getRecentReviews();
}
function getProviderJobRequests() {
    $controller = new ProviderController();
    $controller->getJobRequests();
}

function respondToProviderJobRequest($requestId) {
    $controller = new ProviderController();
    $controller->respondToJobRequest($requestId);
}
function getProviderTimeline() {
    $controller = new ProviderController();
    $controller->getTimeline();
}