<?php
require_once __DIR__ . '/../../../../config/database.php';
require_once __DIR__ . '/../order-functions.php';

updateOrderStatus(
    $conn,
    $_POST['id'],
    $_POST['status']
);

echo json_encode(['success'=>true]);

