<?php
require_once __DIR__ . '/../../../../config/verify-each-request.php';
require_once __DIR__ . '/../../../../config/database.php';

function getOrderDetails() {
    global $conn;

    $orderId = $_GET['order_id'] ?? '';

    if ($orderId === '') {
        http_response_code(400);
        return ['success' => false, 'message' => 'Order ID required'];
    }

    $stmt = $conn->prepare("
        SELECT 
            oi.id AS item_id,
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

    return [
        'success' => true,
        'items' => array_values($items)
    ];
}

