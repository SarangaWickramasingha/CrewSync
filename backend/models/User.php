<?php

class User {

    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function findByEmail($email) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function emailExists($email) {
        $stmt = $this->conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch() !== false;
    }

    public function createUser($data) {

        $this->conn->beginTransaction();

        try {

            // 1. Insert into users table (all roles)
            $stmt = $this->conn->prepare("
                INSERT INTO users (fname, lname, email, contact_no, password_hash, role, district, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $data['fname'],
                $data['lname'],
                $data['email'],
                $data['contact_no'],
                $data['password'],       // already hashed in controller
                $data['role'],
                $data['district']
            ]);

            $userId = $this->conn->lastInsertId();

            // 2. Role-specific inserts
            if ($data['role'] === 'property_owner') {

                $stmt = $this->conn->prepare("
                    INSERT INTO property_owners (user_id, address)
                    VALUES (?, ?)
                ");
                $stmt->execute([
                    $userId,
                    $data['address'] ?? null
                ]);

            } elseif ($data['role'] === 'service_provider') {

                $stmt = $this->conn->prepare("
                    INSERT INTO service_provider (user_id, bio, work_address, willing_outside_region, charge_per_day)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $userId,
                    $data['bio'] ?? null,
                    $data['work_address'] ?? null,
                    !empty($data['willing_outside_region']) ? 1 : 0,
                    $data['charge_per_day'] ?? null
                ]);

                $profileId = $this->conn->lastInsertId();

                // Insert skills with experience_yr defaulting to 0
                if (!empty($data['skill_ids']) && is_array($data['skill_ids'])) {
                    $skillStmt = $this->conn->prepare("
                        INSERT INTO provider_skills (profile_id, skill_id, experience_yr)
                        VALUES (?, ?, 0)
                    ");
                    foreach ($data['skill_ids'] as $skillId) {
                        $skillStmt->execute([$profileId, (int)$skillId]);
                    }
                }

            } elseif ($data['role'] === 'material_supplier') {

                $stmt = $this->conn->prepare("
                    INSERT INTO supplier_profiles (user_id, business_name, business_address, is_hardware_shop)
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([
                    $userId,
                    $data['business_name'],
                    $data['business_address'] ?? null,
                    !empty($data['is_hardware_shop']) ? 1 : 0
                ]);

                $supplierId = $this->conn->lastInsertId();

                // Insert selected materials with price/stock as 0 (filled later from dashboard)
                if (!empty($data['material_ids']) && is_array($data['material_ids'])) {
                    $matStmt = $this->conn->prepare("
                        INSERT INTO supplier_materials (supplier_id, material_id, unit_price, stock_qty)
                        VALUES (?, ?, 0.00, 0)
                    ");
                    foreach ($data['material_ids'] as $materialId) {
                        $matStmt->execute([$supplierId, (int)$materialId]);
                    }
                }

                // Insert hardware store details if applicable
                if (!empty($data['is_hardware_shop']) && !empty($data['hw_store_name'])) {
                    $stmt = $this->conn->prepare("
                        INSERT INTO hardware_store_details (supplier_id, store_name, br_number, address)
                        VALUES (?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $supplierId,
                        $data['hw_store_name'],
                        $data['hw_br_number'] ?? '',
                        $data['hw_address'] ?? ''
                    ]);
                }
            }

            $this->conn->commit();
            return $userId;

        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }
}