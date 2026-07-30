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
}