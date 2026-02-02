<?php
require_once __DIR__ . '/../../../../config/verify-each-request.php';
require_once __DIR__ . '/../../../../config/database.php';

function getOrderDetails()
{
    global $conn;

    $orderId = $_GET['order_id'] ?? '';

    if ($orderId === '') {
        http_response_code(400);
        return ['success' => false, 'message' => 'Order ID required'];
    }

    // 1️⃣ Get order info including user details
    $stmt = $conn->prepare("
        SELECT 
            o.id AS order_id,
            o.username,
            o.email,
            o.phone,
            o.address,
            o.total_amount,
            o.payment_method,
            o.payment_status,
            o.status
        FROM orders o
        WHERE o.id = ?
        LIMIT 1
    ");
    $stmt->bind_param('s', $orderId);
    $stmt->execute();
    $orderResult = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$orderResult) {
        http_response_code(404);
        return ['success' => false, 'message' => 'Order not found'];
    }

    // 2️⃣ Get order items + extras
    $stmt = $conn->prepare("
        SELECT 
            oi.id AS item_id,
            oi.product_id,
            oi.product_name,
            oi.quantity,
            oi.product_price,
            oi.product_image,
            e.extra_name,
            e.extra_price
        FROM order_items oi
        LEFT JOIN order_item_extras e ON oi.id = e.order_item_id
        WHERE oi.order_id = ?
    ");
    $stmt->bind_param('s', $orderId);
    $stmt->execute();
    $result = $stmt->get_result();

    $items = [];
    while ($row = $result->fetch_assoc()) {
        $itemId = $row['item_id'];

        if (!isset($items[$itemId])) {
            $items[$itemId] = [
                'product_id' => $row['product_id'],
                'product_name' => $row['product_name'],
                'quantity' => $row['quantity'],
                'price' => $row['product_price'],
                'image' => $row['product_image'],
                'extras' => []
            ];
        }

        if ($row['extra_name']) {
            $items[$itemId]['extras'][] = [
                'name' => $row['extra_name'],
                'price' => $row['extra_price']
            ];
        }
    }
    $stmt->close();

    return [
        'success' => true,
        'order' => [
            'id' => $orderResult['order_id'],
            'username' => $orderResult['username'],
            'email' => $orderResult['email'],
            'phone' => $orderResult['phone'],
            'address' => $orderResult['address'],
            'total_amount' => $orderResult['total_amount'],
            'payment_method' => $orderResult['payment_method'],
            'payment_status' => $orderResult['payment_status'],
            'status' => $orderResult['status'],
        ],
        'items' => array_values($items)
    ];
}
