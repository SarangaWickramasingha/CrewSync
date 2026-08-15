<?php

class Report {

    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // ── LIST STORED REPORTS FOR A PROJECT ──────────────────────────────────────
    public function listForProject($projectId) {
        if (!$this->conn) return [];

        try {
            $stmt = $this->conn->prepare("
                SELECT project_id, task_id, report_type, file_path, generated_date
                FROM reports
                WHERE project_id = ?
                ORDER BY generated_date DESC
            ");
            $stmt->execute([$projectId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Report list failed: " . $e->getMessage());
            return [];
        }
    }

    // ── FIND AN EXISTING REPORT (to reuse instead of regenerating) ────────────
    public function findExisting($projectId, $taskId, $reportType) {
        if (!$this->conn) return null;

        try {
            if ($taskId !== null) {
                $stmt = $this->conn->prepare("
                    SELECT report_id, file_path FROM reports
                    WHERE project_id = ? AND task_id = ? AND report_type = ?
                    ORDER BY report_id DESC LIMIT 1
                ");
                $stmt->execute([$projectId, $taskId, $reportType]);
            } else {
                $stmt = $this->conn->prepare("
                    SELECT report_id, file_path FROM reports
                    WHERE project_id = ? AND task_id IS NULL AND report_type = ?
                    ORDER BY report_id DESC LIMIT 1
                ");
                $stmt->execute([$projectId, $reportType]);
            }
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (PDOException $e) {
            error_log("Report find failed: " . $e->getMessage());
            return null;
        }
    }

    // ── STORE A NEW REPORT ─────────────────────────────────────────────────────
    public function insert($projectId, $taskId, $reportType, $filePath) {
        if (!$this->conn) return 0;

        try {
            $stmt = $this->conn->prepare("
                INSERT INTO reports (project_id, task_id, report_type, file_path)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$projectId, $taskId, $reportType, $filePath]);
            return (int) $this->conn->lastInsertId();
        } catch (PDOException $e) {
            error_log("Report insert failed: " . $e->getMessage());
            return 0;
        }
    }

    // ── REFRESH A STORED REPORT'S FILE PATH ────────────────────────────────────
    public function updateFile($reportId, $filePath) {
        if (!$this->conn) return false;

        try {
            $stmt = $this->conn->prepare("UPDATE reports SET file_path = ? WHERE report_id = ?");
            $stmt->execute([$filePath, $reportId]);
            return true;
        } catch (PDOException $e) {
            error_log("Report update failed: " . $e->getMessage());
            return false;
        }
    }

    // ── TASK WITH OWNERSHIP CHECK ──────────────────────────────────────────────
    public function getOwnedTask($taskId, $userId) {
        if (!$this->conn) return null;

        $stmt = $this->conn->prepare("
            SELECT t.*, p.project_name
            FROM tasks t
            JOIN projects p ON p.project_id = t.project_id
            JOIN property_owners po ON po.owner_id = p.owner_id
            WHERE po.user_id = ? AND t.task_id = ?
        ");
        $stmt->execute([$userId, $taskId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ── PROJECT BELONGS TO OWNER? ──────────────────────────────────────────────
    public function getOwnedProject($projectId, $userId) {
        if (!$this->conn) return null;

        $stmt = $this->conn->prepare("
            SELECT p.*
            FROM projects p
            JOIN property_owners po ON po.owner_id = p.owner_id
            WHERE po.user_id = ? AND p.project_id = ?
        ");
        $stmt->execute([$userId, $projectId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ── PER-TASK REPORT FACTS ──────────────────────────────────────────────────
    public function taskFacts($task) {
        if (!$this->conn) return $this->emptyTaskFacts($task);
        $taskId = $task['task_id'];

        try {
            // worked days = dates marked 'done'
            $stmt = $this->conn->prepare("
                SELECT COUNT(*) AS worked FROM task_daily_status
                WHERE task_id = ? AND status = 'done'
            ");
            $stmt->execute([$taskId]);
            $workedDays = (int) $stmt->fetch(PDO::FETCH_ASSOC)['worked'];

            // assigned service providers
            $stmt = $this->conn->prepare("
                SELECT u.fname, u.lname
                FROM task_assignments ta
                JOIN service_providers sp ON sp.provider_id = ta.provider_id
                JOIN users u ON u.user_id = sp.user_id
                WHERE ta.task_id = ?
            ");
            $stmt->execute([$taskId]);
            $providers = array_map(
                fn($p) => trim(($p['fname'] ?? '') . ' ' . ($p['lname'] ?? '')),
                $stmt->fetchAll(PDO::FETCH_ASSOC)
            );
        } catch (PDOException $e) {
            error_log("Task facts failed: " . $e->getMessage());
            $workedDays = 0;
            $providers = [];
        }

        $scheduledDays = null;
        if ($task['start_date'] && $task['end_date']) {
            $scheduledDays = (int) ((strtotime($task['end_date']) - strtotime($task['start_date'])) / 86400) + 1;
            if ($scheduledDays < 0) $scheduledDays = 0;
        }

        $budget = (float) ($task['task_budget'] ?? 0);
        $cost   = (float) ($task['t_cost'] ?? 0);

        return array_merge($this->emptyTaskFacts($task), [
            'worked_days'    => $workedDays,
            'scheduled_days' => $scheduledDays,
            'providers'      => $providers,
            'budget'         => $budget,
            'cost'           => $cost,
            'diff'           => $cost - $budget,
        ]);
    }

    private function emptyTaskFacts($task) {
        return [
            'task'           => $task,
            'worked_days'    => 0,
            'scheduled_days' => null,
            'providers'      => [],
            'budget'         => (float) ($task['task_budget'] ?? 0),
            'cost'           => (float) ($task['t_cost'] ?? 0),
            'diff'           => (float) (($task['t_cost'] ?? 0) - ($task['task_budget'] ?? 0)),
        ];
    }

    // ── PROJECT AGGREGATE FACTS ────────────────────────────────────────────────
    public function projectFacts($project, $projectId) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM tasks WHERE project_id = ? ORDER BY task_id ASC");
            $stmt->execute([$projectId]);
            $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Project tasks failed: " . $e->getMessage());
            $tasks = [];
        }

        $rows = [];
        $totalCost   = 0;
        $totalBudget = 0;
        $totalWorked = 0;
        foreach ($tasks as $t) {
            $d = $this->taskFacts($t);
            $rows[] = $d;
            $totalCost   += $d['cost'];
            $totalBudget += $d['budget'];
            $totalWorked += $d['worked_days'];
        }

        $budget = (float) ($project['p_budget'] ?? 0);
        $cost   = (float) ($project['p_cost'] ?? 0);
        if ($cost <= 0) $cost = $totalCost;

        $totalDays = null;
        if ($project['start_date'] && $project['end_date']) {
            $totalDays = (int) ((strtotime($project['end_date']) - strtotime($project['start_date'])) / 86400) + 1;
            if ($totalDays < 0) $totalDays = 0;
        }

        return [
            'project'       => $project,
            'tasks'         => $rows,
            'budget'        => $budget,
            'cost'          => $cost,
            'total_cost'    => $totalCost,
            'total_worked'  => $totalWorked,
            'duration_days' => $totalDays,
            'diff'          => $cost - $budget,
        ];
    }
}