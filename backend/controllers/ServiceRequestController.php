<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/auth.php';

class ServiceRequestController {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // ── CREATE SERVICE REQUEST(S) ────────────────────────────────────────────
    // Body: { "provider_id": 1, "task_id": 5 }  or  { "task_id": [5, 6] }
    // Requests default to 'pending' and expire 72 hours from now (see the
    // MySQL event `expire_service_requests` which flips them to 'expired').
    public function create() {
        $user = requireRole('property_owner');

        $stmt = $this->db->prepare("SELECT owner_id FROM property_owners WHERE user_id = ?");
        $stmt->execute([$user['user_id']]);
        $owner = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$owner) {
            http_response_code(403);
            echo json_encode(["success" => false, "message" => "Property owner profile not found"]);
            return;
        }

        $ownerId = $owner['owner_id'];

        $data = json_decode(file_get_contents("php://input"), true) ?? [];

        $providerId = intval($data['provider_id'] ?? 0);
        $taskIds = $data['task_id'] ?? null;

        if (is_numeric($taskIds)) {
            $taskIds = [(int) $taskIds];
        }

        if (!is_array($taskIds) || empty($taskIds)) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "task_id is required"]);
            return;
        }

        $taskIds = array_values(array_unique(array_map('intval', $taskIds)));

        if (!$providerId) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "provider_id is required"]);
            return;
        }

        // Validate provider exists
        $stmt = $this->db->prepare("SELECT provider_id FROM service_providers WHERE provider_id = ?");
        $stmt->execute([$providerId]);
        if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Service provider not found"]);
            return;
        }

        // Validate every task belongs to one of the owner's projects
        $in = implode(',', array_fill(0, count($taskIds), '?'));
        $params = $taskIds;
        $params[] = $ownerId;
        $stmt = $this->db->prepare("
            SELECT t.task_id
            FROM tasks t
            JOIN projects p ON p.project_id = t.project_id
            WHERE t.task_id IN ($in) AND p.owner_id = ?
        ");
        $stmt->execute($params);
        $validTasks = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (count($validTasks) !== count($taskIds)) {
            http_response_code(403);
            echo json_encode(["success" => false, "message" => "One or more tasks don't belong to you"]);
            return;
        }

        $created = [];
        foreach ($taskIds as $taskId) {
            // Skip if a pending request already exists for the same owner/provider/task
            $stmt = $this->db->prepare("
                SELECT request_id FROM service_requests
                WHERE owner_id = ? AND provider_id = ? AND task_id = ? AND request_status = 'pending'
            ");
            $stmt->execute([$ownerId, $providerId, $taskId]);
            if ($stmt->fetch(PDO::FETCH_ASSOC)) {
                continue;
            }

            $stmt = $this->db->prepare("
                INSERT INTO service_requests (owner_id, provider_id, task_id, request_status, expires_at)
                VALUES (?, ?, ?, 'pending', DATE_ADD(NOW(), INTERVAL 3 DAY))
            ");
            $stmt->execute([$ownerId, $providerId, $taskId]);

            $created[] = [
                "request_id" => (int) $this->db->lastInsertId(),
                "task_id"    => $taskId,
            ];
        }

        if (empty($created)) {
            echo json_encode([
                "success"  => false,
                "message"  => "A pending request already exists for these tasks",
                "requests" => [],
            ]);
            return;
        }

        echo json_encode([
            "success"  => true,
            "message"  => count($created) . " request(s) sent",
            "requests" => $created,
        ]);
    }

}
