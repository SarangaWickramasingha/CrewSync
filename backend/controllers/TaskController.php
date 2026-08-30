<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/auth.php';

class TaskController {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // ── HELPER: confirm this task belongs to the logged-in owner ─────────────
    private function getOwnedTask($taskId, $userId) {
        $stmt = $this->db->prepare("
            SELECT t.*
            FROM tasks t
            JOIN projects p ON p.project_id = t.project_id
            JOIN property_owners po ON po.owner_id = p.owner_id
            WHERE po.user_id = ? AND t.task_id = ?
        ");
        $stmt->execute([$userId, $taskId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ── ADD TASK ───────────────────────────────────────────────────────────────
    public function create() {
        $user = requireRole('property_owner');
        $data = json_decode(file_get_contents("php://input"), true);

        $projectId = $data['project_id'] ?? null;
        $taskName  = trim($data['task_name'] ?? '');

        if (!$projectId || !$taskName) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "project_id and task_name are required"]);
            return;
        }

        // Confirm the project belongs to this owner
        $stmt = $this->db->prepare("
            SELECT p.project_id
            FROM projects p
            JOIN property_owners po ON po.owner_id = p.owner_id
            WHERE po.user_id = ? AND p.project_id = ?
        ");
        $stmt->execute([$user['user_id'], $projectId]);
        if (!$stmt->fetch()) {
            http_response_code(403);
            echo json_encode(["success" => false, "message" => "Project not found or access denied"]);
            return;
        }

        $stmt = $this->db->prepare("
            INSERT INTO tasks (project_id, task_name, task_budget, t_cost, is_finished)
            VALUES (?, ?, ?, 0, 0)
        ");
        $stmt->execute([
            $projectId,
            $taskName,
            $data['task_budget'] ?? null
        ]);

        echo json_encode([
            "success" => true,
            "task_id" => $this->db->lastInsertId()
        ]);
    }

    // ── UPDATE TASK (name, cost, budget) ──────────────────────────────────────
    public function update($taskId) {
        $user = requireRole('property_owner');
        $task = $this->getOwnedTask($taskId, $user['user_id']);

        if (!$task) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Task not found"]);
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true);

        // Build dynamic update — only update fields that were sent
        $fields = [];
        $values = [];

        if (isset($data['task_name'])) {
            $fields[] = "task_name = ?";
            $values[] = $data['task_name'];
        }
        if (isset($data['add_cost'])) {
            // "Add Cost" adds to existing t_cost, doesn't replace it
            $fields[] = "t_cost = t_cost + ?";
            $values[] = $data['add_cost'];
        }
        if (isset($data['task_budget'])) {
            $fields[] = "task_budget = ?";
            $values[] = $data['task_budget'];
        }

        if (empty($fields)) {
            echo json_encode(["success" => false, "message" => "Nothing to update"]);
            return;
        }

        $values[] = $taskId;
        $sql = "UPDATE tasks SET " . implode(', ', $fields) . " WHERE task_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($values);

        echo json_encode(["success" => true]);
    }

    // ── FINISH TASK (permanent — no unfreeze) ─────────────────────────────────
    public function finish($taskId) {
        $user = requireRole('property_owner');
        $task = $this->getOwnedTask($taskId, $user['user_id']);

        if (!$task) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Task not found"]);
            return;
        }

        $stmt = $this->db->prepare("UPDATE tasks SET is_finished = 1 WHERE task_id = ?");
        $stmt->execute([$taskId]);

        echo json_encode([
            "success" => true,
            "is_finished" => true
        ]);
    }

    // ── SAVE DAILY STATUS (upsert into task_daily_status) ─────────────────────
    // status eka save karana fun eka eka eka task ekata
    public function saveDailyStatus($taskId) {
        $user = requireRole('property_owner');
        $task = $this->getOwnedTask($taskId, $user['user_id']);

        if (!$task) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Task not found"]);
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true);
        $statuses = $data['statuses'] ?? null;

        if (!is_array($statuses) || empty($statuses)) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "statuses array is required"]);
            return;
        }

        $validStatuses = ['not_started', 'in_progress', 'done', 'blocked'];
        $stmt = $this->db->prepare("
            INSERT INTO task_daily_status (task_id, status_date, status)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE status = VALUES(status)
        ");

        $saved = 0;
        foreach ($statuses as $entry) {
            $date = $entry['date'] ?? null;
            $status = $entry['status'] ?? null;

            if (!is_string($date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) continue;
            if (!in_array($status, $validStatuses, true)) continue;

            $stmt->execute([$taskId, $date, $status]);
            $saved++;
        }

        echo json_encode(["success" => true, "saved" => $saved]);
    }

    // ── GET UNASSIGNED TASKS (across all the owner's projects) ───────────────
    // Returns every task that has no provider assigned in task_assignments.
    public function getUnassigned() {
        $user = requireRole('property_owner');

        $stmt = $this->db->prepare("SELECT owner_id FROM property_owners WHERE user_id = ?");
        $stmt->execute([$user['user_id']]);
        $owner = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$owner) {
            http_response_code(403);
            echo json_encode(["success" => false, "message" => "Property owner profile not found"]);
            return;
        }

        $stmt = $this->db->prepare("
            SELECT t.task_id, t.task_name, t.is_finished,
                   p.project_id, p.project_name
            FROM tasks t
            JOIN projects p ON p.project_id = t.project_id
            WHERE p.owner_id = ?
              AND t.is_finished = 0
              AND NOT EXISTS (
                  SELECT 1 FROM task_assignments ta WHERE ta.task_id = t.task_id
              )
            ORDER BY p.project_id ASC, t.task_id ASC
        ");
        $stmt->execute([$owner['owner_id']]);
        $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            "success" => true,
            "tasks"   => $tasks,
        ]);
    }

    // ── DELETE TASK ────────────────────────────────────────────────────────────
    public function delete($taskId) {
        $user = requireRole('property_owner');
        $task = $this->getOwnedTask($taskId, $user['user_id']);

        if (!$task) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Task not found"]);
            return;
        }

        $stmt = $this->db->prepare("DELETE FROM tasks WHERE task_id = ?");
        $stmt->execute([$taskId]);

        echo json_encode(["success" => true]);
    }
}