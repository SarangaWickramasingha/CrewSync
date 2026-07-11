<?php
require_once __DIR__ . '/../controllers/ProjectController.php';

function getAllProjects() {
    $controller = new ProjectController();
    $controller->getAll();
}

function getOneProject($projectId) {
    $controller = new ProjectController();
    $controller->getOne($projectId);
}

function toggleProjectFinish($projectId) {
    $controller = new ProjectController();
    $controller->toggleFinish($projectId);
}
function createProject() {
    $controller = new ProjectController();
    $controller->create();
}
