<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/requireDb.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../utils/s3.php';

class ReviewPhotoController {
    private $db;

    public function __construct() {
        $this->db = requireDb(Database::getInstance()->getConnection());
    }

    private function getUploadsBaseUrl(): string {
        $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8080';
        return "{$scheme}://{$host}/CrewSync-backend/backend/uploads/";
    }

    public function uploadReviewPhotos($reviewId) {
        $user = requireRole('service_provider');

        $stmt = $this->db->prepare("SELECT provider_id FROM service_providers WHERE user_id = ?");
        $stmt->execute([$user['user_id']]);
        $provider = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$provider) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Service provider profile not found"]);
            return;
        }

        // Confirm this review belongs to this provider
        $stmt = $this->db->prepare("SELECT review_id FROM reviews WHERE review_id = ? AND provider_id = ?");
        $stmt->execute([$reviewId, $provider['provider_id']]);
        if (!$stmt->fetch()) {
            http_response_code(403);
            echo json_encode(["success" => false, "message" => "You don't have permission to add photos to this review"]);
            return;
        }

        if (empty($_FILES['photos'])) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "No files uploaded"]);
            return;
        }

        $allowedExts  = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $uploaded = [];

        $names    = is_array($_FILES['photos']['name']) ? $_FILES['photos']['name'] : [$_FILES['photos']['name']];
        $errors   = is_array($_FILES['photos']['error']) ? $_FILES['photos']['error'] : [$_FILES['photos']['error']];
        $tmpNames = is_array($_FILES['photos']['tmp_name']) ? $_FILES['photos']['tmp_name'] : [$_FILES['photos']['tmp_name']];
        $sizes    = is_array($_FILES['photos']['size']) ? $_FILES['photos']['size'] : [$_FILES['photos']['size']];

        for ($i = 0; $i < count($names); $i++) {
            if ($errors[$i] !== UPLOAD_ERR_OK) {
                continue;
            }

            $tmpPath  = $tmpNames[$i];
            $origName = $names[$i];
            $size     = $sizes[$i];

            if ($size > 5 * 1024 * 1024) {
                continue; // max 5MB
            }

            $mimeType = @mime_content_type($tmpPath) ?: 'image/jpeg';
            if (!in_array($mimeType, $allowedMimes)) {
                continue;
            }

            $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedExts)) {
                continue;
            }
            $safeExt = ($ext === 'jpeg') ? 'jpg' : $ext;

            $filename     = "review_{$reviewId}_" . time() . "_" . bin2hex(random_bytes(4)) . "." . $safeExt;
            $relativePath = "review_photos/" . $filename;

            $uploadedSuccessfully = false;
            $url = "";

            if (S3Storage::isConfigured()) {
                try {
                    S3Storage::uploadFile($relativePath, $tmpPath, $mimeType);
                    $uploadedSuccessfully = true;
                    $url = S3Storage::getPhotoUrl($relativePath);
                } catch (Exception $e) {
                    error_log("R2 Upload failed: " . $e->getMessage());
                }
            }

            // Fallback to local uploads directory if R2 is not active
            if (!$uploadedSuccessfully) {
                $uploadDir = __DIR__ . '/../uploads/review_photos/';
                if (!is_dir($uploadDir)) {
                    @mkdir($uploadDir, 0777, true);
                }
                if (move_uploaded_file($tmpPath, $uploadDir . $filename)) {
                    $uploadedSuccessfully = true;
                    $url = $this->getUploadsBaseUrl() . $relativePath;
                }
            }

            if ($uploadedSuccessfully) {
                $stmt = $this->db->prepare("INSERT INTO review_photos (review_id, file_path) VALUES (?, ?)");
                $stmt->execute([$reviewId, $relativePath]);
                $uploaded[] = [
                    "photo_id" => (int) $this->db->lastInsertId(),
                    "url"      => $url,
                ];
            }
        }

        echo json_encode(["success" => true, "photos" => $uploaded]);
    }

    public function deleteReviewPhoto($photoId) {
        $user = requireRole('service_provider');

        $stmt = $this->db->prepare("SELECT provider_id FROM service_providers WHERE user_id = ?");
        $stmt->execute([$user['user_id']]);
        $provider = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$provider) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Service provider profile not found"]);
            return;
        }

        $stmt = $this->db->prepare("
            SELECT rp.photo_id, rp.file_path FROM review_photos rp
            JOIN reviews r ON r.review_id = rp.review_id
            WHERE rp.photo_id = ? AND r.provider_id = ?
        ");
        $stmt->execute([$photoId, $provider['provider_id']]);
        $photo = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$photo) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Photo not found or permission denied"]);
            return;
        }

        // Delete from R2 if configured
        try {
            S3Storage::deleteFile($photo['file_path']);
        } catch (Exception $e) {
            error_log("R2 Delete error: " . $e->getMessage());
        }

        // Delete local copy if present
        $localFilePath = __DIR__ . '/../uploads/' . $photo['file_path'];
        if (file_exists($localFilePath)) {
            @unlink($localFilePath);
        }

        $stmt = $this->db->prepare("DELETE FROM review_photos WHERE photo_id = ?");
        $stmt->execute([$photoId]);

        echo json_encode(["success" => true, "message" => "Photo deleted successfully"]);
    }
}