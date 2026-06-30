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

        // Add a "status" field derived from is_finished — frontend expects this
        foreach ($projects as &$p) {
            $p['status'] = $p['is_finished'] ? 'completed' : 'ongoing';
        }

        echo json_encode([
            "success" => true,
            "projects" => $projects
        ]);
    }

    // ── GET SINGLE PROJECT + ITS TASKS ────────────────────────────────────────
    public function getOne($projectId) {
        $user = requireRole('property_owner');

        // Confirm this project belongs to the logged-in owner
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

        // Get tasks for this project
        $stmt = $this->db->prepare("
            SELECT * FROM tasks WHERE project_id = ? ORDER BY start_date ASC
        ");
        $stmt->execute([$projectId]);
        $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Add "status" field for each task too
        foreach ($tasks as &$t) {
            $t['status'] = $t['is_finished'] ? 'completed' : 'ongoing';
        }

        echo json_encode([
            "success" => true,
            "project" => $project,
            "tasks" => $tasks
        ]);
    }

    // ── TOGGLE PROJECT FINISH STATUS ──────────────────────────────────────────
    public function toggleFinish($projectId) {
        $user = requireRole('property_owner');

        // Confirm ownership + get current state
        $stmt = $this->db->prepare("
            SELECT p.is_finished
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

        // Flip the boolean
        $newValue = $project['is_finished'] ? 0 : 1;

        $stmt = $this->db->prepare("UPDATE projects SET is_finished = ? WHERE project_id = ?");
        $stmt->execute([$newValue, $projectId]);

        echo json_encode([
            "success" => true,
            "is_finished" => (bool) $newValue
        ]);
    }
}