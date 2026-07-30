<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/auth.php';

class ProviderController {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }


    // ── TOGGLE AVAILABILITY STATUS ────────────────────────────────────────────
    public function toggleAvailability() {
        $user = requireRole('service_provider');

        $stmt = $this->db->prepare("
            SELECT is_available FROM service_providers WHERE user_id = ?
        ");
        $stmt->execute([$user['user_id']]);
        $provider = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$provider) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Service provider profile not found"]);
            return;
        }

        $newValue = $provider['is_available'] ? 0 : 1;

        $stmt = $this->db->prepare("UPDATE service_providers SET is_available = ? WHERE user_id = ?");
        $stmt->execute([$newValue, $user['user_id']]);

        echo json_encode([
            "success"      => true,
            "is_available" => (bool) $newValue
        ]);
    }
    // ── GET AVAILABILITY STATUS ───────────────────────────────────────────────
    public function getAvailability() {
        $user = requireRole('service_provider');

        $stmt = $this->db->prepare("
            SELECT is_available FROM service_providers WHERE user_id = ?
        ");
        $stmt->execute([$user['user_id']]);
        $provider = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$provider) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Service provider profile not found"]);
            return;
        }

        echo json_encode([
            "success"      => true,
            "is_available" => (bool) $provider['is_available']
        ]);
    }


    // ── GET DASHBOARD STATS ───────────────────────────────────────────────────
    public function getDashboardStats() {
        $user = requireRole('service_provider');

        // Get provider_id from service_providers using user_id
        $stmt = $this->db->prepare("
            SELECT provider_id FROM service_providers WHERE user_id = ?
        ");
        $stmt->execute([$user['user_id']]);
        $provider = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$provider) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Service provider profile not found"]);
            return;
        }

        $providerId = $provider['provider_id'];

        // Total reviews + average rating
        $stmt = $this->db->prepare("
            SELECT COUNT(*) AS total_reviews, AVG(rating) AS avg_rating
            FROM reviews
            WHERE provider_id = ?
        ");
        $stmt->execute([$providerId]);
        $reviewStats = $stmt->fetch(PDO::FETCH_ASSOC);

        // Active projects — distinct projects (via assigned tasks) that aren't finished
        $stmt = $this->db->prepare("
            SELECT COUNT(DISTINCT t.project_id) AS active_projects
            FROM task_assignments ta
            JOIN tasks t ON t.task_id = ta.task_id
            JOIN projects p ON p.project_id = t.project_id
            WHERE ta.provider_id = ? AND p.is_finished = 0
        ");
        $stmt->execute([$providerId]);
        $activeProjects = $stmt->fetch(PDO::FETCH_ASSOC);

        // Jobs completed — assigned tasks that are finished
        $stmt = $this->db->prepare("
            SELECT COUNT(*) AS jobs_completed
            FROM task_assignments ta
            JOIN tasks t ON t.task_id = ta.task_id
            WHERE ta.provider_id = ? AND t.is_finished = 1
        ");
        $stmt->execute([$providerId]);
        $jobsCompleted = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            "success"        => true,
            "total_reviews"  => (int) $reviewStats['total_reviews'],
            "avg_rating"     => $reviewStats['avg_rating'] !== null ? round((float) $reviewStats['avg_rating'], 1) : 0,
            "active_projects"=> (int) $activeProjects['active_projects'],
            "jobs_completed" => (int) $jobsCompleted['jobs_completed'],
        ]);
    }


    // ── GET CURRENT WORK (assigned tasks) ─────────────────────────────────────
    public function getCurrentWork() {
        $user = requireRole('service_provider');

        $stmt = $this->db->prepare("SELECT provider_id FROM service_providers WHERE user_id = ?");
        $stmt->execute([$user['user_id']]);
        $provider = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$provider) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Service provider profile not found"]);
            return;
        }

        $stmt = $this->db->prepare("
            SELECT t.task_id, t.task_name, t.start_date, t.end_date, t.is_finished,
                   p.project_name
            FROM task_assignments ta
            JOIN tasks t ON t.task_id = ta.task_id
            JOIN projects p ON p.project_id = t.project_id
            WHERE ta.provider_id = ?
            ORDER BY t.start_date ASC
            LIMIT 5
        ");
        $stmt->execute([$provider['provider_id']]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $today = date('Y-m-d');
        $work = array_map(function ($t) use ($today) {
            if ($t['is_finished']) {
                $status = 'Completed';
            } elseif ($t['start_date'] && $t['start_date'] <= $today) {
                $status = 'Active';
            } else {
                $status = 'Upcoming';
            }
            return [
                "task_id"      => $t['task_id'],
                "task_name"    => $t['task_name'],
                "project_name" => $t['project_name'],
                "start_date"   => $t['start_date'],
                "end_date"     => $t['end_date'],
                "status"       => $status,
            ];
        }, $rows);

        echo json_encode(["success" => true, "current_work" => $work]);
    }



    // ── GET RECENT REVIEWS ─────────────────────────────────────────────────────
    public function getRecentReviews() {
        $user = requireRole('service_provider');

        $stmt = $this->db->prepare("SELECT provider_id FROM service_providers WHERE user_id = ?");
        $stmt->execute([$user['user_id']]);
        $provider = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$provider) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Service provider profile not found"]);
            return;
        }

        $stmt = $this->db->prepare("
            SELECT r.rating, r.comment, r.review_date, u.fname, u.lname
            FROM reviews r
            JOIN property_owners po ON po.owner_id = r.owner_id
            JOIN users u ON u.user_id = po.user_id
            WHERE r.provider_id = ?
            ORDER BY r.review_date DESC
            LIMIT 5
        ");
        $stmt->execute([$provider['provider_id']]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $reviews = array_map(function ($r) {
            return [
                "name"    => $r['fname'] . ' ' . substr($r['lname'], 0, 1) . '.',
                "rating"  => (int) $r['rating'],
                "comment" => $r['comment'],
            ];
        }, $rows);

        echo json_encode(["success" => true, "reviews" => $reviews]);
    }
    // ── GET JOB REQUESTS ───────────────────────────────────────────────────────
    public function getJobRequests() {
        $user = requireRole('service_provider');

        $stmt = $this->db->prepare("SELECT provider_id FROM service_providers WHERE user_id = ?");
        $stmt->execute([$user['user_id']]);
        $provider = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$provider) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Service provider profile not found"]);
            return;
        }

        $providerId = $provider['provider_id'];

        // Auto-expire anything past its deadline
        $stmt = $this->db->prepare("
            UPDATE service_requests
            SET request_status = 'expired'
            WHERE provider_id = ? AND request_status = 'pending' AND expires_at IS NOT NULL AND expires_at < NOW()
        ");
        $stmt->execute([$providerId]);

        // Fetch active (non-expired) requests
        $stmt = $this->db->prepare("
            SELECT sr.request_id, sr.request_status, sr.request_date, sr.expires_at,
                   u.fname, u.lname,
                   t.task_name, t.start_date, t.end_date,
                   p.district
            FROM service_requests sr
            JOIN property_owners po ON po.owner_id = sr.owner_id
            JOIN users u ON u.user_id = po.user_id
            LEFT JOIN tasks t ON t.task_id = sr.task_id
            LEFT JOIN projects p ON p.project_id = t.project_id
            WHERE sr.provider_id = ? AND sr.request_status != 'expired'
            ORDER BY sr.request_date DESC
        ");
        $stmt->execute([$providerId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $statusMap = ['pending' => 'New', 'accepted' => 'Accepted', 'rejected' => 'Declined'];

        $jobs = array_map(function ($r) use ($statusMap) {
            $duration = null;
            if ($r['start_date'] && $r['end_date']) {
                $days = (strtotime($r['end_date']) - strtotime($r['start_date'])) / 86400;
                $duration = $days >= 7 ? round($days / 7) . ' week' . (round($days / 7) != 1 ? 's' : '') : $days . ' day' . ($days != 1 ? 's' : '');
            }
            return [
                "id"       => $r['request_id'],
                "title"    => $r['task_name'] ?? 'General Service Request',
                "client"   => trim($r['fname'] . ' ' . $r['lname']),
                "location" => $r['district'] ?? 'Not specified',
                "duration" => $duration ?? 'N/A',
                "start"    => $r['start_date'] ?? 'N/A',
                "status"   => $statusMap[$r['request_status']] ?? 'New',
            ];
        }, $rows);

        echo json_encode(["success" => true, "jobs" => $jobs]);
    }

    // ── RESPOND TO JOB REQUEST (accept / decline / undo) ──────────────────────
    // ── RESPOND TO JOB REQUEST (accept / decline) ─────────────────────────────
    public function respondToJobRequest($requestId) {
        $user = requireRole('service_provider');

        $stmt = $this->db->prepare("SELECT provider_id FROM service_providers WHERE user_id = ?");
        $stmt->execute([$user['user_id']]);
        $provider = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$provider) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Service provider profile not found"]);
            return;
        }

        $providerId = $provider['provider_id'];

        $data = json_decode(file_get_contents("php://input"), true) ?? [];
        $action = $data['action'] ?? '';

        $validActions = ['accept' => 'accepted', 'decline' => 'rejected'];
        if (!isset($validActions[$action])) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Invalid action"]);
            return;
        }

        // Confirm the request belongs to this provider and isn't expired
        $stmt = $this->db->prepare("
            SELECT request_status, expires_at, task_id FROM service_requests
            WHERE request_id = ? AND provider_id = ?
        ");
        $stmt->execute([$requestId, $providerId]);
        $request = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$request) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Request not found"]);
            return;
        }

        if ($request['request_status'] === 'expired' || ($request['expires_at'] && strtotime($request['expires_at']) < time())) {
            http_response_code(410);
            echo json_encode(["success" => false, "message" => "This request has expired"]);
            return;
        }

        $newStatus = $validActions[$action];

        $stmt = $this->db->prepare("UPDATE service_requests SET request_status = ? WHERE request_id = ?");
        $stmt->execute([$newStatus, $requestId]);

        // If accepted AND tied to a specific task, create the actual assignment
        if ($newStatus === 'accepted' && $request['task_id']) {
            // Avoid duplicate assignment if somehow already assigned
            $stmt = $this->db->prepare("
                SELECT id FROM task_assignments WHERE task_id = ? AND provider_id = ?
            ");
            $stmt->execute([$request['task_id'], $providerId]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$existing) {
                $stmt = $this->db->prepare("
                    INSERT INTO task_assignments (task_id, provider_id) VALUES (?, ?)
                ");
                $stmt->execute([$request['task_id'], $providerId]);
            }
        }
        

        echo json_encode([
            "success" => true,
            "status"  => $newStatus === 'accepted' ? 'Accepted' : 'Declined'
        ]);
    }
    // ── GET FULL TIMELINE (all projects the provider is assigned to) ──────────
    public function getTimeline() {
        $user = requireRole('service_provider');

        $stmt = $this->db->prepare("SELECT provider_id FROM service_providers WHERE user_id = ?");
        $stmt->execute([$user['user_id']]);
        $provider = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$provider) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Service provider profile not found"]);
            return;
        }

        $providerId = $provider['provider_id'];

        $stmt = $this->db->prepare("
            SELECT DISTINCT p.project_id, p.project_name, p.is_finished
            FROM task_assignments ta
            JOIN tasks t ON t.task_id = ta.task_id
            JOIN projects p ON p.project_id = t.project_id
            WHERE ta.provider_id = ?
            ORDER BY p.project_id DESC
        ");
        $stmt->execute([$providerId]);
        $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($projects as $proj) {
            $stmt = $this->db->prepare("
                SELECT task_id, task_name, start_date, end_date, is_finished
                FROM tasks
                WHERE project_id = ?
                ORDER BY start_date ASC
            ");
            $stmt->execute([$proj['project_id']]);
            $allTasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt = $this->db->prepare("
                SELECT t.task_id, t.task_name
                FROM task_assignments ta
                JOIN tasks t ON t.task_id = ta.task_id
                WHERE ta.provider_id = ? AND t.project_id = ?
            ");
            $stmt->execute([$providerId, $proj['project_id']]);
            $assigned = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $assignedNames = array_column($assigned, 'task_name');

            $tasksFormatted = array_map(function ($t) {
                $status = $t['is_finished'] ? 'done' : 'pending';
                $dates = $t['start_date']
                    ? ($t['end_date'] ? "{$t['start_date']} – {$t['end_date']}" : "Starts {$t['start_date']}")
                    : 'Dates not set';
                return [
                    "task_id" => $t['task_id'],
                    "name"    => $t['task_name'],
                    "dates"   => $dates,
                    "status"  => $status,
                    "label"   => $t['is_finished'] ? 'Done' : 'Pending',
                ];
            }, $allTasks);

            $result[] = [
                "project_id"          => $proj['project_id'],
                "project_name"        => $proj['project_name'],
                "project_status"      => $proj['is_finished'] ? 'Completed' : 'In Progress',
                "tasks"               => $tasksFormatted,
                "assigned_task_names" => $assignedNames,
            ];
        }

        echo json_encode(["success" => true, "projects" => $result]);
    }

}