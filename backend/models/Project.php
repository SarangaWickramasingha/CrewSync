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

        $query = "INSERT INTO projects (owner_id, project_name, district, address, p_budget, p_cost, start_date, estimate_date, is_finished)
                  VALUES (:owner_id, :project_name, :district, :address, :p_budget, 0.00, :start_date, :estimate_date, 0)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':owner_id', $ownerId);
        $stmt->bindParam(':project_name', $title);
        $stmt->bindParam(':district', $district);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':p_budget', $totalBudget);
        $stmt->bindParam(':start_date', $startDate);
        $stmt->bindParam(':estimate_date', $endDate); // target completion date

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function createTasks($projectId, $tasks, $taskBudgets = []) {
        if (!$this->conn || empty($tasks)) {
            return true;
        }

        $query = "INSERT INTO tasks (project_id, task_name, start_date, end_date, task_budget, t_cost, is_finished)
                  VALUES (:project_id, :task_name, NULL, NULL, :task_budget, 0.00, 0)";

        $stmt = $this->conn->prepare($query);

        foreach ($tasks as $taskName) {
            $budget = floatval($taskBudgets[$taskName] ?? 0);
            $stmt->bindValue(':project_id', $projectId, PDO::PARAM_INT);
            $stmt->bindValue(':task_name', $taskName, PDO::PARAM_STR);
            $stmt->bindValue(':task_budget', $budget);
            $stmt->execute();
        }
        return true;
    }
}
