<?php
require_once __DIR__ . '/../../../../config/database.php';
require_once __DIR__ . '/../order-functions.php';

if (!isset($_POST['id'], $_POST['payment_status'])) {
    echo json_encode(['success'=>false,'msg'=>'Invalid data']);
    exit;
}

$updated = updatePaymentStatus(
    $conn,
    $_POST['id'],
    $_POST['payment_status']
);

echo json_encode([
    'success' => $updated
]);
