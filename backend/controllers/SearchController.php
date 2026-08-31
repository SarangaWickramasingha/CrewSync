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
        $minRating = (isset($_GET['min_rating']) && $_GET['min_rating'] !== '') ? (float) $_GET['min_rating'] : null;
        $maxRating = (isset($_GET['max_rating']) && $_GET['max_rating'] !== '') ? (float) $_GET['max_rating'] : null;

        $sql = "
            SELECT
                sp.provider_id,
                u.fname,
                u.lname,
                u.district,
                u.contact_no,
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

        $sql .= " GROUP BY sp.provider_id, u.fname, u.lname, u.district, sp.charge_per_day, sp.bio";

        if ($minRating !== null) {
            $sql .= " HAVING avg_rating >= ?";
            $params[] = $minRating;
        }

        if ($maxRating !== null) {
            if ($minRating !== null) {
                $sql .= " AND avg_rating <= ?";
            } else {
                $sql .= " HAVING avg_rating <= ?";
            }
            $params[] = $maxRating;
        }

        $sql .= " ORDER BY avg_rating DESC, review_count DESC";

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
                "contact_no"   => $r['contact_no'],
                "daily_rate"   => $r['charge_per_day'] !== null ? (float) $r['charge_per_day'] : null,
                "bio"          => $r['bio'],
                "rating"       => round((float) $r['avg_rating'], 1),
                "review_count" => (int) $r['review_count'],
            ];
        }, $rows);

        echo json_encode(["success" => true, "providers" => $providers]);
    }

    // ── SEARCH MATERIALS ─────────────────────────────────────────────────────
    // Public endpoint — property owners can browse available supplier materials.
    // Accepts optional query params: material_id, district, hardware (1/0)
    public function searchMaterials() {
        $materialId = (isset($_GET['material_id']) && $_GET['material_id'] !== '') ? (int) $_GET['material_id'] : null;
        $district   = trim($_GET['district'] ?? '');
        $hardware   = (isset($_GET['hardware']) && $_GET['hardware'] !== '') ? (int) $_GET['hardware'] : null;

        $sql = "
            SELECT
                sm.id,
                sm.material_id,
                sm.unit_price,
                sm.stock_qty,
                sm.description,
                m.name,
                m.unit,
                sp.business_name,
                sp.is_hardware_shop,
                sp.avg_rating,
                u.district,
                u.contact_no
            FROM supplier_materials sm
            JOIN materials m ON m.material_id = sm.material_id
            JOIN supplier_profiles sp ON sp.supplier_id = sm.supplier_id
            JOIN users u ON u.user_id = sp.user_id
            WHERE sm.is_available = 1
        ";

        $params = [];

        if ($materialId) {
            $sql .= " AND sm.material_id = ?";
            $params[] = $materialId;
        }

        if ($district !== '') {
            $sql .= " AND u.district = ?";
            $params[] = $district;
        }

        if ($hardware) {
            $sql .= " AND sp.is_hardware_shop = 1";
        }

        $sql .= " ORDER BY sp.avg_rating DESC, sm.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $materials = array_map(function ($r) {
            $stockQty = (int) $r['stock_qty'];
            $variant  = $stockQty <= 0 ? 'red' : ($stockQty <= 10 ? 'amber' : 'green');
            $stockText = $stockQty <= 0 ? 'Out of Stock' : ($stockQty <= 10 ? 'Low Stock' : 'In Stock');

            return [
                "id"           => (int) $r['id'],
                "material_id"  => (int) $r['material_id'],
                "name"         => $r['name'],
                "unit"         => $r['unit'],
                "description"  => $r['description'],
                "price"        => "LKR " . number_format($r['unit_price'], 0) . " / " . $r['unit'],
                "unit_price"   => (float) $r['unit_price'],
                "stock_qty"    => $stockQty,
                "stock"        => $stockText,
                "stockVariant" => $variant,
                "businessName" => $r['business_name'],
                "supplier"     => trim($r['business_name'] . ', ' . $r['district'], ', '),
                "district"     => $r['district'],
                "contactNo"    => $r['contact_no'],
                "isHardware"   => (bool) $r['is_hardware_shop'],
                "avg_rating"   => round((float) $r['avg_rating'], 1),
            ];
        }, $rows);

        echo json_encode(["success" => true, "materials" => $materials]);
    }

}
