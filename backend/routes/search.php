<?php
require_once __DIR__ . '/../controllers/SearchController.php';

function searchProviders() {
    $controller = new SearchController();
    $controller->searchProviders();
}
