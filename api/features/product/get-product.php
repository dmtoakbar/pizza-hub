<?php
require_once __DIR__ . '/../../../config/verify-each-request.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../../config/database.php';

header('Content-Type: application/json');

function getProducts()
{
    global $conn;

    // -------------------------
    // Read query parameters
    // -------------------------
    $tag     = isset($_GET['tag']) ? trim($_GET['tag']) : '';
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

    if ($tag !== '') {
        $conditions[] = "tag = ?";
        $params[] = $tag;
        $types .= 's';
    }

    if ($search !== '') {
        $conditions[] = "(name LIKE ? OR tag_description LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $types .= 'ss';
    }

    $whereSQL = '';
    if (!empty($conditions)) {
        $whereSQL = 'WHERE ' . implode(' AND ', $conditions);
    }

    // -------------------------
    // Count total products
    // -------------------------
    $countSql = "SELECT COUNT(*) as total FROM products $whereSQL";
    $countStmt = $conn->prepare($countSql);

    if ($types !== '') {
        $countStmt->bind_param($types, ...$params);
    }

    $countStmt->execute();
    $totalResult = $countStmt->get_result()->fetch_assoc();
    $total = (int) $totalResult['total'];
    $countStmt->close();

    // -------------------------
    // Fetch products
    // -------------------------
    $sql = "
        SELECT 
            id,
            name,
            price,
            tag,
            tag_description,
            image,
            created_at
        FROM products
        $whereSQL
        ORDER BY created_at DESC
        LIMIT ? OFFSET ?
    ";

    $stmt = $conn->prepare($sql);

    // Bind params dynamically
    if ($types !== '') {
        $typesWithLimit = $types . 'ii';
        $paramsWithLimit = array_merge($params, [$limit, $offset]);
        $stmt->bind_param($typesWithLimit, ...$paramsWithLimit);
    } else {
        $stmt->bind_param('ii', $limit, $offset);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'price' => (float) $row['price'],
            'tag' => $row['tag'],
            'tag_description' => $row['tag_description'],
            'image' => $row['image'],
            'created_at' => $row['created_at'],
        ];
    }

    $stmt->close();

    // -------------------------
    // Final response
    // -------------------------
    return [
        'success' => true,
        'data' => $products,
        'pagination' => [
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($total / $limit),
        ]
    ];
}


