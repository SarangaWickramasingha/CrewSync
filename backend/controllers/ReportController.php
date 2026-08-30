<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../models/Report.php';
require_once __DIR__ . '/../utils/PdfGenerator.php';

class ReportController {

    private $db;
    private $model;

    public function __construct() {
        $this->db    = Database::getInstance()->getConnection();
        $this->model = new Report($this->db);
    }

    // ── LIST REPORTS FOR A PROJECT ─────────────────────────────────────────────
    public function byProject($projectId) {
        $user = requireRole('property_owner');

        if (!$this->model->getOwnedProject($projectId, $user['user_id'])) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Project not found"]);
            return;
        }

        $reports = $this->model->listForProject($projectId);

        echo json_encode([
            "success" => true,
            "reports" => $reports,
            "project" => [
                "project_id" => (int) $projectId,
            ],
        ]);
    }

    private function fileExists($filePath) {
        if (!$filePath) return false;
        $name = basename(parse_url($filePath, PHP_URL_PATH));
        return $name !== '' && file_exists(PdfGenerator::reportsDir() . $name);
    }

    // ── GENERATE A TASK REPORT ─────────────────────────────────────────────────
    public function generateTask($taskId) {
        $user = requireRole('property_owner');
        $task = $this->model->getOwnedTask($taskId, $user['user_id']);

        if (!$task) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Task not found"]);
            return;
        }

        if (!(int) $task['is_finished']) {
            http_response_code(403);
            echo json_encode(["success" => false, "message" => "Report is only available for finished tasks."]);
            return;
        }

        $existing = $this->model->findExisting($task['project_id'], $taskId, 'task');
        if ($existing && $this->fileExists($existing['file_path'])) {
            echo json_encode([
                "success"   => true,
                "report_id" => (int) $existing['report_id'],
                "file_path" => $existing['file_path'],
            ]);
            return;
        }

        try {
            $pdf = new PdfGenerator();
            $filePath = $pdf->taskReport($this->model->taskFacts($task));
        } catch (Throwable $e) {
            error_log("Report generation failed: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(["success" => false, "message" => "Failed to generate the report: " . $e->getMessage()]);
            return;
        }

        if ($existing) {
            $this->model->updateFile($existing['report_id'], $filePath);
            echo json_encode([
                "success"   => true,
                "report_id" => (int) $existing['report_id'],
                "file_path" => $filePath,
            ]);
            return;
        }

        $reportId = $this->model->insert($task['project_id'], $taskId, 'task', $filePath);
        if (!$reportId) {
            http_response_code(500);
            echo json_encode(["success" => false, "message" => "Failed to store the report."]);
            return;
        }

        echo json_encode([
            "success"   => true,
            "report_id" => $reportId,
            "file_path" => $filePath,
        ]);
    }

    // ── GENERATE A PROJECT REPORT ──────────────────────────────────────────────
    public function generateProject($projectId) {
        $user = requireRole('property_owner');
        $project = $this->model->getOwnedProject($projectId, $user['user_id']);

        if (!$project) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Project not found"]);
            return;
        }

        if (!(int) $project['is_finished']) {
            http_response_code(403);
            echo json_encode(["success" => false, "message" => "Project report is only available for completed projects."]);
            return;
        }

        $existing = $this->model->findExisting($projectId, null, 'project');
        if ($existing && $this->fileExists($existing['file_path'])) {
            echo json_encode([
                "success"   => true,
                "report_id" => (int) $existing['report_id'],
                "file_path" => $existing['file_path'],
            ]);
            return;
        }

        try {
            $pdf = new PdfGenerator();
            $filePath = $pdf->projectReport($this->model->projectFacts($project, $projectId));
        } catch (Throwable $e) {
            error_log("Report generation failed: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(["success" => false, "message" => "Failed to generate the report: " . $e->getMessage()]);
            return;
        }

        if ($existing) {
            $this->model->updateFile($existing['report_id'], $filePath);
            echo json_encode([
                "success"   => true,
                "report_id" => (int) $existing['report_id'],
                "file_path" => $filePath,
            ]);
            return;
        }

        $reportId = $this->model->insert($projectId, null, 'project', $filePath);
        if (!$reportId) {
            http_response_code(500);
            echo json_encode(["success" => false, "message" => "Failed to store the report."]);
            return;
        }

        echo json_encode([
            "success"   => true,
            "report_id" => $reportId,
            "file_path" => $filePath,
        ]);
    }
}