<?php
require_once __DIR__ . '/../../../config/verify-each-request.php';
require_once __DIR__ . '/../../../config/database.php';

header('Content-Type: application/json');

function getProductRatingSummary()
{
    global $conn;

    $productId = $_GET['product_id'] ?? '';

    $stmt = $conn->prepare("
    SELECT 
        COUNT(*) as total_reviews,
        AVG(rating) as avg_rating
    FROM product_reviews
    WHERE product_id = ?
    AND approval_status = 'approved'
");

    $stmt->bind_param("s", $productId);
    $stmt->execute();

    $data = $stmt->get_result()->fetch_assoc();

    return [
        'success' => true,
        'data' => [
            'total_reviews' => (int)$data['total_reviews'],
            'avg_rating' => round((float)$data['avg_rating'], 1)
        ]
    ];
}
