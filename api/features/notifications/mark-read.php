<?php
require_once __DIR__ . '/../../../config/verify-each-request.php';
require_once __DIR__ . '/../../../config/database.php';

function markNotificationRead()
{
    global $conn;

    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        $data = $_POST;
    }

    $userId = $data['user_id'] ?? null;
    $notificationId = $data['notification_id'] ?? null;

    if (!$userId || !$notificationId) {
        http_response_code(400);
        return [
            'success' => false,
            'message' => 'User ID and Notification ID required'
        ];
    }

    $stmt = $conn->prepare("
        UPDATE notifications
        SET is_read = 1,
            read_at = NOW()
        WHERE id = ?
        AND user_id = ?
        LIMIT 1
    ");

    if (!$stmt) {
        http_response_code(500);
        return [
            'success' => false,
            'message' => 'Database prepare error'
        ];
    }

    $stmt->bind_param("ss", $notificationId, $userId);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        return [
            'success' => true,
            'message' => 'Notification marked as read'
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Notification not found or already read'
        ];
    }
}