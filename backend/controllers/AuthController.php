<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';

class AuthController {

    public function login() {

        $database = new Database();

        $db = $database->connect();

        $userModel = new User($db);

        $data = json_decode(
            file_get_contents("php://input"),
            true
        );

        $email = $data['email'] ?? '';
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
                "fname" => $user['fname'],
                "role" => $user['role']
            ]
        ]);
    }
}