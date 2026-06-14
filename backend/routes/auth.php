<?php

require_once __DIR__ . '/../controllers/AuthController.php';

function login() {
    $controller = new AuthController();
    $controller->login();
}