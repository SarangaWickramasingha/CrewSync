<?php
// Insert a notification row for any user_id (does NOT require auth).
// Used by backend controllers to notify the "other party" after an event.
// $db: PDO connection, $userId: target user_id, $title: notification type/category,
// $message: HTML-safe message text.
function notify_user($db, $userId, $title, $message) {
    if (!$userId || empty($message)) return;
    $stmt = $db->prepare(
        "INSERT INTO notifications (user_id, title, message, is_read) VALUES (?, ?, ?, 0)"
    );
    $stmt->execute([$userId, $title, $message]);
    return (int) $db->lastInsertId();
}
