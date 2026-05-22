<?php
require_once __DIR__ . '/../../../../config/verify-each-request.php';
require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once __DIR__ . '/../../../../config/database.php';

function changePassword()
{
    global $conn;

    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data) {
        $data = $_POST;
    }

    $userId = isset($data['user_id']) ? trim($data['user_id']) : '';
    $currentPassword = isset($data['current_password']) ? $data['current_password'] : '';
    $newPassword = isset($data['new_password']) ? $data['new_password'] : '';

    // ✅ Basic validation
    if ($userId === '' || $currentPassword === '' || $newPassword === '') {
        http_response_code(400);
        return [
            'success' => false,
            'message' => 'User ID, current password, and new password are required'
        ];
    }

    // ✅ Password length validation
    if (strlen($newPassword) < 6) {
        http_response_code(400);
        return [
            'success' => false,
            'message' => 'New password must be at least 6 characters long'
        ];
    }

    // ✅ Fetch user securely
    $stmt = $conn->prepare("SELECT id, password FROM users WHERE id = ? LIMIT 1");

    if ($stmt === false) {
        http_response_code(500);
        return [
            'success' => false,
            'message' => 'Database error (prepare)',
            'error' => $conn->error
        ];
    }

    $stmt->bind_param('s', $userId);
    $stmt->execute();

    $result = $stmt->get_result();

    // ✅ User not found
    if ($result->num_rows === 0) {
        $stmt->close();

        http_response_code(404);
        return [
            'success' => false,
            'message' => 'User not found'
        ];
    }

    $user = $result->fetch_assoc();
    $stmt->close();

    // ✅ Verify current password
    if (!password_verify($currentPassword, $user['password'])) {
        http_response_code(401);

        return [
            'success' => false,
            'message' => 'Current password is incorrect'
        ];
    }

    // ✅ Prevent same password reuse
    if (password_verify($newPassword, $user['password'])) {
        http_response_code(400);

        return [
            'success' => false,
            'message' => 'New password cannot be same as current password'
        ];
    }

    // ✅ Hash new password
    $newHashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    // ✅ Update password
    $update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");

    if ($update === false) {
        http_response_code(500);

        return [
            'success' => false,
            'message' => 'Database error (update prepare)',
            'error' => $conn->error
        ];
    }

    $update->bind_param('ss', $newHashedPassword, $userId);

    if (!$update->execute()) {
        $update->close();

        http_response_code(500);

        return [
            'success' => false,
            'message' => 'Failed to update password'
        ];
    }

    $update->close();
    // ✅ Success response
    return [
        'success' => true,
        'message' => 'Password changed successfully'
    ];
}