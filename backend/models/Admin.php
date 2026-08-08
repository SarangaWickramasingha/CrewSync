<?php

class Admin {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // ── USERS ─────────────────────────────────────────────────────────────────

    public function getAllUsers() {
        $sql = "SELECT user_id, fname, lname, email, contact_no, district, role, status, created_at
                FROM users
                ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserById($id) {
        $sql = "SELECT user_id, fname, lname, email, contact_no, district, role, status, created_at
                FROM users WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createUser($fname, $lname, $email, $password, $contact_no, $district, $role) {
        $sql = "INSERT INTO users (fname, lname, email, password_hash, contact_no, district, role, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $fname, $lname, $email,
            password_hash($password, PASSWORD_BCRYPT),
            $contact_no, $district, $role
        ]);
    }

    public function updateUser($id, $data) {
        $fields = [];
        $values = [];

        $allowed = ['fname', 'lname', 'email', 'contact_no', 'district', 'role', 'status'];
        foreach ($allowed as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $values[] = $data[$field];
            }
        }

        if (empty($fields)) return false;

        $values[] = $id;
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($values);
    }

    public function deleteUser($id) {
        $stmt = $this->conn->prepare("DELETE FROM users WHERE user_id = ?");
        return $stmt->execute([$id]);
    }

    // ── PROPERTY OWNERS ───────────────────────────────────────────────────────

    public function getPropertyOwners() {
        $sql = "SELECT po.owner_id, po.user_id, u.fname, u.lname, u.email,
                       u.contact_no, u.district, u.status, po.address, u.created_at
                FROM property_owners po
                JOIN users u ON u.user_id = po.user_id
                ORDER BY u.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ── SERVICE PROVIDERS ─────────────────────────────────────────────────────

    public function getServiceProviders() {
        $sql = "SELECT sp.provider_id, sp.user_id, u.fname, u.lname, u.email,
                       u.contact_no, u.district, u.status, sp.bio,
                       sp.charge_per_day, sp.avg_rating, sp.is_available,
                       sp.willing_outside_region, u.created_at
                FROM service_providers sp
                JOIN users u ON u.user_id = sp.user_id
                ORDER BY u.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ── MATERIAL SUPPLIERS ────────────────────────────────────────────────────

    public function getMaterialSuppliers() {
        $sql = "SELECT ms.supplier_id, ms.user_id, u.fname, u.lname, u.email,
                       u.contact_no, u.district, u.status, ms.business_name,
                       ms.business_address, ms.is_hardware_shop,
                       u.created_at
                FROM supplier_profiles ms
                JOIN users u ON u.user_id = ms.user_id
                ORDER BY u.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ── ADMIN STATS ───────────────────────────────────────────────────────────

    public function getAdminStats() {
        // Total users
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS total FROM users WHERE role != 'admin'");
        $stmt->execute();
        $totalUsers = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // New users this week
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS total FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) AND role != 'admin'");
        $stmt->execute();
        $newUsersThisWeek = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Active projects
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS total FROM projects WHERE is_finished = 0");
        $stmt->execute();
        $activeProjects = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Service providers
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS total FROM users WHERE role = 'service_provider'");
        $stmt->execute();
        $serviceProviders = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // User distribution
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS total FROM users WHERE role = 'property_owner'");
        $stmt->execute();
        $owners = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];

        $stmt = $this->conn->prepare("SELECT COUNT(*) AS total FROM users WHERE role = 'material_supplier'");
        $stmt->execute();
        $suppliers = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Total transactions (sum of p_cost from projects)
        $stmt = $this->conn->prepare("SELECT COALESCE(SUM(p_cost), 0) AS total FROM projects");
        $stmt->execute();
        $totalTransactions = (float)$stmt->fetch(PDO::FETCH_ASSOC)['total'];

        return [
            'totalUsers'        => $totalUsers,
            'newUsersThisWeek'  => $newUsersThisWeek,
            'activeProjects'    => $activeProjects,
            'serviceProviders'  => $serviceProviders,
            'totalTransactions' => $totalTransactions,
            'userDistribution'  => [
                'owners'    => $owners,
                'providers' => $serviceProviders,
                'suppliers' => $suppliers,
            ],
        ];
    }

    // ── REVIEWS ───────────────────────────────────────────────────────────────

    public function getAllReviews() {
        $sql = "SELECT r.review_id, r.rating, r.comment, r.review_date,
                       CONCAT(uo.fname, ' ', uo.lname) AS reviewer_name,
                       CONCAT(up.fname, ' ', up.lname) AS provider_name
                FROM reviews r
                JOIN property_owners po   ON po.owner_id    = r.owner_id
                JOIN users uo             ON uo.user_id     = po.user_id
                JOIN service_providers sp ON sp.provider_id = r.provider_id
                JOIN users up             ON up.user_id     = sp.user_id
                ORDER BY r.review_date DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteReview($id) {
        $stmt = $this->conn->prepare("DELETE FROM reviews WHERE review_id = ?");
        return $stmt->execute([$id]);
    }

    // ── FEEDBACK ──────────────────────────────────────────────────────────────

    public function getAllFeedback() {
        $sql = "SELECT feedback_id, user_id, name, email, subject, message,
                       is_handled, created_at
                FROM feedback
                ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function setFeedbackHandled($id, $handled) {
        $stmt = $this->conn->prepare("UPDATE feedback SET is_handled = ? WHERE feedback_id = ?");
        return $stmt->execute([$handled ? 1 : 0, $id]);
    }

    // ── PROJECTS (ADMIN VIEW) ─────────────────────────────────────────────────

    public function getAllProjects() {
        $sql = "SELECT p.project_id, p.project_name, p.district, p.address,
                       p.p_budget, p.p_cost, p.start_date, p.end_date,
                       p.is_finished,
                       CONCAT(u.fname, ' ', u.lname) AS owner_name
                FROM projects p
                JOIN property_owners po ON po.owner_id = p.owner_id
                JOIN users u ON u.user_id = po.user_id
                ORDER BY p.start_date DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProjectWithTasks($project_id) {
        // Get project
        $sql = "SELECT p.*, CONCAT(u.fname, ' ', u.lname) AS owner_name
                FROM projects p
                JOIN property_owners po ON po.owner_id = p.owner_id
                JOIN users u ON u.user_id = po.user_id
                WHERE p.project_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$project_id]);
        $project = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$project) return null;

        // Get tasks with assigned provider names (a task may have several)
        $sql2 = "SELECT t.task_id, t.task_name, t.task_budget, t.t_cost,
                        t.start_date, t.end_date, t.is_finished,
                        GROUP_CONCAT(CONCAT(u.fname, ' ', u.lname) SEPARATOR ', ') AS provider_name
                 FROM tasks t
                 LEFT JOIN task_assignments ta ON ta.task_id = t.task_id
                 LEFT JOIN service_providers sp ON sp.provider_id = ta.provider_id
                 LEFT JOIN users u ON u.user_id = sp.user_id
                 WHERE t.project_id = ?
                 GROUP BY t.task_id
                 ORDER BY t.start_date ASC";
        $stmt2 = $this->conn->prepare($sql2);
        $stmt2->execute([$project_id]);
        $project['tasks'] = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        return $project;
    }
}