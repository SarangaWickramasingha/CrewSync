<?php

require_once __DIR__ . '/../controllers/ProjectController.php';

function createProjectRoute() {
    $controller = new ProjectController();
    $controller->create();
}

function getProjectRoute($id) {
    $controller = new ProjectController();
    $controller->getProject($id);
}

function updateProjectStatusRoute($id) {
    $controller = new ProjectController();
    $controller->updateStatus($id);
}
