<?php
require_once __DIR__ . '/../controllers/CommentController.php';

function getProjectComments($projectId) {
    $controller = new CommentController();
    $controller->getByProject($projectId);
}

function createProjectComment($projectId) {
    $controller = new CommentController();
    $controller->create($projectId);
}