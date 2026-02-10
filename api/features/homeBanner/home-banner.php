<?php
require_once __DIR__ . '/../../../config/verify-each-request.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../../config/database.php';

header('Content-Type: application/json');

function getHomeBanners()
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

    /* =========================
       WHERE CONDITIONS
    ========================== */
    $conditions = ['status = 1']; // only active banners
    $whereSQL = 'WHERE ' . implode(' AND ', $conditions);

    /* =========================
       COUNT TOTAL
    ========================== */
    $countSql = "SELECT COUNT(*) AS total FROM home_banners $whereSQL";
    $countStmt = $conn->prepare($countSql);
    $countStmt->execute();

    $total = (int)$countStmt
        ->get_result()
        ->fetch_assoc()['total'];

    $countStmt->close();

    /* =========================
       FETCH BANNERS
    ========================== */
    $sql = "
        SELECT
            id,
            title,
            subtitle,
            image,
            discount_text,
            valid_till,
            sort_order,
            created_at
        FROM home_banners
        $whereSQL
        ORDER BY sort_order ASC, created_at DESC
        LIMIT ? OFFSET ?
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $limit, $offset);
    $stmt->execute();

    $result = $stmt->get_result();

    $banners = [];
    while ($row = $result->fetch_assoc()) {
        $banners[] = [
            'id'            => $row['id'],
            'title'         => $row['title'],
            'subtitle'      => $row['subtitle'],
            'image'         => $row['image'],
            'discount_text' => $row['discount_text'],
            'valid_till'    => $row['valid_till'],
            'sort_order'    => (int)$row['sort_order'],
            'created_at'    => $row['created_at'],
        ];
    }

    $stmt->close();

    /* =========================
       RESPONSE
    ========================== */
    return [
        'success' => true,
        'data' => $banners,
        'pagination' => [
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($total / $limit),
        ]
    ];
}
