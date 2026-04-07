<?php
require_once __DIR__ . '/../../../config/verify-each-request.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../vendor/autoload.php';

use Ramsey\Uuid\Uuid;

header('Content-Type: application/json');

function addProductReview()
{
    global $conn;

    $data = json_decode(file_get_contents("php://input"), true);

    $userId    = $data['user_id'] ?? '';
    $productId = $data['product_id'] ?? '';
    $rating    = $data['rating'] ?? 0;
    $review    = $data['review'] ?? '';

    if (!$userId || !$productId || $rating < 1 || $rating > 5) {
        echo json_encode(['success' => false, 'message' => 'Invalid data']);
        exit;
    }

    $id = Uuid::uuid4()->toString();

    $stmt = $conn->prepare("
    INSERT INTO product_reviews (
        id, product_id, user_id, rating, review, approval_status
    ) VALUES (?, ?, ?, ?, ?, 'pending')
");

    $stmt->bind_param("sssis", $id, $productId, $userId, $rating, $review);

    if ($stmt->execute()) {
        return [
            'success' => true,
            'message' => 'Review submitted. Awaiting approval.'
        ];
    } else {
        return ['success' => false, 'message' => $conn->error];
    }
}
