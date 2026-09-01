<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../helpers/notify.php';

class ProviderController {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }


    // ── TOGGLE AVAILABILITY STATUS ────────────────────────────────────────────
    
    private function getUploadsBaseUrl() {
        $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8080';
        return "{$scheme}://{$host}/CrewSync-backend/backend/uploads/";
    }
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
        try {
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

            // Pending job requests (non-expired)
            $stmt = $this->db->prepare("
                SELECT COUNT(*) AS pending_requests
                FROM service_requests
                WHERE provider_id = ? AND request_status = 'pending'
                  AND (expires_at IS NULL OR expires_at >= NOW())
            ");
            $stmt->execute([$providerId]);
            $pendingRequests = $stmt->fetch(PDO::FETCH_ASSOC);

            echo json_encode([
                "success"          => true,
                "total_reviews"    => (int) $reviewStats['total_reviews'],
                "avg_rating"       => $reviewStats['avg_rating'] !== null ? round((float) $reviewStats['avg_rating'], 1) : 0,
                "active_projects"  => (int) $activeProjects['active_projects'],
                "jobs_completed"   => (int) $jobsCompleted['jobs_completed'],
                "pending_requests" => (int) $pendingRequests['pending_requests'],
            ]);
        } catch (PDOException $e) {
            error_log("getDashboardStats error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(["success" => false, "message" => "Failed to load dashboard stats"]);
        }
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
        try {
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
                SELECT r.review_id, r.rating, r.comment, r.review_date, u.fname, u.lname
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
        } catch (PDOException $e) {
            error_log("getRecentReviews error: " . $e->getMessage());
            echo json_encode(["success" => true, "reviews" => []]);
        }
    }
    // ── GET JOB REQUESTS ───────────────────────────────────────────────────────
    public function getJobRequests() {
        try {
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
        } catch (PDOException $e) {
            error_log("getJobRequests error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(["success" => false, "message" => "Failed to fetch job requests", "error" => $e->getMessage()]);
        }
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
            SELECT request_status, expires_at, task_id, owner_id FROM service_requests
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

        // ── NOTIFY OWNER ────────────────────────────────────────────────────────
        $ownerUserId = null;
        $stmt = $this->db->prepare("SELECT user_id FROM property_owners WHERE owner_id = ?");
        $stmt->execute([$request['owner_id']]);
        $owRow = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($owRow) $ownerUserId = (int) $owRow['user_id'];

        if ($ownerUserId) {
            // Fetch task name for the message
            $taskName = 'the task';
            if ($request['task_id']) {
                $stmt = $this->db->prepare("SELECT task_name FROM tasks WHERE task_id = ?");
                $stmt->execute([$request['task_id']]);
                $tRow = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($tRow) $taskName = $tRow['task_name'];
            }
            // Fetch provider name
            $stmt = $this->db->prepare("SELECT fname, lname FROM users WHERE user_id = ?");
            $stmt->execute([$user['user_id']]);
            $provUser = $stmt->fetch(PDO::FETCH_ASSOC);
            $provName = $provUser ? trim($provUser['fname'] . ' ' . $provUser['lname']) : 'A provider';

            $statusLabel = $newStatus === 'accepted' ? 'accepted' : 'declined';
            $message = "<strong>{$provName}</strong> {$statusLabel} your request for <strong>{$taskName}</strong>";
            notify_user($this->db, $ownerUserId, 'service_request', $message);
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

    // ── GET ALL REVIEWS (full list, with photos + reply) ──────────────────────
    public function getAllReviews() {
        $user = requireRole('service_provider');

        $stmt = $this->db->prepare("SELECT provider_id FROM service_providers WHERE user_id = ?");
        $stmt->execute([$user['user_id']]);
        $provider = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$provider) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Service provider profile not found"]);
            return;
        }

        $sql = "SELECT r.review_id, r.rating, r.comment, r.review_date,
                       u.fname, u.lname
                FROM reviews r
                JOIN property_owners po ON po.owner_id = r.owner_id
                JOIN users u ON u.user_id = po.user_id
                WHERE r.provider_id = ?
                ORDER BY r.review_date DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$provider['provider_id']]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $reviews = [];
        foreach ($rows as $r) {
            $stmt2 = $this->db->prepare("SELECT photo_id, file_path FROM review_photos WHERE review_id = ?");
            $stmt2->execute([$r['review_id']]);
            $photos = $stmt2->fetchAll(PDO::FETCH_ASSOC);

            $reviews[] = [
                "id"       => $r['review_id'],
                "name"     => trim($r['fname'] . ' ' . $r['lname']),
                "stars"    => (int) $r['rating'],
                "date"     => date('F j, Y', strtotime($r['review_date'])),
                "text"     => $r['comment'],
                "photos"   => array_map(fn($p) => [
                    "photo_id" => $p['photo_id'],
                    "url"      => $this->getUploadsBaseUrl() . $p['file_path'],
                ], $photos),
            ];
        }

        echo json_encode(["success" => true, "reviews" => $reviews]);
    }

    // ── GET PUBLIC PROVIDER PROFILE (guests can view) ──────────────────────────
    public function getPublicProfile($providerId) {
        $providerId = (int) $providerId;

        $stmt = $this->db->prepare("
            SELECT sp.provider_id, sp.bio, sp.experience_yr, sp.charge_per_day, sp.avg_rating,
                   sp.is_available, sp.willing_outside_region,
                   u.fname, u.lname, u.email, u.district
            FROM service_providers sp
            JOIN users u ON u.user_id = sp.user_id
            WHERE sp.provider_id = ?
        ");
        $stmt->execute([$providerId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Service provider not found"]);
            return;
        }

        $stmt = $this->db->prepare("
            SELECT s.name FROM provider_skills ps
            JOIN skills s ON s.skill_id = ps.skill_id
            WHERE ps.provider_id = ?
            ORDER BY ps.id ASC
        ");
        $stmt->execute([$providerId]);
        $skills = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'name');

        $stmt = $this->db->prepare("
            SELECT r.review_id, r.rating, r.comment, r.review_date, u.fname, u.lname
            FROM reviews r
            JOIN property_owners po ON po.owner_id = r.owner_id
            JOIN users u ON u.user_id = po.user_id
            WHERE r.provider_id = ?
            ORDER BY r.review_date DESC
        ");
        $stmt->execute([$providerId]);
        $reviewRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $reviews = [];
        foreach ($reviewRows as $r) {
            $stmt2 = $this->db->prepare("SELECT file_path FROM review_photos WHERE review_id = ?");
            $stmt2->execute([$r['review_id']]);
            $photoPaths = $stmt2->fetchAll(PDO::FETCH_ASSOC);

            $reviews[] = [
                "id"      => (int) $r['review_id'],
                "author"  => trim($r['fname'] . ' ' . $r['lname']),
                "date"    => date('F j, Y', strtotime($r['review_date'])),
                "rating"  => (int) $r['rating'],
                "comment" => $r['comment'],
                "photos"  => array_map(fn($p) => $this->getUploadsBaseUrl() . $p['file_path'], $photoPaths),
            ];
        }

        $fname = $row['fname'];
        $lname = $row['lname'];
        $initials = strtoupper($fname[0] ?? '');
        if (isset($lname[0]) && trim($lname) !== '') {
            $initials .= strtoupper($lname[0]);
        } elseif (isset($fname[1])) {
            $initials .= strtoupper($fname[1]);
        }

        // Compute the live average rating directly from this provider's reviews
        // instead of relying on the stored service_providers.avg_rating column,
        // which is only set by seed data and never kept in sync with new reviews.
        // This keeps the public profile consistent with search results / dashboard.
        $liveAvgRating = 0;
        if (!empty($reviewRows)) {
            $total = 0;
            foreach ($reviewRows as $rv) {
                $total += (int) $rv['rating'];
            }
            $liveAvgRating = round($total / count($reviewRows), 1);
        }

        echo json_encode([
            "success" => true,
            "provider" => [
                "provider_id"            => (int) $row['provider_id'],
                "name"                   => trim($fname . ' ' . $lname),
                "email"                  => $row['email'],
                "bio"                    => $row['bio'],
                "experience_yr"          => (int) $row['experience_yr'],
                "charge_per_day"         => $row['charge_per_day'] !== null ? (float) $row['charge_per_day'] : null,
                "avg_rating"             => $liveAvgRating,
                "is_available"           => (bool) $row['is_available'],
                "willing_outside_region" => (bool) $row['willing_outside_region'],
                "district"               => $row['district'],
                "initials"               => $initials,
                "skills"                 => $skills,
                "reviews"                => $reviews,
            ],
        ]);
    }

    // ── UPLOAD REVIEW PHOTOS ────────────────────────────────────────────────────
    public function uploadReviewPhotos($reviewId) {
        $user = requireRole('service_provider');

        $stmt = $this->db->prepare("SELECT provider_id FROM service_providers WHERE user_id = ?");
        $stmt->execute([$user['user_id']]);
        $provider = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$provider) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Service provider profile not found"]);
            return;
        }

        // Confirm this review belongs to this provider
        $stmt = $this->db->prepare("SELECT review_id FROM reviews WHERE review_id = ? AND provider_id = ?");
        $stmt->execute([$reviewId, $provider['provider_id']]);
        if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
            http_response_code(403);
            echo json_encode(["success" => false, "message" => "You don't have permission to add photos to this review"]);
            return;
        }

        if (empty($_FILES['photos'])) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "No files uploaded"]);
            return;
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        $uploadDir = __DIR__ . '/../uploads/review_photos/';
        $uploaded = [];

        $fileCount = count($_FILES['photos']['name']);
        for ($i = 0; $i < $fileCount; $i++) {
            if ($_FILES['photos']['error'][$i] !== UPLOAD_ERR_OK) continue;

            $tmpPath = $_FILES['photos']['tmp_name'][$i];
            $mimeType = mime_content_type($tmpPath);
            $size = $_FILES['photos']['size'][$i];

            if (!in_array($mimeType, $allowedTypes) || $size > $maxSize) continue;

            $ext = pathinfo($_FILES['photos']['name'][$i], PATHINFO_EXTENSION);
            $safeExt = preg_replace('/[^a-zA-Z0-9]/', '', $ext);
            $filename = "review_{$reviewId}_" . time() . "_" . bin2hex(random_bytes(4)) . "." . $safeExt;

            if (move_uploaded_file($tmpPath, $uploadDir . $filename)) {
                $relativePath = "review_photos/" . $filename;
                $stmt = $this->db->prepare("INSERT INTO review_photos (review_id, file_path) VALUES (?, ?)");
                $stmt->execute([$reviewId, $relativePath]);
                $uploaded[] = [
                    "photo_id" => $this->db->lastInsertId(),
                    "url"      => $this->getUploadsBaseUrl() . $relativePath,
                ];
            }
        }

        echo json_encode(["success" => true, "photos" => $uploaded]);
    }

    // ── DELETE REVIEW PHOTO ─────────────────────────────────────────────────────
    public function deleteReviewPhoto($photoId) {
        $user = requireRole('service_provider');

        $stmt = $this->db->prepare("SELECT provider_id FROM service_providers WHERE user_id = ?");
        $stmt->execute([$user['user_id']]);
        $provider = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt = $this->db->prepare("
            SELECT rp.file_path FROM review_photos rp
            JOIN reviews r ON r.review_id = rp.review_id
            WHERE rp.photo_id = ? AND r.provider_id = ?
        ");
        $stmt->execute([$photoId, $provider['provider_id']]);
        $photo = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$photo) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Photo not found"]);
            return;
        }

        $filePath = __DIR__ . '/../uploads/' . $photo['file_path'];
        if (file_exists($filePath)) unlink($filePath);

        $stmt = $this->db->prepare("DELETE FROM review_photos WHERE photo_id = ?");
        $stmt->execute([$photoId]);

        echo json_encode(["success" => true]);
    }


