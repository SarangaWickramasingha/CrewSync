<?php

require_once __DIR__ . '/../controllers/StatsController.php';

function getStatsSummary() {
    $controller = new StatsController();
    $controller->getSummary();
}
