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