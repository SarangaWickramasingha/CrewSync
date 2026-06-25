<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';

class AuthController {

    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }

    public function login() {

        $userModel = new User($this->db);

        $data = json_decode(file_get_contents("php://input"), true);

        $email    = $data['email']    ?? '';
        $password = $data['password'] ?? '';

        $user = $userModel->findByEmail($email);

        if (!$user) {
            echo json_encode([
                "success" => false,
                "message" => "User not found"
            ]);
            return;
        }

        if (!password_verify($password, $user['password_hash'])) {
            echo json_encode([
                "success" => false,
                "message" => "Invalid password"
            ]);
            return;
        }

        echo json_encode([
            "success" => true,
            "message" => "Login successful",
            "user" => [
                "user_id" => $user['user_id'],
                "fname"   => $user['fname'],
                "role"    => $user['role']
            ]
        ]);
    }

    public function register() {

        $userModel = new User($this->db);

        $data = json_decode(file_get_contents("php://input"), true);

        // 1. Common required fields
        $required = ['email', 'password', 'role', 'fname', 'lname', 'contact_no', 'district'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                http_response_code(400);
                echo json_encode(["success" => false, "message" => "Missing field: $field"]);
                return;
            }
        }

        // 2. Password length check
        if (strlen($data['password']) < 8) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Password must be at least 8 characters"]);
            return;
        }

        // 3. Role validation
        $allowedRoles = ['property_owner', 'service_provider', 'material_supplier'];
        if (!in_array($data['role'], $allowedRoles)) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Invalid role"]);
            return;
        }

        // 4. Role-specific validation
        if ($data['role'] === 'service_provider') {
            if (empty($data['charge_per_day'])) {
                http_response_code(400);
                echo json_encode(["success" => false, "message" => "Missing field: charge_per_day"]);
                return;
            }
        }

        if ($data['role'] === 'material_supplier') {
            if (empty($data['business_name'])) {
                http_response_code(400);
                echo json_encode(["success" => false, "message" => "Missing field: business_name"]);
                return;
            }
        }

        // 5. Check email already exists
        if ($userModel->emailExists($data['email'])) {
            http_response_code(409);
            echo json_encode(["success" => false, "message" => "Email already registered"]);
            return;
        }

        // 6. Hash password
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);

        // 7. Save to DB
        try {
            $userId = $userModel->createUser($data);
            http_response_code(201);
            echo json_encode([
                "success" => true,
                "message" => "Account created successfully",
                "user_id" => $userId
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "message" => "Registration failed. Please try again."
            ]);
        }
    }
}