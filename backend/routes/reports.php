<?php
require_once __DIR__ . '/../controllers/ReportController.php';

function listProjectReports($projectId) {
    $controller = new ReportController();
    $controller->byProject($projectId);
}

function generateTaskReport($taskId) {
    $controller = new ReportController();
    $controller->generateTask($taskId);
}

function generateProjectReport($projectId) {
    $controller = new ReportController();
    $controller->generateProject($projectId);
}