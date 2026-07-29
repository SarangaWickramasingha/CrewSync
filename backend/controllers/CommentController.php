<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/auth.php';

class CommentController {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // ── HELPER: can this user access this project's forum? ───────────────────
    // For now: property owner who owns the project. Later you can OR-in
    // service providers assigned to the project.
    private function canAccessProject($projectId, $userId) {
        $stmt = $this->db->prepare("
            SELECT p.project_id
            FROM projects p
            JOIN property_owners po ON po.owner_id = p.owner_id
            WHERE po.user_id = ? AND p.project_id = ?
        ");
        $stmt->execute([$userId, $projectId]);
        return (bool) $stmt->fetch();
    }

    // ── GET ALL COMMENTS FOR A PROJECT ────────────────────────────────────────
    public function getByProject($projectId) {
        $user = requireAuth(); // any logged-in user; access checked below

        if (!$this->canAccessProject($projectId, $user['user_id'])) {
            http_response_code(403);
            echo json_encode(["success" => false, "message" => "Access denied"]);
            return;
        }

        $stmt = $this->db->prepare("
            SELECT c.comment_id, c.project_id, c.user_id, c.comment, c.created_at,
                   CONCAT(u.fname, ' ', u.lname) AS author_name, u.role AS author_role
            FROM project_comments c
            JOIN users u ON u.user_id = c.user_id
            WHERE c.project_id = ?
            ORDER BY c.created_at ASC
        ");
        $stmt->execute([$projectId]);
        $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            "success" => true,
            "comments" => $comments,
            "current_user_id" => (int) $user['user_id']
        ]);
    }

    // ── POST A COMMENT ────────────────────────────────────────────────────────
    public function create($projectId) {
        $user = requireAuth();

        if (!$this->canAccessProject($projectId, $user['user_id'])) {
            http_response_code(403);
            echo json_encode(["success" => false, "message" => "Access denied"]);
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true);
        $text = trim($data['comment'] ?? '');

        if ($text === '') {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Comment cannot be empty"]);
            return;
        }

        $stmt = $this->db->prepare("
            INSERT INTO project_comments (project_id, user_id, comment)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$projectId, $user['user_id'], $text]);

        // Return the freshly created comment with author info
        $stmt = $this->db->prepare("
            SELECT c.comment_id, c.project_id, c.user_id, c.comment, c.created_at,
                   CONCAT(u.fname, ' ', u.lname) AS author_name, u.role AS author_role
            FROM project_comments c
            JOIN users u ON u.user_id = c.user_id
            WHERE c.comment_id = ?
        ");
        $stmt->execute([$this->db->lastInsertId()]);

        echo json_encode([
            "success" => true,
            "comment" => $stmt->fetch(PDO::FETCH_ASSOC)
        ]);
    }
}