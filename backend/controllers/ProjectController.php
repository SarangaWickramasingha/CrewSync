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

        // Ensure database tables exist (projects and tasks)
        try {
            $db->exec("CREATE TABLE IF NOT EXISTS projects (
                project_id INT AUTO_INCREMENT PRIMARY KEY,
                owner_id INT,
                title VARCHAR(255) NOT NULL,
                status ENUM('planning','ongoing','completed','paused') DEFAULT 'planning',
                total_budget DECIMAL(12,2) NOT NULL,
                actual_cost DECIMAL(12,2) DEFAULT 0.00,
                start_date DATE,
                end_date DATE,
                district VARCHAR(100),
                address VARCHAR(255),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (owner_id) REFERENCES users(user_id) ON DELETE CASCADE
            )");

            $db->exec("CREATE TABLE IF NOT EXISTS tasks (
                task_id INT AUTO_INCREMENT PRIMARY KEY,
                project_id INT,
                task_name VARCHAR(255) NOT NULL,
                description TEXT,
                status ENUM('pending','in_progress','completed') DEFAULT 'pending',
                priority ENUM('low','medium','high') DEFAULT 'medium',
                start_date DATE,
                end_date DATE,
                estimated_cost DECIMAL(12,2) DEFAULT 0.00,
                actual_cost DECIMAL(12,2) DEFAULT 0.00,
                sequence_order INT,
                FOREIGN KEY (project_id) REFERENCES projects(project_id) ON DELETE CASCADE
            )");
        } catch (PDOException $e) {
            echo json_encode([
                "success" => false,
                "message" => "Database table setup failed: " . $e->getMessage()
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
            $projectModel->createTasks($projectId, $tasks);
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

            $stmt2 = $db->prepare("SELECT * FROM tasks WHERE project_id = :id ORDER BY sequence_order ASC");
            $stmt2->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt2->execute();
            $tasks = $stmt2->fetchAll(PDO::FETCH_ASSOC);

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

        try {
            $stmt = $db->prepare("UPDATE projects SET status = :status WHERE project_id = :id");
            $stmt->bindValue(':status', $status);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            echo json_encode(["success" => true, "message" => "Project status updated to '$status'"]);
        } catch (PDOException $e) {
            echo json_encode(["success" => false, "message" => $e->getMessage()]);
        }
    }
}
