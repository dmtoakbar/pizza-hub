<?php
require_once __DIR__ . '/../../../config/verify-each-request.php';
require_once __DIR__ . '/../../../config/database.php';

header('Content-Type: application/json');

function getProductReviews()
{
    global $conn;

    $productId = $_GET['product_id'] ?? '';
    $page  = $_GET['page'] ?? 1;
    $limit = $_GET['limit'] ?? 10;

    $offset = ($page - 1) * $limit;

    $stmt = $conn->prepare("
    SELECT 
        pr.id,
        pr.rating,
        pr.review,
        pr.created_at,
        u.name as user_name
    FROM product_reviews pr
    JOIN users u ON pr.user_id = u.id
    WHERE pr.product_id = ?
    AND pr.approval_status = 'approved'
    ORDER BY pr.created_at DESC
    LIMIT ? OFFSET ?
");

    $stmt->bind_param("sii", $productId, $limit, $offset);
    $stmt->execute();

    $result = $stmt->get_result();

    $reviews = [];

    while ($row = $result->fetch_assoc()) {
        $reviews[] = $row;
    }

    return [
        'success' => true,
        'data' => $reviews
    ];
}
