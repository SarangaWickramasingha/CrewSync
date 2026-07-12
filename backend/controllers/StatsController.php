<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Stats.php';

class StatsController {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getSummary() {
        $statsModel = new Stats($this->db);

        $summary = $statsModel->getSummary();

        echo json_encode([
            "success"   => true,
            "workers"   => $summary['workers'],
            "projects"  => $summary['projects'],
            "suppliers" => $summary['suppliers'],
            "avgSaved"  => $summary['avgSaved']
        ]);
    }
}