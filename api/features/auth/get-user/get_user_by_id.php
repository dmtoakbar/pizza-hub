<?php
require_once __DIR__ . '/../../../../config/verify-each-request.php';
require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once __DIR__ . '/../../../../config/database.php';

function getUserById()
{
    global $conn;

    // Read JSON or form-data
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        $data = $_POST;
    }

    $userId = trim($data['user_id'] ?? '');

    // ✅ Validation
    if ($userId === '') {
        return [
            'success' => false,
            'message' => 'User ID is required'
        ];
    }

    // ✅ Fetch user (exclude password)
    $stmt = $conn->prepare("
        SELECT 
            id,
            name,
            phone,
            address,
            email,
            email_verified,
            created_at
        FROM users
        WHERE id = ?
        LIMIT 1
    ");

    if (!$stmt) {
        return [
            'success' => false,
            'message' => 'Database prepare failed',
            'error'   => $conn->error
        ];
    }

    $stmt->bind_param("s", $userId);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $stmt->close();
        return [
            'success' => false,
            'message' => 'User not found'
        ];
    }

    $user = $result->fetch_assoc();
    $stmt->close();

    return [
        'success' => true,
        'message' => 'User fetched successfully',
        'data'    => $user
    ];
}

?>