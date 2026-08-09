<?php
require_once __DIR__ . '/../config/database.php';

class SearchController {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // ── SEARCH SERVICE PROVIDERS ──────────────────────────────────────────────
    // Public endpoint — guests and logged-in users can browse available providers.
    // Accepts optional query params: skill_id, district, q (name/skill search)
    public function searchProviders() {
        $skillId  = (isset($_GET['skill_id']) && $_GET['skill_id'] !== '') ? (int) $_GET['skill_id'] : null;
        $district = trim($_GET['district'] ?? '');
        $q        = trim($_GET['q'] ?? '');

        $sql = "
            SELECT
                sp.provider_id,
                u.fname,
                u.lname,
                u.district,
                sp.charge_per_day,
                sp.bio,
                (
                    SELECT GROUP_CONCAT(s.name SEPARATOR ', ')
                    FROM provider_skills ps
                    JOIN skills s ON s.skill_id = ps.skill_id
                    WHERE ps.provider_id = sp.provider_id
                ) AS skills,
                COUNT(DISTINCT r.review_id) AS review_count,
                COALESCE(AVG(r.rating), 0) AS avg_rating
            FROM service_providers sp
            JOIN users u ON u.user_id = sp.user_id
            LEFT JOIN reviews r ON r.provider_id = sp.provider_id
            WHERE sp.is_available = 1
        ";

        $params = [];

        if ($skillId) {
            $sql .= " AND EXISTS (
                SELECT 1 FROM provider_skills ps
                WHERE ps.provider_id = sp.provider_id AND ps.skill_id = ?
            )";
            $params[] = $skillId;
        }

        if ($district !== '') {
            $sql .= " AND u.district = ?";
            $params[] = $district;
        }

        if ($q !== '') {
            $sql .= " AND (
                u.fname LIKE ? OR u.lname LIKE ? OR EXISTS (
                    SELECT 1 FROM provider_skills ps
                    JOIN skills s ON s.skill_id = ps.skill_id
                    WHERE ps.provider_id = sp.provider_id AND s.name LIKE ?
                )
            )";
            $like = '%' . $q . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $sql .= " GROUP BY sp.provider_id, u.fname, u.lname, u.district, sp.charge_per_day, sp.bio
                  ORDER BY avg_rating DESC, review_count DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $providers = array_map(function ($r) {
            $skills = array_values(array_filter(array_map('trim', explode(',', $r['skills'] ?? ''))));

            $fname = $r['fname'];
            $lname = $r['lname'];

            $initials = strtoupper($fname[0] ?? '');
            if (isset($lname[0]) && trim($lname) !== '') {
                $initials .= strtoupper($lname[0]);
            } elseif (isset($fname[1])) {
                $initials .= strtoupper($fname[1]);
            }

            return [
                "provider_id"  => (int) $r['provider_id'],
                "name"         => trim($fname . ' ' . $lname),
                "initials"     => $initials,
                "skills"       => $skills,
                "district"     => $r['district'],
                "daily_rate"   => $r['charge_per_day'] !== null ? (float) $r['charge_per_day'] : null,
                "bio"          => $r['bio'],
                "rating"       => round((float) $r['avg_rating'], 1),
                "review_count" => (int) $r['review_count'],
            ];
        }, $rows);

        echo json_encode(["success" => true, "providers" => $providers]);
    }

}
