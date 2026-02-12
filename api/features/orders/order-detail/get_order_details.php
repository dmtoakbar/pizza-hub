<?php
require_once __DIR__ . '/../../../../config/verify-each-request.php';
require_once __DIR__ . '/../../../../config/database.php';

header('Content-Type: application/json');

function getOrderDetails()
{
    global $conn;

    if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Order ID is required'
        ]);
        exit;
    }

    $orderId = (int) $_GET['order_id'];

    /* ===================== FETCH ORDER ===================== */

    $orderStmt = $conn->prepare("
    SELECT 
        id,
        username,
        email,
        phone,
        address,
        total_amount,
        payment_method,
        payment_status,
        status,
        created_at
    FROM orders
    WHERE id = ?
    LIMIT 1
");

    $orderStmt->bind_param("i", $orderId);
    $orderStmt->execute();
    $orderResult = $orderStmt->get_result();

    if ($orderResult->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Order not found'
        ]);
        exit;
    }

    $order = $orderResult->fetch_assoc();
    $orderStmt->close();

    /* ===================== FETCH ORDER ITEMS ===================== */

    $itemStmt = $conn->prepare("
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

    $itemStmt->bind_param("i", $orderId);
    $itemStmt->execute();
    $itemResult = $itemStmt->get_result();

    $itemsMap = [];
    $orderItemIds = [];

    while ($row = $itemResult->fetch_assoc()) {
        $row['base_price'] = (float) $row['base_price'];
        $row['discount_percentage'] = (float) $row['discount_percentage'];
        $row['final_price'] = (float) $row['final_price'];
        $row['quantity'] = (int) $row['quantity'];
        $row['extras'] = [];

        $itemsMap[$row['id']] = $row;
        $orderItemIds[] = $row['id'];
    }

    $itemStmt->close();

    /* ===================== FETCH EXTRAS (BULK) ===================== */

    if (!empty($orderItemIds)) {
        $placeholders = implode(',', array_fill(0, count($orderItemIds), '?'));
        $types = str_repeat('i', count($orderItemIds));

        $extraStmt = $conn->prepare("
        SELECT 
            order_item_id,
            extra_name,
            extra_price
        FROM order_item_extras
        WHERE order_item_id IN ($placeholders)
    ");

        $extraStmt->bind_param($types, ...$orderItemIds);
        $extraStmt->execute();
        $extraResult = $extraStmt->get_result();

        while ($extra = $extraResult->fetch_assoc()) {
            $itemsMap[$extra['order_item_id']]['extras'][] = [
                'extra_name'  => $extra['extra_name'],
                'extra_price' => (float) $extra['extra_price'],
            ];
        }

        $extraStmt->close();
    }

    /* ===================== FINAL RESPONSE ===================== */

    $response = [
        'success' => true,
        'data' => [
            'order' => [
                'id'             => (int) $order['id'],
                'username'       => $order['username'],
                'email'          => $order['email'],
                'phone'          => $order['phone'],
                'address'        => $order['address'],
                'total_amount'   => (float) $order['total_amount'],
                'payment_method' => $order['payment_method'],
                'payment_status' => $order['payment_status'],
                'status'         => $order['status'],
                'created_at'     => $order['created_at'],
            ],
            'items' => array_values(array_map(function ($item) {
                return [
                    'product_id'          => $item['product_id'],
                    'product_name'        => $item['product_name'],
                    'product_image'       => $item['product_image'],
                    'size'                => $item['size'],
                    'base_price'          => $item['base_price'],
                    'discount_percentage' => $item['discount_percentage'],
                    'final_price'         => $item['final_price'],
                    'quantity'            => $item['quantity'],
                    'extras'              => $item['extras'],
                ];
            }, $itemsMap)),
        ]
    ];


    return $response;
}
