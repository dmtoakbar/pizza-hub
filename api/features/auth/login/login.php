<?php
require_once __DIR__ . '/../../../../config/verify-each-request.php';
require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once __DIR__ . '/../../../../config/database.php';

function loginUser()
{
    global $conn;

    $data = json_decode(file_get_contents('php://input'), true);

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

    // ✅ Create simple session or token (example using UUID)
    $sessionToken = bin2hex(random_bytes(32)); // secure 64-char token
    $expiry = date('Y-m-d H:i:s', strtotime('+30 minutes'));

    // Optionally store session in DB
    $tokenStmt = $conn->prepare("INSERT INTO user_sessions (user_id, token, expires_at) VALUES (?, ?, ?)");
    if ($tokenStmt) {
        $tokenStmt->bind_param('sss', $user['id'], $sessionToken, $expiry);
        $tokenStmt->execute();
        $tokenStmt->close();
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

?>
