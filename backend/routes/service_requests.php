<?php
require_once __DIR__ . '/../controllers/ServiceRequestController.php';

function createServiceRequest() {
    $controller = new ServiceRequestController();
    $controller->create();
}
