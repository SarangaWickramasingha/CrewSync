<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/auth.php';

class SupplierController {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    private function getSupplierId($userId) {
        $stmt = $this->db->prepare("SELECT supplier_id FROM supplier_profiles WHERE user_id = ?");
        $stmt->execute([$userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['supplier_id'] : null;
    }

    // ── GET ALL PRODUCTS FOR LOGGED-IN SUPPLIER ────────────────────────────────
    public function getProducts() {
        $user = requireRole('material_supplier');
        $supplierId = $this->getSupplierId($user['user_id']);

        if (!$supplierId) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Supplier profile not found"]);
            return;
        }

        $stmt = $this->db->prepare("
            SELECT sm.id, sm.material_id, sm.unit_price, sm.stock_qty, sm.is_available, sm.description,
                   m.name, m.unit
            FROM supplier_materials sm
            JOIN materials m ON m.material_id = sm.material_id
            WHERE sm.supplier_id = ?
            ORDER BY sm.id DESC
        ");
        $stmt->execute([$supplierId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $products = array_map(function ($r) {
            $stockType = !$r['is_available'] ? 'out' : ($r['stock_qty'] <= 10 ? 'low' : 'in');
            return [
                "id"          => $r['id'],
                "material_id" => (int) $r['material_id'],
                "title"       => $r['name'],
                "unit"        => $r['unit'],
                "description" => $r['description'],
                "price"       => "LKR " . number_format($r['unit_price'], 2) . " / " . $r['unit'],
                "stockType"   => $stockType,
                "stockNote"   => $r['stock_qty'] . " " . $r['unit'],
                "stock_qty"   => (int) $r['stock_qty'],
                "unit_price"  => (float) $r['unit_price'],
            ];
        }, $rows);

        echo json_encode(["success" => true, "products" => $products]);
    }

    // ── ADD OR UPDATE A PRODUCT (upsert by supplier_id + material_id) ──────────
    public function upsertProduct() {
        $user = requireRole('material_supplier');
        $supplierId = $this->getSupplierId($user['user_id']);

        if (!$supplierId) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Supplier profile not found"]);
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true) ?? [];
        $materialId = intval($data['material_id'] ?? 0);
        $unitPrice  = floatval($data['unit_price'] ?? 0);
        $stockQty   = intval($data['stock_qty'] ?? 0);
        $description = trim($data['description'] ?? '');
        $isAvailable = isset($data['is_available']) ? (int) (bool) $data['is_available'] : 1;

        if (!$materialId || $unitPrice <= 0) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "material_id and unit_price are required"]);
            return;
        }

        $stmt = $this->db->prepare("SELECT id FROM supplier_materials WHERE supplier_id = ? AND material_id = ?");
        $stmt->execute([$supplierId, $materialId]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            $stmt = $this->db->prepare("
                UPDATE supplier_materials SET unit_price = ?, stock_qty = ?, description = ?, is_available = ?
                WHERE id = ?
            ");
            $stmt->execute([$unitPrice, $stockQty, $description, $isAvailable, $existing['id']]);
            $id = $existing['id'];
        } else {
            $stmt = $this->db->prepare("
                INSERT INTO supplier_materials (supplier_id, material_id, unit_price, stock_qty, description, is_available)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$supplierId, $materialId, $unitPrice, $stockQty, $description, $isAvailable]);
            $id = $this->db->lastInsertId();
        }

        echo json_encode(["success" => true, "id" => $id]);
    }

    // ── REMOVE A PRODUCT ─────────────────────────────────────────────────────────
    public function removeProduct($id) {
        $user = requireRole('material_supplier');
        $supplierId = $this->getSupplierId($user['user_id']);

        if (!$supplierId) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Supplier profile not found"]);
            return;
        }

        $stmt = $this->db->prepare("DELETE FROM supplier_materials WHERE id = ? AND supplier_id = ?");
        $stmt->execute([$id, $supplierId]);

        echo json_encode(["success" => true]);
    }
}