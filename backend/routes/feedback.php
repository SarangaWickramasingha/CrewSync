<?php

require_once __DIR__ . '/../controllers/FeedbackController.php';

function submitFeedback() {
    $controller = new FeedbackController();
    $controller->submit();
}

function listFeedback() {
    $controller = new FeedbackController();
    $controller->list();
}

function updateFeedbackStatus() {
    $controller = new FeedbackController();
    $controller->updateStatus();
}