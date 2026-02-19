<?php
require_once __DIR__ . '/../../../../config/verify-each-request.php';
require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once __DIR__ . '/../../../../config/database.php';

use Ramsey\Uuid\Uuid;

header('Content-Type: application/json');

function placeOrder()
{
    global $conn;

    /* =========================
       INPUT
    ========================== */
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) $data = $_POST;

    $userId        = trim($data['user_id'] ?? '');
    $cart          = $data['cart'] ?? [];
    $paymentMethod = $data['payment_method'] ?? 'cod';

    if (is_string($cart)) {
        $cart = json_decode($cart, true);
    }

    if ($userId === '' || empty($cart)) {
        http_response_code(400);
        return ['success' => false, 'message' => 'User ID and cart are required'];
    }


    /* =========================
       USER
    ========================== */
    $stmt = $conn->prepare("
        SELECT name, email, phone, address
        FROM users WHERE id = ? LIMIT 1
    ");
    $stmt->bind_param('s', $userId);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$user) {
        http_response_code(404);
        return ['success' => false, 'message' => 'User not found'];
    }

    $name    = $data['name'] ?? $user['name'];
    $phone   = $data['phone'] ?? $user['phone'];
    $address = $data['address'] ?? $user['address'];

    /* =========================
       TRANSACTION
    ========================== */

    mysqli_begin_transaction($conn);

    try {
        $orderId     = Uuid::uuid4()->toString();
        $totalAmount = 0;

        /* =========================
           CREATE ORDER
        ========================== */
        $stmt = $conn->prepare("
            INSERT INTO orders
            (id, username, email, phone, address, total_amount, payment_method)
            VALUES (?, ?, ?, ?, ?, 0, ?)
        ");
        $stmt->bind_param(
            'ssssss',
            $orderId,
            $name,
            $user['email'],
            $phone,
            $address,
            $paymentMethod
        );
        $stmt->execute();
        $stmt->close();


        /* =========================
           ITEMS
        ========================== */
        foreach ($cart as $item) {

            $productId   = $item['product_id'];
            $productName = $item['name'];
            $productImg  = $item['image'];

            $size        = strtoupper($item['size']);
            $quantity    = (int)$item['quantity'];

            $basePrice   = (float)$item['base_price'];
            $discountPct = (float)($item['discount_percentage'] ?? 0);
            $finalPrice  = (float)$item['final_price'];

            // item subtotal (WITHOUT extras)
            $itemSubtotal = $finalPrice * $quantity;
            $totalAmount += $itemSubtotal;

            /* =========================
               INSERT ORDER ITEM (SNAPSHOT)
            ========================== */
            $stmt = $conn->prepare("
                INSERT INTO order_items (
                    order_id,
                    product_id,
                    product_image,
                    product_name,
                    size,
                    base_price,
                    discount_percentage,
                    final_price,
                    quantity
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param(
                'sssssdddi',
                $orderId,
                $productId,
                $productImg,
                $productName,
                $size,
                $basePrice,
                $discountPct,
                $finalPrice,
                $quantity
            );
            $stmt->execute();
            $orderItemId = $stmt->insert_id;
            $stmt->close();

            /* =========================
               EXTRAS
            ========================== */
            if (!empty($item['extras'])) {
                foreach ($item['extras'] as $extra) {

                    $extraName  = $extra['name'];
                    $extraPrice = (float)$extra['price'];

                    $totalAmount += $extraPrice * $quantity;

                    $stmt = $conn->prepare("
                        INSERT INTO order_item_extras
                        (order_item_id, extra_name, extra_price)
                        VALUES (?, ?, ?)
                    ");
                    $stmt->bind_param('isd', $orderItemId, $extraName, $extraPrice);
                    $stmt->execute();
                    $stmt->close();
                }
            }
        }

        /* =========================
           UPDATE TOTAL
        ========================== */
        $stmt = $conn->prepare("
            UPDATE orders SET total_amount = ?
            WHERE id = ?
        ");
        $stmt->bind_param('ds', $totalAmount, $orderId);
        $stmt->execute();
        $stmt->close();

        mysqli_commit($conn);

        return [
            'success' => true,
            'message' => 'Order placed successfully',
            'order_id' => $orderId,
            'total_amount' => round($totalAmount, 2)
        ];
    } catch (Throwable $e) {
        mysqli_rollback($conn);
        http_response_code(500);
        return [
            'success' => false,
            'message' => 'Order failed',
            'error' => $e->getMessage()
        ];
    }
}
