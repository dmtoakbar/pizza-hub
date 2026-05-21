<?php
require_once __DIR__ . '/../../../config/verify-each-request.php';
require_once __DIR__ . '/../../../config/database.php';

function getOffers()
{
    global $conn;

    /* =========================
       GET REQUEST DATA
    ========================== */

    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data) {
        $data = $_POST;
    }

    /* =========================
       FILTERS
    ========================== */

    $offerType = trim($data['offer_type'] ?? 'all');

    $page  = isset($data['page'])
        ? max(1, (int)$data['page'])
        : 1;

    $limit = isset($data['limit'])
        ? max(1, (int)$data['limit'])
        : 20;

    $offset = ($page - 1) * $limit;

    /* =========================
       BASE QUERY
    ========================== */

    $sql = "
        SELECT
            c.id,
            c.code,
            c.title,
            c.description,

            c.offer_type,

            c.discount_type,
            c.discount_value,
            c.max_discount_amount,

            c.min_order_amount,

            c.badge_text,
            c.coupon_image,
            c.background_color,
            c.button_text,

            c.start_date,
            c.end_date,

            c.status,
            c.created_at,

            cat.name AS category_name,
            p.name AS product_name

        FROM coupons c

        LEFT JOIN categories cat
            ON c.category_id = cat.id

        LEFT JOIN products p
            ON c.product_id = p.id

        WHERE c.status = 1

        AND (
            c.start_date IS NULL
            OR c.start_date <= NOW()
        )

        AND (
            c.end_date IS NULL
            OR c.end_date >= NOW()
        )
    ";

    $params = [];
    $types  = "";

    /* =========================
       OFFER TYPE FILTER
    ========================== */

    if (
        !empty($offerType)
        && $offerType !== 'all'
    ) {

        $sql .= " AND c.offer_type = ? ";

        $params[] = $offerType;
        $types .= "s";
    }

    /* =========================
       ORDER
    ========================== */

    $sql .= "
        ORDER BY c.created_at DESC
        LIMIT ?, ?
    ";

    $params[] = $offset;
    $params[] = $limit;

    $types .= "ii";

    /* =========================
       PREPARE
    ========================== */

    $stmt = $conn->prepare($sql);

    if (!$stmt) {

        http_response_code(500);

        return [
            'success' => false,
            'message' => 'Database prepare failed'
        ];
    }

    /* =========================
       BIND PARAMS
    ========================== */

    $stmt->bind_param($types, ...$params);

    /* =========================
       EXECUTE
    ========================== */

    if (!$stmt->execute()) {

        http_response_code(500);

        return [
            'success' => false,
            'message' => 'Database execute failed'
        ];
    }

    $result = $stmt->get_result();

    $offers = [];

    /* =========================
       FORMAT RESPONSE
    ========================== */

    while ($row = $result->fetch_assoc()) {

        $offers[] = [

            'id' => $row['id'],

            'code' => $row['code'],

            'title' => $row['title'],

            'description' => $row['description'],

            'offer_type' => $row['offer_type'],

            'discount' => [
                'type' => $row['discount_type'],
                'value' => (float)$row['discount_value'],
                'max_discount_amount' =>
                    $row['max_discount_amount'] !== null
                        ? (float)$row['max_discount_amount']
                        : null,
            ],

            'min_order_amount' =>
                (float)$row['min_order_amount'],

            'badge_text' => $row['badge_text'],

            'coupon_image' => $row['coupon_image'],

            'background_color' => $row['background_color'],

            'button_text' => $row['button_text'],

            'category_name' => $row['category_name'],

            'product_name' => $row['product_name'],

            'start_date' => $row['start_date'],

            'end_date' => $row['end_date'],

            'created_at' => $row['created_at'],
        ];
    }

    $stmt->close();

    /* =========================
       RESPONSE
    ========================== */

    return [

        'success' => true,

        'message' => 'Offers fetched successfully',

        'data' => $offers,

        'pagination' => [
            'page' => $page,
            'limit' => $limit
        ]
    ];
}