<?php
require_once __DIR__ . '/../../../../config/verify-each-request.php';
require_once __DIR__ . '/../../../../config/database.php';

header('Content-Type: application/json');

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

    /* =========================
       FETCH ORDER HISTORY
    ========================== */
    $stmt = $conn->prepare("
        SELECT 
            id AS order_id,
            total_amount,
            status,
            payment_status,
            payment_method,
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
        $orders[] = [
            'order_id'       => $row['order_id'],
            'total_amount'   => (float)$row['total_amount'],
            'status'         => $row['status'],
            'payment_status' => $row['payment_status'],
            'payment_method' => $row['payment_method'],
            'created_at'     => $row['created_at'],
        ];
    }

    $stmt->close();

    return [
        'success' => true,
        'orders'  => $orders
    ];
}

