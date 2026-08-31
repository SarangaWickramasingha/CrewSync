<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/auth.php';

class ReviewController {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    private function getOwnerId($user) {
        $stmt = $this->db->prepare("SELECT owner_id FROM property_owners WHERE user_id = ?");
        $stmt->execute([$user['user_id']]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int) $row['owner_id'] : null;
    }

    // ── GET PROVIDERS ASSIGNED TO THIS OWNER'S PROJECT TASKS ────────────────────
    // The owner can only review providers who are (or were) assigned to a task on
    // one of their projects. Used to populate the review dropdown.
    public function getAssignedProviders() {
        $user = requireRole('property_owner');
        $ownerId = $this->getOwnerId($user);

        if (!$ownerId) {
            http_response_code(403);
            echo json_encode(["success" => false, "message" => "Property owner profile not found"]);
            return;
        }

        $stmt = $this->db->prepare("
            SELECT DISTINCT sp.provider_id, u.fname, u.lname,
                   (
                       SELECT GROUP_CONCAT(s.name SEPARATOR ', ')
                       FROM provider_skills ps
                       JOIN skills s ON s.skill_id = ps.skill_id
                       WHERE ps.provider_id = sp.provider_id
                   ) AS skills
            FROM task_assignments ta
            JOIN tasks t ON t.task_id = ta.task_id
            JOIN projects p ON p.project_id = t.project_id
            JOIN service_providers sp ON sp.provider_id = ta.provider_id
            JOIN users u ON u.user_id = sp.user_id
            WHERE p.owner_id = ?
            ORDER BY u.fname, u.lname
        ");
        $stmt->execute([$ownerId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $providers = array_map(function ($r) {
            return [
                "provider_id" => (int) $r['provider_id'],
                "name"        => trim($r['fname'] . ' ' . $r['lname']),
                "skills"      => array_values(array_filter(array_map('trim', explode(',', $r['skills'] ?? '')))),
            ];
        }, $rows);

        echo json_encode(["success" => true, "providers" => $providers]);
    }

    // ── CREATE A REVIEW (property owner) ────────────────────────────────────────
    // Body: { "provider_id": 1, "rating": 5, "comment": "..." }
    // Only allowed for providers assigned to a task on one of the owner's projects.
    // Recomputes and persists service_providers.avg_rating after insert.
    public function create() {
        $user = requireRole('property_owner');
        $ownerId = $this->getOwnerId($user);

        if (!$ownerId) {
            http_response_code(403);
            echo json_encode(["success" => false, "message" => "Property owner profile not found"]);
            return;
        }

        $data    = json_decode(file_get_contents("php://input"), true) ?? [];
        $providerId = intval($data['provider_id'] ?? 0);
        $rating     = intval($data['rating'] ?? 0);
        $comment    = trim($data['comment'] ?? '');

        if (!$providerId || $rating < 1 || $rating > 5) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "provider_id and a rating between 1 and 5 are required"]);
            return;
        }

        if ($comment === '') {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Review comment is required"]);
            return;
        }

        // Validate the provider exists
        $stmt = $this->db->prepare("SELECT provider_id FROM service_providers WHERE provider_id = ?");
        $stmt->execute([$providerId]);
        if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Service provider not found"]);
            return;
        }

        // Ensure the provider is (or was) assigned to a task on one of this owner's projects
        $stmt = $this->db->prepare("
            SELECT ta.id
            FROM task_assignments ta
            JOIN tasks t ON t.task_id = ta.task_id
            JOIN projects p ON p.project_id = t.project_id
            WHERE ta.provider_id = ? AND p.owner_id = ?
            LIMIT 1
        ");
        $stmt->execute([$providerId, $ownerId]);
        if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
            http_response_code(403);
            echo json_encode([
                "success" => false,
                "message" => "You can only review providers assigned to a task on one of your projects",
            ]);
            return;
        }

        // Guard against duplicate reviews by this owner for the same provider
        $stmt = $this->db->prepare("
            SELECT review_id FROM reviews
            WHERE owner_id = ? AND provider_id = ?
        ");
        $stmt->execute([$ownerId, $providerId]);
        if ($stmt->fetch(PDO::FETCH_ASSOC)) {
            http_response_code(409);
            echo json_encode(["success" => false, "message" => "You already reviewed this provider"]);
            return;
        }

        $stmt = $this->db->prepare("
            INSERT INTO reviews (owner_id, provider_id, rating, comment)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$ownerId, $providerId, $rating, $comment]);
        $reviewId = (int) $this->db->lastInsertId();

        // Keep service_providers.avg_rating in sync with the live average
        $stmt = $this->db->prepare("
            UPDATE service_providers sp
            SET sp.avg_rating = COALESCE((
                SELECT ROUND(AVG(r.rating), 1) FROM reviews r WHERE r.provider_id = sp.provider_id
            ), 0)
            WHERE sp.provider_id = ?
        ");
        $stmt->execute([$providerId]);

        echo json_encode([
            "success"  => true,
            "message"  => "Review submitted successfully",
            "review_id" => $reviewId,
        ]);
    }

    // ── GET THE REVIEWS THIS OWNER HAS SUBMITTED ────────────────────────────────
    public function getMyReviews() {
        $user = requireRole('property_owner');
        $ownerId = $this->getOwnerId($user);

        if (!$ownerId) {
            http_response_code(403);
            echo json_encode(["success" => false, "message" => "Property owner profile not found"]);
            return;
        }

        $stmt = $this->db->prepare("
            SELECT r.review_id, r.rating, r.comment, r.review_date,
                   sp.provider_id, u.fname, u.lname
            FROM reviews r
            JOIN service_providers sp ON sp.provider_id = r.provider_id
            JOIN users u ON u.user_id = sp.user_id
            WHERE r.owner_id = ?
            ORDER BY r.review_date DESC
        ");
        $stmt->execute([$ownerId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $reviews = array_map(function ($r) {
            return [
                "review_id"   => (int) $r['review_id'],
                "provider_id" => (int) $r['provider_id'],
                "name"        => trim($r['fname'] . ' ' . $r['lname']),
                "rating"      => (int) $r['rating'],
                "comment"     => $r['comment'],
                "date"        => date('F j, Y', strtotime($r['review_date'])),
            ];
        }, $rows);

        echo json_encode(["success" => true, "reviews" => $reviews]);
    }

}
