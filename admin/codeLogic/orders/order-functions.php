<?php

/* =========================
   GET SINGLE ORDER
========================= */
function getOrder(mysqli $conn, string $orderId)
{
    $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->bind_param("s", $orderId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

/* =========================
   GET ORDER ITEMS
========================= */
function getOrderItems(mysqli $conn, string $orderId)
{
    $stmt = $conn->prepare(
        "SELECT * FROM order_items WHERE order_id = ?"
    );
    $stmt->bind_param("s", $orderId);
    $stmt->execute();
    return $stmt->get_result();
}

/* =========================
   GET ITEM EXTRAS
========================= */
function getItemExtras(mysqli $conn, int $orderItemId)
{
    $stmt = $conn->prepare(
        "SELECT * FROM order_item_extras WHERE order_item_id = ?"
    );
    $stmt->bind_param("i", $orderItemId);
    $stmt->execute();
    return $stmt->get_result();
}

/* =========================
   UPDATE ORDER STATUS
========================= */
function updateOrderStatus(mysqli $conn, string $orderId, string $status)
{
    $stmt = $conn->prepare("UPDATE orders SET status=? WHERE id=?");
    $stmt->bind_param("ss", $status, $orderId);
    $stmt->execute();

    return [
        'affected' => $stmt->affected_rows,
        'error' => $stmt->error
    ];
}


/* =========================
   UPDATE PAYMENT STATUS
========================= */
function updatePaymentStatus(mysqli $conn, string $orderId, string $paymentStatus)
{
    $stmt = $conn->prepare(
        "UPDATE orders SET payment_status = ? WHERE id = ?"
    );
    $stmt->bind_param("ss", $paymentStatus, $orderId);
    return $stmt->execute();
}