// ── GET PROFILE (personal info + skills) ──────────────────────────────────
    public function getProfile() {
        $user = requireRole('service_provider');

        $stmt = $this->db->prepare("
            SELECT u.fname, u.lname, u.district, sp.provider_id, sp.bio, sp.charge_per_day, sp.willing_outside_region
            FROM users u
            JOIN service_providers sp ON sp.user_id = u.user_id
            WHERE u.user_id = ?
        ");
        $stmt->execute([$user['user_id']]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Service provider profile not found"]);
            return;
        }

        $stmt = $this->db->prepare("
            SELECT skill_id, experience_yr, description FROM provider_skills WHERE provider_id = ?
        ");
        $stmt->execute([$row['provider_id']]);
        $skills = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            "success" => true,
            "personal_info" => [
                "full_name"  => trim($row['fname'] . ' ' . $row['lname']),
                "district"   => $row['district'],
                "daily_rate" => $row['charge_per_day'],
                "bio"        => $row['bio'],
                "out_region" => (bool) $row['willing_outside_region'],
            ],
            "skills" => array_map(fn($s) => [
                "skill_id" => (int) $s['skill_id'],
                "years"    => (int) $s['experience_yr'],
                "desc"     => $s['description'],
            ], $skills),
        ]);
    }

    // ── UPDATE PERSONAL INFO ───────────────────────────────────────────────────
    public function updatePersonalInfo() {
        $user = requireRole('service_provider');

        $data = json_decode(file_get_contents("php://input"), true) ?? [];

        $fullName  = trim($data['full_name'] ?? '');
        $district  = trim($data['district'] ?? '');
        $dailyRate = floatval($data['daily_rate'] ?? 0);
        $bio       = trim($data['bio'] ?? '');
        $outRegion = !empty($data['out_region']) ? 1 : 0;

        if (!$fullName || !$district) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Full name and district are required"]);
            return;
        }

        // Split into first name + rest as last name
        $parts = explode(' ', $fullName, 2);
        $fname = $parts[0];
        $lname = $parts[1] ?? '';

        $stmt = $this->db->prepare("UPDATE users SET fname = ?, lname = ?, district = ? WHERE user_id = ?");
        $stmt->execute([$fname, $lname, $district, $user['user_id']]);

        $stmt = $this->db->prepare("
            UPDATE service_providers SET bio = ?, charge_per_day = ?, willing_outside_region = ? WHERE user_id = ?
        ");
        $stmt->execute([$bio, $dailyRate, $outRegion, $user['user_id']]);

        echo json_encode(["success" => true]);
    }

    // ── ADD OR UPDATE A SKILL (upsert by skill_id) ─────────────────────────────
    public function upsertSkill() {
        $user = requireRole('service_provider');

        $stmt = $this->db->prepare("SELECT provider_id FROM service_providers WHERE user_id = ?");
        $stmt->execute([$user['user_id']]);
        $provider = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$provider) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Service provider profile not found"]);
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true) ?? [];
        $skillId = intval($data['skill_id'] ?? 0);
        $years   = intval($data['years'] ?? 1);
        $desc    = trim($data['description'] ?? '');

        if (!$skillId) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "skill_id is required"]);
            return;
        }

        $stmt = $this->db->prepare("SELECT id FROM provider_skills WHERE provider_id = ? AND skill_id = ?");
        $stmt->execute([$provider['provider_id'], $skillId]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            $stmt = $this->db->prepare("UPDATE provider_skills SET experience_yr = ?, description = ? WHERE id = ?");
            $stmt->execute([$years, $desc, $existing['id']]);
        } else {
            $stmt = $this->db->prepare("
                INSERT INTO provider_skills (provider_id, skill_id, experience_yr, description) VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$provider['provider_id'], $skillId, $years, $desc]);
        }

        echo json_encode(["success" => true]);
    }

    // ── REMOVE A SKILL ──────────────────────────────────────────────────────────
    public function removeSkill($skillId) {
        $user = requireRole('service_provider');

        $stmt = $this->db->prepare("SELECT provider_id FROM service_providers WHERE user_id = ?");
        $stmt->execute([$user['user_id']]);
        $provider = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$provider) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Service provider profile not found"]);
            return;
        }

        $stmt = $this->db->prepare("DELETE FROM provider_skills WHERE provider_id = ? AND skill_id = ?");
        $stmt->execute([$provider['provider_id'], $skillId]);

        echo json_encode(["success" => true]);
    }

}