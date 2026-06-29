<?php

class Project {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($ownerId, $title, $status, $totalBudget, $startDate, $endDate, $district, $address) {
        if (!$this->conn) {
            return false;
        }

        $query = "INSERT INTO projects (owner_id, title, status, total_budget, actual_cost, start_date, end_date, district, address)
                  VALUES (:owner_id, :title, :status, :total_budget, 0.00, :start_date, :end_date, :district, :address)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':owner_id', $ownerId);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':total_budget', $totalBudget);
        $stmt->bindParam(':start_date', $startDate);
        $stmt->bindParam(':end_date', $endDate);
        $stmt->bindParam(':district', $district);
        $stmt->bindParam(':address', $address);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function createTasks($projectId, $tasks) {
        if (!$this->conn || empty($tasks)) {
            return true;
        }

        $query = "INSERT INTO tasks (project_id, task_name, description, status, priority, start_date, end_date, estimated_cost, actual_cost, sequence_order)
                  VALUES (:project_id, :task_name, :description, 'pending', 'medium', NULL, NULL, 0.00, 0.00, :sequence_order)";

        $stmt = $this->conn->prepare($query);

        $seq = 1;
        foreach ($tasks as $taskName) {
            $desc = "Task for phase: " . $taskName;
            $stmt->bindValue(':project_id', $projectId, PDO::PARAM_INT);
            $stmt->bindValue(':task_name', $taskName, PDO::PARAM_STR);
            $stmt->bindValue(':description', $desc, PDO::PARAM_STR);
            $stmt->bindValue(':sequence_order', $seq, PDO::PARAM_INT);
            $stmt->execute();
            $seq++;
        }
        return true;
    }
}
