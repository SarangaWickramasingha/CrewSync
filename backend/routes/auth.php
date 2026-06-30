<?php
require_once __DIR__ . '/../controllers/AuthController.php';

function login() {
    $controller = new AuthController();
    $controller->login();
}

function register() {
    $controller = new AuthController();
    $controller->register();
}
function me() {
    $controller = new AuthController();
    $controller->me();
}

function logout() {
    $controller = new AuthController();
    $controller->logout();
}