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
    $minPrice    = isset($_GET['min_price']) ? (float)$_GET['min_price'] : null;
    $maxPrice    = isset($_GET['max_price']) ? (float)$_GET['max_price'] : null;
    $isPopular   = isset($_GET['popular']) ? (int)$_GET['popular'] : null;
    $isFeatured  = isset($_GET['featured']) ? (int)$_GET['featured'] : null;
    $sort        = $_GET['sort'] ?? 'latest';
    $selectedSize = $_GET['size'] ?? 'M'; // default size

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

    if ($q !== '') {
        $conditions[] = '(name LIKE ? OR description LIKE ?)';
        $params[] = "%$q%";
        $params[] = "%$q%";
        $types .= 'ss';
    }

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

    // -------------------------
    // Count total products
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
    // Fetch products
    // -------------------------
    $sql = "
        SELECT 
            id,
            category_id,
            name,
            description,
            image,
            sizes,
            discount_percentage,
            is_popular,
            is_featured,
            created_at
        FROM products
        $whereSQL
        ORDER BY created_at DESC
        LIMIT ? OFFSET ?
    ";

    $stmt = $conn->prepare($sql);
    $paramsWithLimit = array_merge($params, [$limit, $offset]);
    $typesWithLimit = $types . 'ii';
    $stmt->bind_param($typesWithLimit, ...$paramsWithLimit);
    $stmt->execute();
    $result = $stmt->get_result();

    $products = [];
    while ($row = $result->fetch_assoc()) {
        $sizes = json_decode($row['sizes'], true);

        $basePrice = isset($sizes[$selectedSize]) ? (float)$sizes[$selectedSize] : 0.0;
        $discountPercentage = (float)$row['discount_percentage'];
        $finalPrice = $basePrice * (1 - ($discountPercentage / 100));

        // Price filter
        if (($minPrice !== null && $finalPrice < $minPrice) ||
            ($maxPrice !== null && $finalPrice > $maxPrice)
        ) {
            continue;
        }

        $products[] = [
            'id' => $row['id'],
            'category_id' => $row['category_id'],
            'name' => $row['name'],
            'description' => $row['description'],
            'image' => $row['image'],
            'sizes' => $sizes,
            'selected_size' => $selectedSize,
            'price' => $basePrice,
            'discount_percentage' => $discountPercentage,
            'final_price' => $finalPrice,
            'is_popular' => (bool)$row['is_popular'],
            'is_featured' => (bool)$row['is_featured'],
            'created_at' => $row['created_at'],
        ];
    }
    $stmt->close();

    // -------------------------
    // Sort products by price if requested
    // -------------------------
    if ($sort === 'price_asc') {
        usort($products, fn($a, $b) => $a['final_price'] <=> $b['final_price']);
    } elseif ($sort === 'price_desc') {
        usort($products, fn($a, $b) => $b['final_price'] <=> $a['final_price']);
    }

    // -------------------------
    // Return JSON
    // -------------------------
    return [
        'success' => true,
        'filters' => [
            'query' => $q,
            'category_id' => $categoryId,
            'size' => $selectedSize,
            'min_price' => $minPrice,
            'max_price' => $maxPrice,
            'popular' => $isPopular,
            'featured' => $isFeatured,
            'sort' => $sort,
        ],
        'data' => $products,
        'pagination' => [
            'total' => count($products),
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($total / $limit)
        ]
    ];
}
