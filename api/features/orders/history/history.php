<?php
require_once __DIR__ . '/../../../../config/verify-each-request.php';
require_once __DIR__ . '/../../../../config/database.php';

header('Content-Type: application/json');

function getOrderHistory()
{
    global $conn;

    $email  = $_GET['email'] ?? '';
    $status = $_GET['status'] ?? 'all';

    if ($email === '') {
        http_response_code(400);

        return [
            'success' => false,
            'message' => 'Email is required'
        ];
    }

    $allowedStatuses = [
        'all',
        'pending',
        'accepted',
        'preparing',
        'ready',
        'out_for_delivery',
        'delivered',
        'cancelled'
    ];

    if (!in_array($status, $allowedStatuses)) {
        http_response_code(400);

        return [
            'success' => false,
            'message' => 'Invalid status'
        ];
    }

    $query = "
        SELECT 
            o.id AS order_id,
            o.total_amount,
            o.status,
            o.payment_status,
            o.payment_method,
            o.created_at,

            (
                SELECT COUNT(*)
                FROM order_items oi_count
                WHERE oi_count.order_id = o.id
            ) AS total_items,

            oi.product_name,
            oi.product_image,
            oi.quantity,
            oi.final_price

        FROM orders o

        LEFT JOIN order_items oi 
            ON oi.id = (
                SELECT id
                FROM order_items
                WHERE order_id = o.id
                ORDER BY id ASC
                LIMIT 1
            )

        WHERE o.email = ?
    ";


    if ($status === 'accepted') {

        $query .= "
        AND o.status IN (
            'pending',
            'accepted',
            'preparing',
            'ready',
            'out_for_delivery'
        )
    ";
    } elseif ($status !== 'all') {

        $query .= " AND o.status = ?";
    }

    $query .= " ORDER BY o.created_at DESC";

    $stmt = $conn->prepare($query);

    if (
        $status !== 'all' &&
        $status !== 'accepted'
    ) {

        $stmt->bind_param('ss', $email, $status);
    } else {

        $stmt->bind_param('s', $email);
    }

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

            'total_items'    => (int)$row['total_items'],
            'first_item' => [
                'product_name'  => $row['product_name'],
                'product_image' => $row['product_image'],
                'quantity'      => (int)$row['quantity'],
                'final_price'   => (float)$row['final_price'],
            ],
        ];
    }

    $stmt->close();

    return [
        'success' => true,
        'orders'  => $orders
    ];
}
