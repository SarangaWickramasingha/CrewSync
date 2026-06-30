<?php
require_once __DIR__ . '/../controllers/TaskController.php';

function createTask() {
    $controller = new TaskController();
    $controller->create();
}

function updateTask($taskId) {
    $controller = new TaskController();
    $controller->update($taskId);
}

function toggleTaskFinish($taskId) {
    $controller = new TaskController();
    $controller->toggleFinish($taskId);
}

function deleteTask($taskId) {
    $controller = new TaskController();
    $controller->delete($taskId);
}