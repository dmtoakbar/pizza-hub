<?php

require_once __DIR__ . '/../../../../config/verify-each-request.php';
require_once __DIR__ . '/../../../../config/database.php';

header('Content-Type: application/json');

function trackOrder()
{
    global $conn;

    /* =========================
       GET ORDER ID
    ========================== */
    $orderId = $_GET['order_id'] ?? '';

    if ($orderId === '') {

        http_response_code(400);

        return [
            'success' => false,
            'message' => 'Order id required'
        ];
    }

    /* =========================
       FETCH ORDER
    ========================== */
    $stmt = $conn->prepare("
        SELECT 
            id,
            status,
            created_at
        FROM orders
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->bind_param('s', $orderId);

    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows === 0) {

        return [
            'success' => false,
            'message' => 'Order not found'
        ];
    }

    $order = $result->fetch_assoc();

    $stmt->close();

    /* =========================
       ORDER DATA
    ========================== */
    $status = $order['status'];

    $createdAt = strtotime($order['created_at']);

    /* =========================
       ALL TRACKING STEPS
    ========================== */
    $allSteps = [

        [
            'status' => 'accepted',

            'title'  => 'Order Confirmed',

            'time'   => date(
                'Y-m-d H:i:s',
                $createdAt
            ),
        ],

        [
            'status' => 'preparing',

            'title'  => 'Preparing Your Order',

            'time'   => date(
                'Y-m-d H:i:s',
                strtotime('+5 minutes', $createdAt)
            ),
        ],

        [
            'status' => 'out_for_delivery',

            'title'  => 'On The Way',

            'time'   => date(
                'Y-m-d H:i:s',
                strtotime('+15 minutes', $createdAt)
            ),
        ],

        [
            'status' => 'delivered',

            'title'  => 'Delivered',

            'time'   => date(
                'Y-m-d H:i:s',
                strtotime('+35 minutes', $createdAt)
            ),
        ],
    ];

    /* =========================
       STATUS LEVEL FLOW
    ========================== */
    $statusFlow = [

        'pending'          => 0,

        'accepted'         => 1,

        'preparing'        => 2,

        'ready'            => 2,

        'out_for_delivery' => 3,

        'delivered'        => 4,

        'cancelled'        => -1,
    ];

    $currentLevel = $statusFlow[$status] ?? 0;

    $isCancelled = $status === 'cancelled';

    /* =========================
       BUILD TRACKING STEPS
    ========================== */
    $steps = [];

    foreach ($allSteps as $index => $step) {

        $stepLevel = $index + 1;

        $steps[] = [

            'title' => $step['title'],

            'time'  => $step['time'],

            /* =========================
               COMPLETED STEP
            ========================== */
            'completed' =>
            $currentLevel > $stepLevel,

            /* =========================
               CURRENT STEP
            ========================== */
            'current' =>
            $currentLevel == $stepLevel,

            /* =========================
               UPCOMING STEP
            ========================== */
            'pending' =>
            $currentLevel < $stepLevel,
        ];
    }

    /* =========================
       ESTIMATED TIME
    ========================== */
    $estimatedTime = match ($status) {

        'pending'          => '30-40 min',

        'accepted'         => '25-30 min',

        'preparing'        => '20-25 min',

        'ready'            => '15-20 min',

        'out_for_delivery' => '10-15 min',

        'delivered'        => 'Delivered',

        'cancelled'        => 'Cancelled',

        default            => '20-30 min',
    };

    /* =========================
       RESPONSE
    ========================== */
    return [

        'success' => true,

        'data' => [

            'order_id' => $order['id'],

            'status' => $status,

            'is_cancelled' => $isCancelled,

            'estimated_time' => $estimatedTime,

            /* =========================
               DELIVERY BOY
            ========================== */
            'delivery_boy_name' => 'John Doe',

            'delivery_boy_image' =>
            'https://i.pravatar.cc/300',

            /* =========================
               MAP IMAGE
            ========================== */
            'map_image' =>
            'https://images.unsplash.com/photo-1502920917128-1aa500764cbd?q=80&w=1200&auto=format&fit=crop',
            /* =========================
               TRACKING STEPS
            ========================== */
            'steps' => $steps
        ]
    ];
}
