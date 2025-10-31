<?php
require_once __DIR__ . '/../../../../config/verify-each-request.php';
require_once __DIR__ . '/../../../../vendor/autoload.php';

use Ramsey\Uuid\Uuid;

require_once __DIR__ . '/../../../../config/database.php';

function registerUser()
{
    global $conn;

    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data) {
        $data = $_POST;
    }

    $name = isset($data['name']) ? trim($data['name']) : '';
    $email = isset($data['email']) ? trim($data['email']) : '';
    $password = isset($data['password']) ? $data['password'] : '';

    if ($name === '' || $email === '' || $password === '') {
        http_response_code(400);
        return ['success' => false, 'message' => 'Name, email, and password are required'];
        exit;
    }

    // validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        return ['success' => false, 'message' => 'Invalid email address'];
        exit;
    }

    // Optional: password policy (length example)
    if (strlen($password) < 6) {
        http_response_code(400);
        return ['success' => false, 'message' => 'Password must be at least 6 characters'];
        exit;
    }

    // Check if email already exists (prepared statement)
    $check = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    if ($check === false) {
        return ['success' => false, 'message' => 'Database error (prepare)', 'error' => $conn->error];
        exit;
    }
    $check->bind_param('s', $email);
    $check->execute();
    $check->store_result();
    if ($check->num_rows > 0) {
        // email already registered
        return ['success' => false, 'message' => 'Email already in use'];
        exit;
    }
    $check->close();

    // All good — create user
    $userId = Uuid::uuid4()->toString();
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (id, name, email, password) VALUES (?, ?, ?, ?)");
    if ($stmt === false) {
        return ['success' => false, 'message' => 'Database error (prepare)', 'error' => $conn->error];
        exit;
    }
    $stmt->bind_param("ssss", $userId, $name, $email, $hashedPassword);

    if ($stmt->execute()) {
        $stmt->close();
        return ['success' => true, 'user_id' => $userId, 'message' => 'User created successfully'];
        exit;
    }

    // Handle duplicate email race condition (just in case)
    $errno = $stmt->errno;
    $error = $stmt->error;
    $stmt->close();

    if ($errno === 1062) { // MySQL duplicate entry
        return ['success' => false, 'message' => 'Email already in use (duplicate)'];
        exit;
    }

    return ['success' => false, 'message' => 'Failed to create user', 'error' => $error];
    exit;
}
