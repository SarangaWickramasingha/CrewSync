<?php
require_once __DIR__ . '/../controllers/NotificationController.php';

function getUserNotifications() {
    $controller = new NotificationController();
    $controller->getNotifications();
}

function createNotification() {
    $controller = new NotificationController();
    $controller->createNotification();
}

function markNotificationsRead() {
    $controller = new NotificationController();
    $controller->markRead();
}

function deleteNotification($notifId) {
    $controller = new NotificationController();
    $controller->deleteNotification($notifId);
}