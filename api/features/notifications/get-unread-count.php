<?php
require_once __DIR__ . '/../../../config/verify-each-request.php';
require_once __DIR__ . '/../../../config/database.php';

function getUnreadCount()
{
    global $conn;

    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        $data = $_POST;
    }

    $userId = $data['user_id'] ?? null;

    if (!$userId) {
        http_response_code(400);
        return [
            'success' => false,
            'message' => 'User ID required'
        ];
    }

    $stmt = $conn->prepare("
        SELECT COUNT(*) as unread_count
        FROM notifications
        WHERE user_id = ?
        AND is_read = 0
    ");

    if (!$stmt) {
        http_response_code(500);
        return [
            'success' => false,
            'message' => 'Database prepare error'
        ];
    }

    $stmt->bind_param("s", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    return [
        'success' => true,
        'unread_count' => (int)$row['unread_count']
    ];
}