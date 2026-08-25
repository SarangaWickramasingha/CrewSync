<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../config/jwt.php';
require_once __DIR__ . '/../middleware/auth.php';

class AuthController {

    private $db;

public function __construct() {
    $this->db = Database::getInstance()->getConnection(); // 👈 singleton way
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
            "message" => "Invalid email or password"
        ]);
        return;
    }

    if (!password_verify($password, $user['password_hash'])) {
        echo json_encode([
            "success" => false,
            "message" => "Invalid email or password"
        ]);
        return;
    }

    // Generate JWT with user data embedded inside
        $token = generateToken([
            'user_id' => $user['user_id'],
            'name'    => $user['fname'] . ' ' . $user['lname'],
            'role'    => $user['role'],
        ]);

    // Set it as an httpOnly cookie — JS cannot read this
    setAuthCookie($token);

    // Still return user data in the response body
    // Frontend uses this to set React state (name, role, avatar)
    echo json_encode([
        "success" => true,
        "message" => "Login successful",
        "user" => [
            "user_id" => $user['user_id'],
            "name" => $user['fname'] . ' ' . $user['lname'],
            "role"    => $user['role']
        ]
    ]);
}
public function me() {
    // Validate the cookie and get the user payload
    $user = requireAuth();

    echo json_encode([
        "success" => true,
        "user" => [
            "user_id" => $user['user_id'],
            "name"    => $user['name'],
            "role"    => $user['role']
        ]
    ]);
}

public function logout() {
    clearAuthCookie();
    echo json_encode([
        "success" => true,
        "message" => "Logged out successfully"
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



    /**
     * Email check -Registration
     */
    public function checkEmail() {
    $data = json_decode(file_get_contents("php://input"), true);

    $email = trim($data['email'] ?? '');

    if (!$email) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Email is required"]);
        return;
    }

    $userModel = new User($this->db);
    $exists = $userModel->emailExists($email);

    echo json_encode(["exists" => $exists]);
}


}