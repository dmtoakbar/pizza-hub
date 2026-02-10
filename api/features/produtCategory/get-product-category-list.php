<?php
require_once __DIR__ . '/../../../config/verify-each-request.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../../config/database.php';

header('Content-Type: application/json');

function getCategories()
{
    global $conn;

    /* =========================
       QUERY PARAMS
    ========================== */
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
    $page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;

    $limit  = ($limit > 0 && $limit <= 100) ? $limit : 20;
    $page   = ($page > 0) ? $page : 1;
    $offset = ($page - 1) * $limit;

    /* =========================
       WHERE CONDITIONS
    ========================== */
    $conditions = ['status = 1']; // only active categories
    $whereSQL = 'WHERE ' . implode(' AND ', $conditions);

    /* =========================
       COUNT TOTAL
    ========================== */
    $countSql = "SELECT COUNT(*) AS total FROM categories $whereSQL";
    $countStmt = $conn->prepare($countSql);
    $countStmt->execute();

    $total = (int)$countStmt
        ->get_result()
        ->fetch_assoc()['total'];

    $countStmt->close();

    /* =========================
       FETCH CATEGORIES
    ========================== */
    $sql = "
        SELECT
            id,
            name,
            image,
            sort_order,
            created_at
        FROM categories
        $whereSQL
        ORDER BY sort_order ASC, created_at DESC
        LIMIT ? OFFSET ?
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $limit, $offset);
    $stmt->execute();

    $result = $stmt->get_result();

    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[] = [
            'id'         => $row['id'],
            'name'       => $row['name'],
            'image'      => $row['image'],
            'sort_order' => (int)$row['sort_order'],
            'created_at' => $row['created_at'],
        ];
    }

    $stmt->close();

    /* =========================
       RESPONSE
    ========================== */
    return [
        'success' => true,
        'data' => $categories,
        'pagination' => [
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($total / $limit),
        ]
    ];
}
