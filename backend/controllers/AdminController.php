<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Admin.php';
require_once __DIR__ . '/../middleware/auth.php';

class AdminController {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // ── STATS ─────────────────────────────────────────────────────────────────

    public function getStats() {
        requireRole('admin');
        $model = new Admin($this->db);
        echo json_encode([
            'success' => true,
            'data'    => $model->getAdminStats(),
        ]);
    }

    // ── USERS ─────────────────────────────────────────────────────────────────

    public function getAllUsers() {
        requireRole('admin');
        $model = new Admin($this->db);
        echo json_encode([
            'success' => true,
            'users'   => $model->getAllUsers(),
        ]);
    }

    public function getUserById($id) {
        requireRole('admin');
        $model = new Admin($this->db);
        $user = $model->getUserById($id);
        if (!$user) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'User not found']);
            return;
        }
        echo json_encode(['success' => true, 'user' => $user]);
    }

    public function createUser() {
        requireRole('admin');
        $data = json_decode(file_get_contents('php://input'), true);

        $required = ['fname', 'lname', 'email', 'password', 'contact_no', 'district', 'role'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => "$field is required"]);
                return;
            }
        }

        $model   = new Admin($this->db);
        $created = $model->createUser(
            $data['fname'], $data['lname'], $data['email'],
            $data['password'], $data['contact_no'],
            $data['district'], $data['role']
        );

        echo json_encode([
            'success' => $created,
            'message' => $created ? 'User created successfully' : 'Could not create user',
        ]);
    }

    public function updateUser($id) {
        requireRole('admin');
        $data    = json_decode(file_get_contents('php://input'), true);
        $model   = new Admin($this->db);
        $updated = $model->updateUser($id, $data);
        echo json_encode([
            'success' => $updated,
            'message' => $updated ? 'User updated successfully' : 'Could not update user',
        ]);
    }

    public function deleteUser($id) {
        requireRole('admin');
        $model   = new Admin($this->db);
        $deleted = $model->deleteUser($id);
        echo json_encode([
            'success' => $deleted,
            'message' => $deleted ? 'User deleted successfully' : 'Could not delete user',
        ]);
    }

    // ── ROLE-SPECIFIC USERS ───────────────────────────────────────────────────

    public function getPropertyOwners() {
        requireRole('admin');
        $model = new Admin($this->db);
        echo json_encode([
            'success' => true,
            'owners'  => $model->getPropertyOwners(),
        ]);
    }

    public function getServiceProviders() {
        requireRole('admin');
        $model     = new Admin($this->db);
        echo json_encode([
            'success'   => true,
            'providers' => $model->getServiceProviders(),
        ]);
    }

    public function getMaterialSuppliers() {
        requireRole('admin');
        $model     = new Admin($this->db);
        echo json_encode([
            'success'   => true,
            'suppliers' => $model->getMaterialSuppliers(),
        ]);
    }

    // ── REVIEWS ───────────────────────────────────────────────────────────────

    public function getAllReviews() {
        requireRole('admin');
        $model = new Admin($this->db);
        echo json_encode([
            'success' => true,
            'reviews' => $model->getAllReviews(),
        ]);
    }

    public function deleteReview($id) {
        requireRole('admin');
        $model   = new Admin($this->db);
        $deleted = $model->deleteReview($id);
        echo json_encode([
            'success' => $deleted,
            'message' => $deleted ? 'Review deleted' : 'Could not delete review',
        ]);
    }

    // ── FEEDBACK ──────────────────────────────────────────────────────────────

    public function getAllFeedback() {
        requireRole('admin');
        $model = new Admin($this->db);
        echo json_encode([
            'success'  => true,
            'feedback' => $model->getAllFeedback(),
        ]);
    }

    public function updateFeedback($id) {
        requireRole('admin');
        $data    = json_decode(file_get_contents('php://input'), true);
        $model   = new Admin($this->db);
        $updated = $model->setFeedbackHandled($id, !empty($data['is_handled']));
        echo json_encode([
            'success' => $updated,
            'message' => $updated ? 'Feedback updated' : 'Could not update feedback',
        ]);
    }

    // ── PROJECTS ──────────────────────────────────────────────────────────────

    public function getAllProjects() {
        requireRole('admin');
        $model = new Admin($this->db);
        echo json_encode([
            'success'  => true,
            'projects' => $model->getAllProjects(),
        ]);
    }

    public function getProjectWithTasks($id) {
        requireRole('admin');
        $model   = new Admin($this->db);
        $project = $model->getProjectWithTasks($id);
        if (!$project) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Project not found']);
            return;
        }
        echo json_encode(['success' => true, 'project' => $project]);
    }
}