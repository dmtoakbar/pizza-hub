<?php
require_once __DIR__ . '/../../../config/verify-each-request.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../../config/database.php';

use Ramsey\Uuid\Uuid;

function submitReport()
{
    global $conn;

    // Read JSON or POST data
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        $data = $_POST;
    }

    $name = isset($data['name']) ? trim($data['name']) : '';
    $email = isset($data['email']) ? trim($data['email']) : '';
    $phone = isset($data['phone']) ? trim($data['phone']) : '';
    $address = isset($data['address']) ? trim($data['address']) : '';
    $orderId = isset($data['orderId']) ? trim($data['orderId']) : '';
    $issue = isset($data['issue']) ? trim($data['issue']) : '';
    $issueMessage = isset($data['issueMessage']) ? trim($data['issueMessage']) : '';

    // ✅ Basic validation
    if ($name === '' || $email === '' || $orderId === '' || $issue === '' || $issueMessage === '') {
        http_response_code(400);
        return [
            'success' => false,
            'message' => 'Name, email, order ID, issue and message are required'
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
        INSERT INTO report (id, name, email, phone, address, order_id, issue, issue_message)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
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
        'ssssssss',
        $id,
        $name,
        $email,
        $phone,
        $address,
        $orderId,
        $issue,
        $issueMessage
    );

    if (!$stmt->execute()) {
        $stmt->close();
        http_response_code(500);
        return [
            'success' => false,
            'message' => 'Failed to submit report'
        ];
    }

    $stmt->close();

    // ✅ Success response
    return [
        'success' => true,
        'message' => 'Your report has been submitted successfully'
    ];
}
