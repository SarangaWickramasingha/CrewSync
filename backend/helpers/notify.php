<?php
require_once __DIR__ . '/clean.php';

// Insert a notification row for any user_id (does NOT require auth).
// Used by backend controllers to notify the "other party" after an event.
// $db: PDO connection, $userId: target user_id, $title: notification type/category,
// $message: message text (sanitized to a safe inline-HTML subset before storing).
function notify_user($db, $userId, $title, $message) {
    if (!$userId || empty($message)) return;
    $message = sanitize_notification_html($message);
    if ($message === '') return;
    $stmt = $db->prepare(
        "INSERT INTO notifications (user_id, title, message, is_read) VALUES (?, ?, ?, 0)"
    );
    $stmt->execute([$userId, $title, $message]);
    return (int) $db->lastInsertId();
}
