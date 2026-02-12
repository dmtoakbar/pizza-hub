<?php
require_once __DIR__ . '/../../../config/verify-each-request.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../../config/database.php';

header('Content-Type: application/json');

function getProducts()
{
    global $conn;

    /* =========================
       QUERY PARAMS
    ========================== */
    $categoryId = $_GET['category_id'] ?? '';
    $isPopular  = isset($_GET['popular']) ? (int)$_GET['popular'] : null;
    $isFeatured = isset($_GET['featured']) ? (int)$_GET['featured'] : null;

    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
    $page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;

    $limit  = ($limit > 0 && $limit <= 100) ? $limit : 20;
    $page   = ($page > 0) ? $page : 1;
    $offset = ($page - 1) * $limit;

    /* =========================
       WHERE CONDITIONS
    ========================== */
    $conditions = ['status = 1'];
    $params = [];
    $types  = '';

    if (!empty($categoryId)) {
        $conditions[] = 'category_id = ?';
        $params[] = $categoryId;
        $types .= 's';
    }

    if ($isPopular !== null) {
        $conditions[] = 'is_popular = ?';
        $params[] = $isPopular;
        $types .= 'i';
    }

    if ($isFeatured !== null) {
        $conditions[] = 'is_featured = ?';
        $params[] = $isFeatured;
        $types .= 'i';
    }

    $whereSQL = 'WHERE ' . implode(' AND ', $conditions);

    /* =========================
       COUNT TOTAL
    ========================== */
    $countSql = "SELECT COUNT(*) AS total FROM products $whereSQL";
    $countStmt = $conn->prepare($countSql);

    if (!empty($types)) {
        $countStmt->bind_param($types, ...$params);
    }

    $countStmt->execute();
    $total = (int)$countStmt->get_result()->fetch_assoc()['total'];
    $countStmt->close();

    /* =========================
       FETCH PRODUCTS
    ========================== */
    $sql = "
        SELECT
            id,
            category_id,
            name,
            description,
            sizes,
            discount_percentage,
            image,
            is_popular,
            is_featured,
            created_at
        FROM products
        $whereSQL
        ORDER BY created_at DESC
        LIMIT ? OFFSET ?
    ";

    $stmt = $conn->prepare($sql);

    if (!empty($types)) {
        $types .= 'ii';
        $params[] = $limit;
        $params[] = $offset;
        $stmt->bind_param($types, ...$params);
    } else {
        $stmt->bind_param('ii', $limit, $offset);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $products = [];

    while ($row = $result->fetch_assoc()) {
        $products[] = [
            'id' => $row['id'],
            'category_id' => $row['category_id'],
            'name' => $row['name'],
            'description' => $row['description'],
            'sizes' => json_decode($row['sizes'], true), // S, M, L prices
            'discount_percentage' => (float)$row['discount_percentage'],
            'image' => $row['image'],
            'is_popular' => (bool)$row['is_popular'],
            'is_featured' => (bool)$row['is_featured'],
            'created_at' => $row['created_at'],
        ];
    }

    $stmt->close();

    /* =========================
       RESPONSE
    ========================== */
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

