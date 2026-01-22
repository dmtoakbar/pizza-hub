<?php
require_once __DIR__ . '/../../../config/verify-each-request.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../../config/database.php';

use Ramsey\Uuid\Uuid;

function contactUs()
{
    global $conn;

    // Read JSON or POST
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        $data = $_POST;
    }

    $name = isset($data['name']) ? trim($data['name']) : '';
    $email = isset($data['email']) ? trim($data['email']) : '';
    $phone = isset($data['phone']) ? trim($data['phone']) : '';
    $subject = isset($data['subject']) ? trim($data['subject']) : '';
    $message = isset($data['message']) ? trim($data['message']) : '';

    // ✅ Basic validation
    if ($name === '' || $email === '' || $subject === '' || $message === '') {
        http_response_code(400);
        return [
            'success' => false,
            'message' => 'Name, email, subject and message are required'
        ];
    }

    // ✅ Email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        return [
            'success' => false,
            'message' => 'Invalid email format'
        ];
    }

    // ✅ Generate UUID
    $id = Uuid::uuid4()->toString();

    // ✅ Insert securely
    $stmt = $conn->prepare("
        INSERT INTO contact_us (id, name, email, phone, subject, message)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    if ($stmt === false) {
        http_response_code(500);
        return [
            'success' => false,
            'message' => 'Database error (prepare)',
            'error' => $conn->error
        ];
    }

    $stmt->bind_param(
        'ssssss',
        $id,
        $name,
        $email,
        $phone,
        $subject,
        $message
    );

    if (!$stmt->execute()) {
        $stmt->close();
        http_response_code(500);
        return [
            'success' => false,
            'message' => 'Failed to submit contact request'
        ];
    }

    $stmt->close();

    // ✅ Success response
    return [
        'success' => true,
        'message' => 'Your message has been sent successfully'
    ];
}
