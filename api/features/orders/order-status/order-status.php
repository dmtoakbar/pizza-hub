<?php
require_once __DIR__ . '/../../../../config/verify-each-request.php';
require_once __DIR__ . '/../../../../config/database.php';

header('Content-Type: application/json');

function getOrderStatus()
{
    global $conn;

    $orderId = $_GET['order_id'] ?? '';

    if ($orderId === '') {
        http_response_code(400);
        return ['success' => false, 'message' => 'Order ID is required'];
    }

    /* =========================
       ORDER
    ========================== */
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

    /* =========================
       ORDER ITEMS (SNAPSHOT)
    ========================== */
    $stmt = $conn->prepare("
        SELECT 
            id,
            product_id,
            product_name,
            product_image,
            size,
            base_price,
            discount_percentage,
            final_price,
            quantity
        FROM order_items
        WHERE order_id = ?
    ");
    $stmt->bind_param('s', $orderId);
    $stmt->execute();
    $itemsResult = $stmt->get_result();
    $stmt->close();

    $items = [];

    while ($row = $itemsResult->fetch_assoc()) {

        /* =========================
           EXTRAS
        ========================== */
        $stmt = $conn->prepare("
            SELECT extra_name, extra_price
            FROM order_item_extras
            WHERE order_item_id = ?
        ");
        $stmt->bind_param('i', $row['id']);
        $stmt->execute();
        $extras = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $items[] = [
            'product_id'           => $row['product_id'],
            'product_name'         => $row['product_name'],
            'product_image'        => $row['product_image'],
            'size'                 => $row['size'],
            'base_price'           => (float)$row['base_price'],
            'discount_percentage'  => (float)$row['discount_percentage'],
            'final_price'          => (float)$row['final_price'],
            'quantity'             => (int)$row['quantity'],
            'extras'               => $extras
        ];
    }

    $order['items'] = $items;

    return [
        'success' => true,
        'order' => $order
    ];
}


