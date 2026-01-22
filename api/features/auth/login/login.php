<?php
require_once __DIR__ . '/../../../../config/verify-each-request.php';
require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once __DIR__ . '/../../../../config/database.php';

use Ramsey\Uuid\Uuid;

function loginUser()
{
    global $conn;

    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data) {
        $data = $_POST;
    }

    $email = isset($data['email']) ? trim($data['email']) : '';
    $password = isset($data['password']) ? $data['password'] : '';

    // ✅ Basic validation
    if ($email === '' || $password === '') {
        http_response_code(400);
        return ['success' => false, 'message' => 'Email and password are required'];
        exit;
    }

    // ✅ Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        return ['success' => false, 'message' => 'Invalid email format'];
        exit;
    }

    // ✅ Fetch user securely
    $stmt = $conn->prepare("SELECT id, name, email, password FROM users WHERE email = ? LIMIT 1");
    if ($stmt === false) {
        return ['success' => false, 'message' => 'Database error (prepare)', 'error' => $conn->error];
        exit;
    }

    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $stmt->close();
        return ['success' => false, 'message' => 'Invalid email or password'];
        exit;
    }

    $user = $result->fetch_assoc();
    $stmt->close();

    // ✅ Verify password
    if (!password_verify($password, $user['password'])) {
        return ['success' => false, 'message' => 'Invalid email or password'];
        exit;
    }

    // ✅ Optional: rehash if algorithm updated
    if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        $update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $update->bind_param("ss", $newHash, $user['id']);
        $update->execute();
        $update->close();
    }

    // Generate new session details
    $sessionId = Uuid::uuid4()->toString();
    $sessionToken = bin2hex(random_bytes(32));
    $expiry = date('Y-m-d H:i:s', strtotime('+30 minutes'));

    // ✅ Check if a session already exists for the user
    $check = $conn->prepare("SELECT id FROM user_sessions WHERE user_id = ? LIMIT 1");
    $check->bind_param('s', $user['id']);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        // ✅ Update existing session
        $existing = $result->fetch_assoc();
        $check->close();

        $update = $conn->prepare("UPDATE user_sessions SET token = ?, expires_at = ? WHERE id = ?");
        $update->bind_param('sss', $sessionToken, $expiry, $existing['id']);
        $update->execute();
        $update->close();
    } else {
        // ✅ Create new session
        $check->close();

        $insert = $conn->prepare("INSERT INTO user_sessions (id, user_id, token, expires_at) VALUES (?, ?, ?, ?)");
        $insert->bind_param('ssss', $sessionId, $user['id'], $sessionToken, $expiry);
        $insert->execute();
        $insert->close();
    }
    // ✅ Successful login
    return [
        'success' => true,
        'message' => 'Login successful',
        'user' => [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
        ],
        'token' => $sessionToken,
        'expires_at' => $expiry
    ];
}
