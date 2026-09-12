<?php

// Guard that verifies the database connection is alive.
// Mirrors the requireAuth() pattern: sends a 500 JSON error and exits.
// Returns the PDO connection so callers can chain:
//   $this->db = requireDb(Database::getInstance()->getConnection());
function requireDb(?PDO $db): PDO {
    if ($db === null) {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Database connection unavailable. Please try again later."
        ]);
        exit();
    }
    return $db;
}