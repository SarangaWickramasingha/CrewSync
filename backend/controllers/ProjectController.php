<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Project.php';

class ProjectController {

    public function create() {
        $database = new Database();
        $db = $database->connect();
        if (!$db) {
            echo json_encode([
                "success" => false,
                "message" => "Database connection failed"
            ]);
            return;
        }

        $projectModel = new Project($db);

        $data = json_decode(
            file_get_contents("php://input"),
            true
        ) ?? [];

        $title    = trim($data['title'] ?? '');
        $totalBudget = floatval($data['total_budget'] ?? 0);
        $startDate   = trim($data['start_date'] ?? '');
        $endDate     = trim($data['end_date'] ?? '');
        $status      = trim($data['status'] ?? 'planning');
        $district    = trim($data['district'] ?? '');
        $address     = trim($data['address'] ?? '');
        $tasks       = $data['tasks'] ?? [];
        $taskBudgets = $data['task_budgets'] ?? [];

        if (!$title || !$startDate || !$endDate || $totalBudget <= 0 || !$district || !$address) {
            echo json_encode([
                "success" => false,
                "message" => "Title, budget, start date, end date, district, and address are required"
            ]);
            return;
        }

        // Get an owner_id to associate with the project
        try {
            $stmt = $db->query("SELECT user_id FROM users WHERE role = 'property_owner' LIMIT 1");
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $ownerId = $user['user_id'];
            } else {
                $insertStmt = $db->prepare("INSERT INTO users (email, password_hash, role, fname, lname, contact_no, district)
                                             VALUES ('owner@crewsync.com', 'dummy_hash', 'property_owner', 'System', 'Owner', '0771234567', 'Colombo')");
                $insertStmt->execute();
                $ownerId = $db->lastInsertId();
            }
        } catch (PDOException $e) {
            echo json_encode([
                "success" => false,
                "message" => "Database error finding or creating owner: " . $e->getMessage()
            ]);
            return;
        }

        $projectId = $projectModel->create($ownerId, $title, $status, $totalBudget, $startDate, $endDate, $district, $address);

        if (!$projectId) {
            echo json_encode([
                "success" => false,
                "message" => "Failed to create project"
            ]);
            return;
        }

        if (!empty($tasks)) {
            $projectModel->createTasks($projectId, $tasks, $taskBudgets);
        }

        echo json_encode([
            "success" => true,
            "message" => "Project and tasks created successfully",
            "project_id" => $projectId
        ]);
    }

    public function getProject($id) {
        $database = new Database();
        $db = $database->connect();
        if (!$db) {
            echo json_encode(["success" => false, "message" => "Database connection failed"]);
            return;
        }

        try {
            $stmt = $db->prepare("SELECT * FROM projects WHERE project_id = :id");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $project = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$project) {
                echo json_encode(["success" => false, "message" => "Project not found"]);
                return;
            }

            // Map project fields to what frontend expects
            $project['title'] = $project['project_name'];
            $project['total_budget'] = $project['p_budget'];
            $project['actual_cost'] = $project['p_cost'];
            $project['status'] = $project['is_finished'] ? 'completed' : 'ongoing';

            $stmt2 = $db->prepare("SELECT * FROM tasks WHERE project_id = :id ORDER BY task_id ASC");
            $stmt2->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt2->execute();
            $dbTasks = $stmt2->fetchAll(PDO::FETCH_ASSOC);

            $tasks = [];
            foreach ($dbTasks as $t) {
                // Map tasks fields for frontend
                $t['status'] = $t['is_finished'] ? 'completed' : 'pending';
                $tasks[] = $t;
            }

            echo json_encode([
                "success" => true,
                "project" => $project,
                "tasks" => $tasks
            ]);
        } catch (PDOException $e) {
            echo json_encode(["success" => false, "message" => $e->getMessage()]);
        }
    }

    public function updateStatus($id) {
        $database = new Database();
        $db = $database->connect();
        if (!$db) {
            echo json_encode(["success" => false, "message" => "Database connection failed"]);
            return;
        }

        $data   = json_decode(file_get_contents("php://input"), true) ?? [];
        $status = trim($data['status'] ?? '');

        $allowed = ['planning', 'ongoing', 'completed', 'paused'];
        if (!in_array($status, $allowed)) {
            echo json_encode(["success" => false, "message" => "Invalid status. Allowed: " . implode(', ', $allowed)]);
            return;
        }

        $isFinished = ($status === 'completed') ? 1 : 0;

        try {
            if ($isFinished) {
                $stmt = $db->prepare("UPDATE projects SET is_finished = 1, end_date = CURRENT_DATE() WHERE project_id = :id");
            } else {
                $stmt = $db->prepare("UPDATE projects SET is_finished = 0, end_date = NULL WHERE project_id = :id");
            }
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            echo json_encode(["success" => true, "message" => "Project status updated to '$status'"]);
        } catch (PDOException $e) {
            echo json_encode(["success" => false, "message" => $e->getMessage()]);
        }
    }
}
