<?php

class Stats {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getSummary() {
        if (!$this->conn) {
            return [
                'workers' => 0,
                'projects' => 0,
                'suppliers' => 0,
                'avgSaved' => 0
            ];
        }

        try {
            // Count workers (role = 'service_provider')
            $q1 = "SELECT COUNT(*) AS total FROM users WHERE role = 'service_provider'";
            $stmt1 = $this->conn->prepare($q1);
            $stmt1->execute();
            $r1 = $stmt1->fetch(PDO::FETCH_ASSOC);
            $workers = (int)($r1['total'] ?? 0);

            // Count projects
            $q2 = "SELECT COUNT(*) AS total FROM projects";
            $stmt2 = $this->conn->prepare($q2);
            $stmt2->execute();
            $r2 = $stmt2->fetch(PDO::FETCH_ASSOC);
            $projects = (int)($r2['total'] ?? 0);

            // Count suppliers (role = 'material_supplier')
            $q3 = "SELECT COUNT(*) AS total FROM users WHERE role = 'material_supplier'";
            $stmt3 = $this->conn->prepare($q3);
            $stmt3->execute();
            $r3 = $stmt3->fetch(PDO::FETCH_ASSOC);
            $suppliers = (int)($r3['total'] ?? 0);

            // Average saved (p_budget - p_cost)
            $q4 = "SELECT COALESCE(AVG(p_budget - p_cost), 0) AS avg_saved 
                   FROM projects 
                   WHERE p_cost > 0 AND p_budget > p_cost";
            $stmt4 = $this->conn->prepare($q4);
            $stmt4->execute();
            $r4 = $stmt4->fetch(PDO::FETCH_ASSOC);
            $avgSaved = (float)($r4['avg_saved'] ?? 0);

            return [
                'workers' => $workers,
                'projects' => $projects,
                'suppliers' => $suppliers,
                'avgSaved' => $avgSaved
            ];
        } catch (PDOException $e) {
            error_log("Error fetching stats: " . $e->getMessage());
            return [
                'workers' => 0,
                'projects' => 0,
                'suppliers' => 0,
                'avgSaved' => 0
            ];
        }
    }
}
