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

    // ── GET ALL ORDERS FOR LOGGED-IN SUPPLIER ────────────────────────────────────
    public function getOrders() {
        $user = requireRole('material_supplier');
        $supplierId = $this->getSupplierId($user['user_id']);

        if (!$supplierId) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Supplier profile not found"]);
            return;
        }

        $stmt = $this->db->prepare("
            SELECT mo.order_id, mo.quantity, mo.total_cost, mo.order_status, mo.ordered_at,
                   m.name AS material_name, m.unit,
                   u.fname, u.lname
            FROM material_orders mo
            JOIN supplier_materials sm ON sm.id = mo.supplier_material_id
            JOIN materials m ON m.material_id = sm.material_id
            JOIN property_owners po ON po.owner_id = mo.owner_id
            JOIN users u ON u.user_id = po.user_id
            WHERE sm.supplier_id = ?
            ORDER BY mo.ordered_at DESC
        ");
        $stmt->execute([$supplierId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $statusMap = [
            'pending'  => 'New',
            'accepted' => 'Processing',
            'rejected' => 'Rejected',
            'delivered'=> 'Delivered',
        ];

        $orders = array_map(function ($r) use ($statusMap) {
            $date = date('M j, Y', strtotime($r['ordered_at']));
            return [
                "id"          => '#ORD-' . str_pad($r['order_id'], 3, '0', STR_PAD_LEFT),
                "orderId"     => (int) $r['order_id'],
                "customer"    => trim($r['fname'] . ' ' . $r['lname']),
                "items"       => $r['material_name'] . ' × ' . $r['quantity'] . ' ' . $r['unit'],
                "amount"      => 'LKR ' . number_format($r['total_cost'], 0),
                "date"        => $date,
                "status"      => $statusMap[$r['order_status']] ?? $r['order_status'],
                "rawStatus"   => $r['order_status'],
            ];
        }, $rows);

        echo json_encode(["success" => true, "orders" => $orders]);
    }

    // ── UPDATE ORDER STATUS (accept / reject / delivered) ─────────────────────────
    public function updateOrderStatus($orderId) {
        $user = requireRole('material_supplier');
        $supplierId = $this->getSupplierId($user['user_id']);

        if (!$supplierId) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Supplier profile not found"]);
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true) ?? [];
        $newStatus = $data['status'] ?? '';

        $allowed = ['accepted', 'rejected', 'delivered'];
        if (!in_array($newStatus, $allowed)) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Invalid status. Allowed: " . implode(', ', $allowed)]);
            return;
        }

        // Verify this order belongs to this supplier
        $stmt = $this->db->prepare("
            SELECT mo.order_id FROM material_orders mo
            JOIN supplier_materials sm ON sm.id = mo.supplier_material_id
            WHERE mo.order_id = ? AND sm.supplier_id = ?
        ");
        $stmt->execute([$orderId, $supplierId]);
        if (!$stmt->fetch()) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Order not found"]);
            return;
        }

        $stmt = $this->db->prepare("UPDATE material_orders SET order_status = ? WHERE order_id = ?");
        $stmt->execute([$newStatus, $orderId]);

        echo json_encode(["success" => true]);
    }

    // ── GET SUPPLIER PROFILE ─────────────────────────────────────────────────────
    public function getProfile() {
        $user = requireRole('material_supplier');
        $supplierId = $this->getSupplierId($user['user_id']);

        if (!$supplierId) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Supplier profile not found"]);
            return;
        }

        // Get user info
        $stmt = $this->db->prepare("SELECT fname, lname, contact_no, district FROM users WHERE user_id = ?");
        $stmt->execute([$user['user_id']]);
        $userInfo = $stmt->fetch(PDO::FETCH_ASSOC);

        // Get supplier profile
        $stmt = $this->db->prepare("SELECT business_name, business_address, is_hardware_shop FROM supplier_profiles WHERE supplier_id = ?");
        $stmt->execute([$supplierId]);
        $supplierInfo = $stmt->fetch(PDO::FETCH_ASSOC);

        // Get hardware store if exists
        $hardwareStore = null;
        if ($supplierInfo && $supplierInfo['is_hardware_shop']) {
            $stmt = $this->db->prepare("SELECT store_name, br_number, address FROM hardware_stores WHERE supplier_id = ?");
            $stmt->execute([$supplierId]);
            $hardwareStore = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        echo json_encode([
            "success" => true,
            "profile" => [
                "personal" => [
                    "firstName"     => $userInfo['fname'] ?? '',
                    "lastName"      => $userInfo['lname'] ?? '',
                    "contactNumber" => $userInfo['contact_no'] ?? '',
                    "district"      => $userInfo['district'] ?? '',
                ],
                "business" => [
                    "businessName"    => $supplierInfo['business_name'] ?? '',
                    "businessAddress" => $supplierInfo['business_address'] ?? '',
                ],
                "hasHardware" => (bool) ($supplierInfo['is_hardware_shop'] ?? false),
                "hardware" => $hardwareStore ? [
                    "storeName" => $hardwareStore['store_name'] ?? '',
                    "brNumber"  => $hardwareStore['br_number'] ?? '',
                    "address"   => $hardwareStore['address'] ?? '',
                ] : null,
            ]
        ]);
    }

    // ── UPDATE SUPPLIER PROFILE ──────────────────────────────────────────────────
    public function updateProfile() {
        $user = requireRole('material_supplier');
        $supplierId = $this->getSupplierId($user['user_id']);

        if (!$supplierId) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Supplier profile not found"]);
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true) ?? [];
        $section = $data['section'] ?? '';
        $payload = $data['data'] ?? [];

        if (!$section || !$payload) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "section and data are required"]);
            return;
        }

        try {
            $this->db->beginTransaction();

            if ($section === 'personal') {
                $fname = trim($payload['firstName'] ?? '');
                $lname = trim($payload['lastName'] ?? '');
                $contact = trim($payload['contactNumber'] ?? '');
                $district = trim($payload['district'] ?? '');

                $stmt = $this->db->prepare("UPDATE users SET fname = ?, lname = ?, contact_no = ?, district = ? WHERE user_id = ?");
                $stmt->execute([$fname, $lname, $contact, $district, $user['user_id']]);
            }
            elseif ($section === 'business') {
                $bizName = trim($payload['businessName'] ?? '');
                $bizAddress = trim($payload['businessAddress'] ?? '');

                $stmt = $this->db->prepare("UPDATE supplier_profiles SET business_name = ?, business_address = ? WHERE supplier_id = ?");
                $stmt->execute([$bizName, $bizAddress, $supplierId]);
            }
            elseif ($section === 'hardware') {
                $hasHardware = $payload['hasHardware'] ?? null;
                $storeName = trim($payload['storeName'] ?? '');
                $brNumber = trim($payload['brNumber'] ?? '');
                $address = trim($payload['address'] ?? '');

                // Update is_hardware_shop flag
                $hwFlag = $hasHardware ? 1 : 0;
                $stmt = $this->db->prepare("UPDATE supplier_profiles SET is_hardware_shop = ? WHERE supplier_id = ?");
                $stmt->execute([$hwFlag, $supplierId]);

                if ($hasHardware) {
                    // Upsert hardware store
                    $stmt = $this->db->prepare("SELECT hardware_id FROM hardware_stores WHERE supplier_id = ?");
                    $stmt->execute([$supplierId]);
                    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($existing) {
                        $stmt = $this->db->prepare("UPDATE hardware_stores SET store_name = ?, br_number = ?, address = ? WHERE supplier_id = ?");
                        $stmt->execute([$storeName, $brNumber, $address, $supplierId]);
                    } else {
                        $stmt = $this->db->prepare("INSERT INTO hardware_stores (supplier_id, store_name, br_number, address) VALUES (?, ?, ?, ?)");
                        $stmt->execute([$supplierId, $storeName, $brNumber, $address]);
                    }
                } else {
                    // Remove hardware store if exists
                    $stmt = $this->db->prepare("DELETE FROM hardware_stores WHERE supplier_id = ?");
                    $stmt->execute([$supplierId]);
                }
            }
            else {
                $this->db->rollBack();
                http_response_code(400);
                echo json_encode(["success" => false, "message" => "Invalid section. Allowed: personal, business, hardware"]);
                return;
            }

            $this->db->commit();
            echo json_encode(["success" => true]);
        } catch (\Exception $e) {
            $this->db->rollBack();
            http_response_code(500);
            echo json_encode(["success" => false, "message" => "Failed to update profile: " . $e->getMessage()]);
        }
    }
}