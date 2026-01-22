<?php
require_once __DIR__ . '/../../../../config/verify-each-request.php';
require_once __DIR__ . '/../../../../config/database.php';

function getOrderHistory()
{
    global $conn;

    $email = $_GET['email'] ?? '';

    if ($email === '') {
        http_response_code(400);
        return [
            'success' => false,
            'message' => 'Email is required'
        ];
    }

    $stmt = $conn->prepare("
        SELECT 
            id AS order_id,
            total_amount,
            status,
            payment_status,
            created_at
        FROM orders
        WHERE email = ?
        ORDER BY created_at DESC
    ");

    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }

    $stmt->close();

    return [
        'success' => true,
        'orders' => $orders
    ];
}
