<?php

require_once __DIR__ . '/../../../../vendor/autoload.php';
use Ramsey\Uuid\Uuid;

// Connect to DB
require_once __DIR__ . '/../../../../config/index.php';

// Read JSON input
$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['name']) || empty($data['email'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Name and email are required']);
    exit;
}

// Generate UUID
$userId = Uuid::uuid4()->toString();

// Insert user into DB
$stmt = $conn->prepare("INSERT INTO users (id, name, email) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $userId, $data['name'], $data['email']);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'user_id' => $userId,
        'message' => 'User created successfully'
    ]);
} else {
    echo json_encode(['error' => 'Failed to create user']);
}
?>
