<?php
require_once __DIR__ . '/../../../../config/verify-each-request.php';
require_once __DIR__ . '/../../../../config/database.php';

function getOrderStatus() {
    global $conn;

    $orderId = $_GET['order_id'] ?? '';

    if ($orderId === '') {
        http_response_code(400);
        return ['success' => false, 'message' => 'Order ID is required'];
    }

    $stmt = $conn->prepare("
        SELECT 
            id AS order_id,
            status,
            payment_status,
            total_amount,
            created_at
        FROM orders
        WHERE id = ?
        LIMIT 1
    ");
    $stmt->bind_param('s', $orderId);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$order) {
        http_response_code(404);
        return ['success' => false, 'message' => 'Order not found'];
    }

    return [
        'success' => true,
        'order' => $order
    ];
}

