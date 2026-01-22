<?php
require_once __DIR__ . '/../../../../config/verify-each-request.php';
require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once __DIR__ . '/../../../../config/database.php';

use Ramsey\Uuid\Uuid;

function registerUser()
{
    global $conn;

    // Read JSON or form-data
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        $data = $_POST;
    }

    $name     = trim($data['name'] ?? '');
    $phone    = trim($data['phone'] ?? '');
    $address  = trim($data['address'] ?? '');
    $email    = trim($data['email'] ?? '');
    $password = $data['password'] ?? '';

    // ✅ Required fields validation
    if ($name === '' || $phone === '' || $address === '' || $email === '' || $password === '') {
        return [
            'success' => false,
            'message' => 'Name, phone, address, email, and password are required'
        ];
    }

    // ✅ Email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return [
            'success' => false,
            'message' => 'Invalid email format'
        ];
    }

    // ✅ Password validation
    if (strlen($password) < 6) {
        return [
            'success' => false,
            'message' => 'Password must be at least 6 characters'
        ];
    }

    // ✅ Check if email already exists
    $check = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $check->close();
        return [
            'success' => false,
            'message' => 'Email already registered'
        ];
    }
    $check->close();

    // ✅ Create new user
    $userId = Uuid::uuid4()->toString();
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("
        INSERT INTO users (
            id, name, phone, address, email, password, email_verified
        )
        VALUES (?, ?, ?, ?, ?, ?, '0')
    ");

    if (!$stmt) {
        return [
            'success' => false,
            'message' => 'Database prepare failed',
            'error'   => $conn->error
        ];
    }

    $stmt->bind_param(
        "ssssss",
        $userId,
        $name,
        $phone,
        $address,
        $email,
        $hashedPassword
    );

    if ($stmt->execute()) {
        $stmt->close();
        return [
            'success' => true,
            'message' => 'User registered successfully',
            'user_id' => $userId
        ];
    }

    $error = $stmt->error;
    $stmt->close();

    return [
        'success' => false,
        'message' => 'Registration failed',
        'error'   => $error
    ];
}
