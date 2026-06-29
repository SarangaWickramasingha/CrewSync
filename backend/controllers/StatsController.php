<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Stats.php';

class StatsController {

    public function getSummary() {
        $database = new Database();
        $db = $database->connect();
        $statsModel = new Stats($db);

        $summary = $statsModel->getSummary();

        echo json_encode([
            "success" => true,
            "workers" => $summary['workers'],
            "projects" => $summary['projects'],
            "suppliers" => $summary['suppliers'],
            "avgSaved" => $summary['avgSaved']
        ]);
    }
}
