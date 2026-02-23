<?php
require_once __DIR__ . '/../../../../config/verify-each-request.php';
require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once __DIR__ . '/../../../../config/database.php';


function deleteUser()
{
    global $conn;

    // Read JSON or form-data
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        $data = $_POST;
    }

    $userId = trim($data['user_id'] ?? '');
    $email  = trim($data['email'] ?? '');

    // ✅ Validation
    if ($userId === '' || $email === '') {
        return [
            'success' => false,
            'message' => 'User ID and Email are required'
        ];
    }

    // ✅ First check if user exists with both id and email
    $checkStmt = $conn->prepare("
        SELECT id 
        FROM users 
        WHERE id = ? AND email = ?
        LIMIT 1
    ");

    if (!$checkStmt) {
        return [
            'success' => false,
            'message' => 'Database prepare failed',
            'error'   => $conn->error
        ];
    }

    $checkStmt->bind_param("ss", $userId, $email);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if ($result->num_rows === 0) {
        $checkStmt->close();
        return [
            'success' => false,
            'message' => 'Invalid User ID or Email'
        ];
    }

    $checkStmt->close();

    // ✅ Delete user
    $deleteStmt = $conn->prepare("
        DELETE FROM users 
        WHERE id = ? AND email = ?
        LIMIT 1
    ");

    if (!$deleteStmt) {
        return [
            'success' => false,
            'message' => 'Delete prepare failed',
            'error'   => $conn->error
        ];
    }

    $deleteStmt->bind_param("ss", $userId, $email);
    $deleteStmt->execute();

    if ($deleteStmt->affected_rows > 0) {
        $deleteStmt->close();
        return [
            'success' => true,
            'message' => 'User deleted successfully'
        ];
    } else {
        $deleteStmt->close();
        return [
            'success' => false,
            'message' => 'Failed to delete user'
        ];
    }
}
