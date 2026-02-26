<?php
require_once __DIR__ . '/../../../config/verify-each-request.php';
require_once __DIR__ . '/../../../config/database.php';

function getNotifications()
{
    global $conn;

    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        $data = $_POST;
    }

    $userId = $data['user_id'] ?? null;

    // Boolean handling
    $onlyUnread = false;
    if (isset($data['only_unread'])) {
        $onlyUnread = filter_var($data['only_unread'], FILTER_VALIDATE_BOOLEAN);
    }

    $page  = isset($data['page']) ? max(1, (int)$data['page']) : 1;
    $limit = isset($data['limit']) ? max(1, (int)$data['limit']) : 20;

    if (!$userId) {
        http_response_code(400);
        return [
            'success' => false,
            'message' => 'User ID required'
        ];
    }

    $offset = ($page - 1) * $limit;

    $sql = "
        SELECT 
            id,
            title,
            message,
            type,
            reference_id,
            created_at,
            is_read
        FROM notifications
        WHERE user_id = ?
        AND is_read = 0
        ORDER BY created_at DESC
        LIMIT ?, ?
    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        http_response_code(500);
        return [
            'success' => false,
            'message' => 'Database prepare failed'
        ];
    }

    $stmt->bind_param("sii", $userId, $offset, $limit);

    if (!$stmt->execute()) {
        http_response_code(500);
        return [
            'success' => false,
            'message' => 'Database execute failed'
        ];
    }

    $result = $stmt->get_result();
    $notifications = [];

    while ($row = $result->fetch_assoc()) {
        $notifications[] = [
            'id' => $row['id'],
            'title' => $row['title'],
            'message' => $row['message'],
            'type' => $row['type'],
            'reference_id' => $row['reference_id'],
            'created_at' => $row['created_at'],
            'is_read' => (bool)$row['is_read']
        ];
    }

    $stmt->close();

    return [
        'success' => true,
        'data' => $notifications,
        'pagination' => [
            'page' => $page,
            'limit' => $limit
        ]
    ];
}
