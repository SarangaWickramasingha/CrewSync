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

function finishTask($taskId) {
    $controller = new TaskController();
    $controller->finish($taskId);
}

function saveTaskDailyStatus($taskId) {
    $controller = new TaskController();
    $controller->saveDailyStatus($taskId);
}

function getUnassignedTasks() {
    $controller = new TaskController();
    $controller->getUnassigned();
}

function deleteTask($taskId) {
    $controller = new TaskController();
    $controller->delete($taskId);
}