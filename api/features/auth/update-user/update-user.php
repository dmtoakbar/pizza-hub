<?php
require_once __DIR__ . '/../../../../config/verify-each-request.php';
require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once __DIR__ . '/../../../../config/database.php';


function updateUser()
{
    global $conn;

    // Read JSON or form-data
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        $data = $_POST;
    }

    $userId   = trim($data['user_id'] ?? '');
    $name     = trim($data['name'] ?? '');
    $phone    = trim($data['phone'] ?? '');
    $address  = trim($data['address'] ?? '');
    $password = $data['password'] ?? null; // optional

    // ✅ Required validation
    if ($userId === '' || $name === '' || $phone === '' || $address === '') {
        return [
            'success' => false,
            'message' => 'User ID, name, phone, and address are required'
        ];
    }

    // ✅ Phone validation (10 digits)
    if (!preg_match('/^[0-9]{10}$/', $phone)) {
        return [
            'success' => false,
            'message' => 'Invalid phone number'
        ];
    }

    // ✅ Check if user exists
    $check = $conn->prepare("SELECT id FROM users WHERE id = ? LIMIT 1");
    $check->bind_param("s", $userId);
    $check->execute();
    $check->store_result();

    if ($check->num_rows === 0) {
        $check->close();
        return [
            'success' => false,
            'message' => 'User not found'
        ];
    }
    $check->close();

    // ✅ Update query (email NOT included)
    if ($password && strlen($password) >= 6) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("
            UPDATE users
            SET name = ?, phone = ?, address = ?, password = ?
            WHERE id = ?
            LIMIT 1
        ");

        $stmt->bind_param(
            "sssss",
            $name,
            $phone,
            $address,
            $hashedPassword,
            $userId
        );
    } else {
        $stmt = $conn->prepare("
            UPDATE users
            SET name = ?, phone = ?, address = ?
            WHERE id = ?
            LIMIT 1
        ");

        $stmt->bind_param(
            "ssss",
            $name,
            $phone,
            $address,
            $userId
        );
    }

    if (!$stmt) {
        return [
            'success' => false,
            'message' => 'Database prepare failed',
            'error'   => $conn->error
        ];
    }

    if ($stmt->execute()) {
        $stmt->close();
        return [
            'success' => true,
            'message' => 'User profile updated successfully'
        ];
    }

    $error = $stmt->error;
    $stmt->close();

    return [
        'success' => false,
        'message' => 'Profile update failed',
        'error'   => $error
    ];
}


?>