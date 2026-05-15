<?php
require_once __DIR__ . '/../../../config/verify-each-request.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../../config/database.php';

header('Content-Type: application/json');

function getPromoSliders()
{
    global $conn;

    /* =========================
       QUERY PARAMS
    ========================== */
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;

    $limit  = ($limit > 0 && $limit <= 50) ? $limit : 10;
    $page   = ($page > 0) ? $page : 1;
    $offset = ($page - 1) * $limit;

    $currentDate = date('Y-m-d H:i:s');

    /* =========================
       WHERE CONDITIONS
    ========================== */
    $whereSQL = "
    WHERE status = 1
    AND (
        start_date IS NULL
        OR start_date <= ?
    )
    AND (
        end_date IS NULL
        OR end_date >= ?
    )
";

    /* =========================
       COUNT TOTAL
    ========================== */
    $countSql = "
        SELECT COUNT(*) AS total
        FROM promo_slider_banners
        $whereSQL
    ";

    $countStmt = $conn->prepare($countSql);

    $countStmt->bind_param(
        "ss",
        $currentDate,
        $currentDate
    );

    $countStmt->execute();

    $total = (int)$countStmt
        ->get_result()
        ->fetch_assoc()['total'];

    $countStmt->close();

    /* =========================
       FETCH PROMO SLIDERS
    ========================== */
    $sql = "
        SELECT
            id,
            title,
            subtitle,
            image,
            button_text,
            sort_order,
            start_date,
            end_date,
            created_at
        FROM promo_slider_banners
    ";

    $stmt = $conn->prepare($sql);

    $stmt->execute();

    $result = $stmt->get_result();

    $sliders = [];

    while ($row = $result->fetch_assoc()) {

        $sliders[] = [
            'id'           => $row['id'],
            'title'        => $row['title'],
            'subtitle'     => $row['subtitle'],
            'image'        => $row['image'],
            'button_text'  => $row['button_text'],
            'sort_order'   => (int)$row['sort_order'],
            'start_date'   => $row['start_date'],
            'end_date'     => $row['end_date'],
            'created_at'   => $row['created_at'],
        ];
    }

    $stmt->close();

    /* =========================
       RESPONSE
    ========================== */
    return [
        'success' => true,

        'data' => $sliders,

        'pagination' => [
            'total'       => $total,
            'page'        => $page,
            'limit'       => $limit,
            'total_pages' => ceil($total / $limit),
        ]
    ];
}
