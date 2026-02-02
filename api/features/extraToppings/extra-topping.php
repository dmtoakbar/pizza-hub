<?php
require_once __DIR__ . '/../../../config/verify-each-request.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../../config/database.php';

header('Content-Type: application/json');

function getExtraToppings()
{
    global $conn;

    // -------------------------
    // Read query parameters
    // -------------------------
    $search  = isset($_GET['search']) ? trim($_GET['search']) : '';
    $limit   = isset($_GET['limit']) ? (int) $_GET['limit'] : 20;
    $page    = isset($_GET['page']) ? (int) $_GET['page'] : 1;

    // Safety limits
    $limit = ($limit > 0 && $limit <= 100) ? $limit : 20;
    $page  = ($page > 0) ? $page : 1;
    $offset = ($page - 1) * $limit;

    // -------------------------
    // Build dynamic query
    // -------------------------
    $conditions = [];
    $params = [];
    $types = '';

    if ($search !== '') {
        $conditions[] = "name LIKE ?";
        $params[] = "%$search%";
        $types .= 's';
    }

    $whereSQL = '';
    if (!empty($conditions)) {
        $whereSQL = 'WHERE ' . implode(' AND ', $conditions);
    }

    // -------------------------
    // Count total toppings
    // -------------------------
    $countSql = "SELECT COUNT(*) as total FROM extra_toppings $whereSQL";
    $countStmt = $conn->prepare($countSql);

    if ($types !== '') {
        $countStmt->bind_param($types, ...$params);
    }

    $countStmt->execute();
    $totalResult = $countStmt->get_result()->fetch_assoc();
    $total = (int) $totalResult['total'];
    $countStmt->close();

    // -------------------------
    // Fetch toppings
    // -------------------------
    $sql = "
        SELECT 
            id,
            name,
            image,
            price,
            status,
            created_at,
            updated_at
        FROM extra_toppings
        $whereSQL
        ORDER BY created_at DESC
        LIMIT ? OFFSET ?
    ";

    $stmt = $conn->prepare($sql);

    if ($types !== '') {
        $typesWithLimit = $types . 'ii';
        $paramsWithLimit = array_merge($params, [$limit, $offset]);
        $stmt->bind_param($typesWithLimit, ...$paramsWithLimit);
    } else {
        $stmt->bind_param('ii', $limit, $offset);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $toppings = [];
    while ($row = $result->fetch_assoc()) {
        $toppings[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'image' => $row['image'],
            'price' => (float) $row['price'],
            'status' => (int) $row['status'],
            'created_at' => $row['created_at'],
            'updated_at' => $row['updated_at'],
        ];
    }

    $stmt->close();

    // -------------------------
    // Final response
    // -------------------------
    return [
        'success' => true,
        'data' => $toppings,
        'pagination' => [
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($total / $limit),
        ]
    ];
}
