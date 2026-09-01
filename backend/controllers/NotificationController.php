<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/auth.php';

class NotificationController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // Get notifications for the authenticated user
    public function getNotifications() {
        $user = requireAuth();
        
        $stmt = $this->db->prepare("
            SELECT notification_id as id, title as type, message as text, is_read as `read`, created_at as time
            FROM notifications 
            WHERE user_id = ? 
            ORDER BY created_at DESC
        ");
        $stmt->execute([$user['user_id']]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Convert SQL datetime to a reader-friendly relative format
        foreach ($rows as &$row) {
            $row['read'] = (bool)$row['read'];
            $timestamp = strtotime($row['time']);
            $time = date("g:i A", $timestamp);

            $now = time();
            $diffDays = (int) floor(($now - $timestamp) / 86400);
            $sameDay = date('Y-m-d', $timestamp) === date('Y-m-d', $now);
            $yesterday = date('Y-m-d', $timestamp) === date('Y-m-d', $now - 86400);

            if ($sameDay) {
                $row['time'] = "Today, " . $time;
            } elseif ($yesterday) {
                $row['time'] = "Yesterday, " . $time;
            } elseif ($diffDays < 7) {
                $row['time'] = date('l, g:i A', $timestamp);
            } else {
                $row['time'] = date('M j, Y g:i A', $timestamp);
            }
        }

        echo json_encode(["success" => true, "notifications" => $rows]);
    }

    // Create a new notification
    public function createNotification() {
        $user = requireAuth();
        $data = json_decode(file_get_contents("php://input"), true) ?? [];
        
        $type = $data['type'] ?? 'system';
        $message = $data['text'] ?? '';
        
        if (empty($message)) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Notification text is required"]);
            return;
        }

        $stmt = $this->db->prepare("
            INSERT INTO notifications (user_id, title, message, is_read) 
            VALUES (?, ?, ?, 0)
        ");
        $stmt->execute([$user['user_id'], $type, $message]);

        echo json_encode(["success" => true, "notif_id" => $this->db->lastInsertId()]);
    }

    // Mark notifications as read
    public function markRead() {
        $user = requireAuth();
        $data = json_decode(file_get_contents("php://input"), true) ?? [];
        $notifId = isset($data['id']) ? intval($data['id']) : null;

        if ($notifId) {
            $stmt = $this->db->prepare("UPDATE notifications SET is_read = 1 WHERE notification_id = ? AND user_id = ?");
            $stmt->execute([$notifId, $user['user_id']]);
        } else {
            $stmt = $this->db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
            $stmt->execute([$user['user_id']]);
        }

        echo json_encode(["success" => true]);
    }

    // Delete a notification
    public function deleteNotification($notifId) {
        $user = requireAuth();
        
        $stmt = $this->db->prepare("DELETE FROM notifications WHERE notification_id = ? AND user_id = ?");
        $stmt->execute([$notifId, $user['user_id']]);

        echo json_encode(["success" => true]);
    }
}