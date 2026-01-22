<?php
require_once __DIR__ . '/../../../config/database.php';

header('Content-Type: application/json');

function getOverallStats($conn) {
    return $conn->query("
        SELECT
            COUNT(*) AS total_orders,
            IFNULL(SUM(total_amount),0) AS total_sales
        FROM orders
    ")->fetch_assoc();
}

function getWeeklySales($conn) {
    $data = [];
    $res = $conn->query("
        SELECT DATE(created_at) AS day, SUM(total_amount) AS sales
        FROM orders
        WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        GROUP BY day
        ORDER BY day
    ");
    while ($row = $res->fetch_assoc()) {
        $data[] = $row;
    }
    return $data;
}

function getMonthlySales($conn) {
    $data = [];
    $res = $conn->query("
        SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, SUM(total_amount) AS sales
        FROM orders
        GROUP BY month
        ORDER BY month
    ");
    while ($row = $res->fetch_assoc()) {
        $data[] = $row;
    }
    return $data;
}

function getOrderStatusStats($conn) {
    $data = [];
    $res = $conn->query("
        SELECT status, COUNT(*) AS total
        FROM orders
        GROUP BY status
    ");
    while ($row = $res->fetch_assoc()) {
        $data[] = $row;
    }
    return $data;
}

function getCounts($conn) {
    return [
        'customers' => $conn->query("SELECT COUNT(*) total FROM users")->fetch_assoc()['total'],
        'products'  => $conn->query("SELECT COUNT(*) total FROM products")->fetch_assoc()['total'],
        'staff'     => $conn->query("SELECT COUNT(*) total FROM admins")->fetch_assoc()['total'],
        'reports'   => $conn->query("SELECT COUNT(*) total FROM report")->fetch_assoc()['total'],
        'contacts'  => $conn->query("SELECT COUNT(*) total FROM contact_us")->fetch_assoc()['total'],
    ];
}

echo json_encode([
    'overall' => getOverallStats($conn),
    'weekly'  => getWeeklySales($conn),
    'monthly' => getMonthlySales($conn),
    'status'  => getOrderStatusStats($conn),
    'counts'  => getCounts($conn)
]);
