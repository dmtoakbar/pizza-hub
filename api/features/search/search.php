<?php
require_once __DIR__ . '/../../../config/verify-each-request.php';
require_once __DIR__ . '/../../../config/database.php';

header('Content-Type: application/json');

function advancedProductSearch()
{
    global $conn;

    // -------------------------
    // Read Params
    // -------------------------
    $q           = trim($_GET['q'] ?? '');
    $categoryId  = $_GET['category_id'] ?? '';
    $minPrice    = $_GET['min_price'] ?? null;
    $maxPrice    = $_GET['max_price'] ?? null;
    $isPopular   = isset($_GET['popular']) ? (int)$_GET['popular'] : null;
    $isFeatured  = isset($_GET['featured']) ? (int)$_GET['featured'] : null;
    $sort        = $_GET['sort'] ?? 'latest';

    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
    $page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;

    $limit  = ($limit > 0 && $limit <= 100) ? $limit : 20;
    $page   = ($page > 0) ? $page : 1;
    $offset = ($page - 1) * $limit;

    // -------------------------
    // Base Conditions
    // -------------------------
    $conditions = ['status = 1'];
    $params = [];
    $types  = '';

    // -------------------------
    // Search
    // -------------------------
    if ($q !== '') {
        $conditions[] = '(name LIKE ? OR description LIKE ?)';
        $params[] = "%$q%";
        $params[] = "%$q%";
        $types .= 'ss';
    }

    // -------------------------
    // Category
    // -------------------------
    if (!empty($categoryId)) {
        $conditions[] = 'category_id = ?';
        $params[] = $categoryId;
        $types .= 's';
    }

    // -------------------------
    // Price Filter (discount aware)
    // -------------------------
    if ($minPrice !== null) {
        $conditions[] = '(IFNULL(discount_price, price) >= ?)';
        $params[] = (float)$minPrice;
        $types .= 'd';
    }

    if ($maxPrice !== null) {
        $conditions[] = '(IFNULL(discount_price, price) <= ?)';
        $params[] = (float)$maxPrice;
        $types .= 'd';
    }

    // -------------------------
    // Flags
    // -------------------------
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

    // -------------------------
    // Sorting
    // -------------------------
    $orderBy = 'created_at DESC';
    switch ($sort) {
        case 'price_asc':
            $orderBy = 'IFNULL(discount_price, price) ASC';
            break;
        case 'price_desc':
            $orderBy = 'IFNULL(discount_price, price) DESC';
            break;
        case 'latest':
        default:
            $orderBy = 'created_at DESC';
    }

    // -------------------------
    // Count
    // -------------------------
    $countSql = "SELECT COUNT(*) AS total FROM products $whereSQL";
    $countStmt = $conn->prepare($countSql);

    if ($types !== '') {
        $countStmt->bind_param($types, ...$params);
    }

    $countStmt->execute();
    $total = (int)$countStmt->get_result()->fetch_assoc()['total'];
    $countStmt->close();

    // -------------------------
    // Fetch
    // -------------------------
    $sql = "
        SELECT 
            id,
            category_id,
            name,
            description,
            price,
            discount_price,
            image,
            is_popular,
            is_featured,
            created_at
        FROM products
        $whereSQL
        ORDER BY $orderBy
        LIMIT ? OFFSET ?
    ";

    $stmt = $conn->prepare($sql);

    $params[] = $limit;
    $params[] = $offset;
    $types .= 'ii';

    $stmt->bind_param($types, ...$params);
    $stmt->execute();

    $result = $stmt->get_result();
    $products = [];

    while ($row = $result->fetch_assoc()) {
        $products[] = [
            'id' => $row['id'],
            'category_id' => $row['category_id'],
            'name' => $row['name'],
            'description' => $row['description'],
            'price' => (float)$row['price'],
            'discount_price' => $row['discount_price'] !== null
                ? (float)$row['discount_price']
                : null,
            'final_price' => (float)($row['discount_price'] ?? $row['price']),
            'image' => $row['image'],
            'is_popular' => (bool)$row['is_popular'],
            'is_featured' => (bool)$row['is_featured'],
            'created_at' => $row['created_at'],
        ];
    }

    $stmt->close();

    return [
        'success' => true,
        'filters' => [
            'query' => $q,
            'category_id' => $categoryId,
            'min_price' => $minPrice,
            'max_price' => $maxPrice,
            'popular' => $isPopular,
            'featured' => $isFeatured,
            'sort' => $sort
        ],
        'data' => $products,
        'pagination' => [
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($total / $limit)
        ]
    ];
}
