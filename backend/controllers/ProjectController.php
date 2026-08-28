<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/auth.php';

class ProjectController {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // ── GET ALL PROJECTS FOR LOGGED-IN OWNER ──────────────────────────────────
    public function getAll() {
        $user = requireRole('property_owner');

        $stmt = $this->db->prepare("
            SELECT p.*
            FROM projects p
            JOIN property_owners po ON po.owner_id = p.owner_id
            WHERE po.user_id = ?
            ORDER BY p.start_date DESC
        ");
        $stmt->execute([$user['user_id']]);
        $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($projects as &$p) {
            $p['status'] = $p['is_finished'] ? 'completed' : 'ongoing';
        }

        echo json_encode([
            "success"  => true,
            "projects" => $projects
        ]);
    }

    // ── GET SINGLE PROJECT + ITS TASKS ────────────────────────────────────────
    public function getOne($projectId) {
        $user = requireRole('property_owner');

        $stmt = $this->db->prepare("
            SELECT p.*
            FROM projects p
            JOIN property_owners po ON po.owner_id = p.owner_id
            WHERE po.user_id = ? AND p.project_id = ?
        ");
        $stmt->execute([$user['user_id'], $projectId]);
        $project = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$project) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Project not found"]);
            return;
        }

        $project['status'] = $project['is_finished'] ? 'completed' : 'ongoing';

        $stmt = $this->db->prepare("
            SELECT * FROM tasks WHERE project_id = ? ORDER BY task_id ASC
        ");
        $stmt->execute([$projectId]);
        $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Attach saved daily statuses from task_daily_status
        // methan status eka save karana ewa load karanwa
        $taskIds = array_column($tasks, 'task_id');
        $statusesByTask = [];
        if (!empty($taskIds)) {
            $in = implode(',', array_fill(0, count($taskIds), '?'));
            $stmt = $this->db->prepare("
                SELECT task_id, status_date, status
                FROM task_daily_status
                WHERE task_id IN ($in)
                ORDER BY status_date ASC
            ");
            $stmt->execute($taskIds);
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $s) {
                $statusesByTask[$s['task_id']][] = [
                    "date"   => $s['status_date'],
                    "status" => $s['status']
                ];
            }
        }

        foreach ($tasks as &$t) {
            $t['status'] = $t['is_finished'] ? 'completed' : 'ongoing';
            $t['daily_statuses'] = $statusesByTask[$t['task_id']] ?? [];
        }
        unset($t);

        echo json_encode([
            "success" => true,
            "project" => $project,
            "tasks"   => $tasks
        ]);
    }

    // ── CREATE PROJECT + TASKS ────────────────────────────────────────────────
    public function create() {
        $user = requireRole('property_owner');
        
        $data = json_decode(file_get_contents("php://input"), true) ?? [];

        $projectName   = trim($data['title'] ?? '');
        $totalBudget   = floatval($data['total_budget'] ?? 0);
        $startDate     = trim($data['start_date'] ?? '');
        $targetEndDate = trim($data['target_end_date'] ?? '');
        $district      = trim($data['district'] ?? '');
        $address       = trim($data['address'] ?? '');
        $tasks         = $data['tasks'] ?? [];
        $taskBudgets   = $data['task_budgets'] ?? [];

        if (!$projectName || !$startDate || !$targetEndDate || $totalBudget <= 0 || !$district || !$address) {
            http_response_code(400);
            echo json_encode([
                "success" => false,
                "message" => "Project name, budget, start date, target end date, district, and address are required"
            ]);
            return;
        }

        // Get owner_id from property_owners table using user_id from JWT
        $stmt = $this->db->prepare("
            SELECT owner_id FROM property_owners WHERE user_id = ?
        ");
        $stmt->execute([$user['user_id']]);
        $owner = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$owner) {
            http_response_code(403);
            echo json_encode(["success" => false, "message" => "Property owner profile not found"]);
            return;
        }

        $ownerId = $owner['owner_id'];

        // Insert project
        $stmt = $this->db->prepare("
            INSERT INTO projects 
                (owner_id, project_name, district, address, p_budget, p_cost, start_date, end_date, target_end_date, is_finished)
            VALUES 
                (?, ?, ?, ?, ?, 0, ?, NULL, ?, 0)
        ");
        $stmt->execute([
            $ownerId,
            $projectName,
            $district,
            $address,
            $totalBudget,
            $startDate,
            $targetEndDate,
        ]);

        $projectId = $this->db->lastInsertId();

        if (!$projectId) {
            http_response_code(500);
            echo json_encode(["success" => false, "message" => "Failed to create project"]);
            return;
        }

        // Insert tasks with per-task budgets
        if (!empty($tasks)) {
            $stmt = $this->db->prepare("
                INSERT INTO tasks (project_id, task_name, task_budget, t_cost, is_finished)
                VALUES (?, ?, ?, 0, 0)
            ");
            foreach ($tasks as $taskName) {
                $taskBudget = floatval($taskBudgets[$taskName] ?? 0);
                $stmt->execute([$projectId, $taskName, $taskBudget]);
            }
        }

        echo json_encode([
            "success"    => true,
            "message"    => "Project created successfully",
            "project_id" => $projectId
        ]);
    }

    // ── TOGGLE PROJECT FINISH STATUS ──────────────────────────────────────────
    public function toggleFinish($projectId) {
        $user = requireRole('property_owner');



        $stmt = $this->db->prepare("
            SELECT p.is_finished, p.project_name
            FROM projects p
            JOIN property_owners po ON po.owner_id = p.owner_id
            WHERE po.user_id = ? AND p.project_id = ?
        ");
        $stmt->execute([$user['user_id'], $projectId]);
        $project = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$project) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Project not found"]);
            return;
        }

        $newValue = $project['is_finished'] ? 0 : 1;

        $stmt = $this->db->prepare("UPDATE projects SET is_finished = ? WHERE project_id = ?");
        $stmt->execute([$newValue, $projectId]);

        echo json_encode([
            "success"     => true,
            "is_finished" => (bool) $newValue
        ]);
    }
}
