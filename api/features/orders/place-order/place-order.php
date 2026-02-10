<?php
require_once __DIR__ . '/../../../../config/verify-each-request.php';
require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once __DIR__ . '/../../../../config/database.php';

use Ramsey\Uuid\Uuid;

function placeOrder()
{
    global $conn;

    // Get incoming data
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) $data = $_POST;

    // 1️⃣ Validate user & cart
    $userId = isset($data['user_id']) ? trim($data['user_id']) : '';
    $cartJson = isset($data['cart']) ? $data['cart'] : '[]';
    $paymentMethod = isset($data['payment_method']) ? $data['payment_method'] : 'cod';

    if ($userId === '' || empty($cartJson)) {
        http_response_code(400);
        return ['success' => false, 'message' => 'User ID and cart are required'];
    }

    // Decode cart JSON string
    $cart = json_decode($cartJson, true);
    if (!is_array($cart) || empty($cart)) {
        http_response_code(400);
        return ['success' => false, 'message' => 'Cart is empty or invalid'];
    }

    // 2️⃣ Fetch user info
    $stmt = $conn->prepare("SELECT name, email, phone, address FROM users WHERE id = ? LIMIT 1");
    $stmt->bind_param('s', $userId);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$user) {
        http_response_code(404);
        return ['success' => false, 'message' => 'User not found'];
    }

    $name = $data['name'] ?? $user['name'];
    $phone = $data['phone'] ?? $user['phone'];
    $address = $data['address'] ?? $user['address'];

    // 3️⃣ Begin transaction
    mysqli_begin_transaction($conn);

    try {
        $orderId = Uuid::uuid4()->toString();
        $totalAmount = 0;

        // 4️⃣ Insert order
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

        // 5️⃣ Insert items & extras
        foreach ($cart as $item) {

            $quantity   = intval($item['quantity'] ?? 1);
            $price      = floatval($item['price'] ?? 0);
            $size       = $item['size'] ?? null;
            $sizePrice  = floatval($item['size_price'] ?? 0);

            // ✅ base + size price
            $perItemPrice = $price + $sizePrice;
            $itemTotal = $perItemPrice * $quantity;
            $totalAmount += $itemTotal;

            // Insert order item
            $stmt = $conn->prepare("
                INSERT INTO order_items 
                (order_id, product_id, product_name, product_price, size, size_price, product_image, quantity)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param(
                'sssssdsi',
                $orderId,
                $item['product_id'],
                $item['name'],
                $price,
                $size,
                $sizePrice,
                $item['image'],
                $quantity
            );
            $stmt->execute();
            $orderItemId = $stmt->insert_id;
            $stmt->close();

            // Insert extras
            if (!empty($item['extras'])) {
                foreach ($item['extras'] as $extra) {
                    $extraName  = $extra['name'];
                    $extraPrice = floatval($extra['price']);
                    $totalAmount += $extraPrice;

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

        // 6️⃣ Update order total
        $stmt = $conn->prepare("UPDATE orders SET total_amount = ? WHERE id = ?");
        $stmt->bind_param('ds', $totalAmount, $orderId);
        $stmt->execute();
        $stmt->close();

        mysqli_commit($conn);

        return [
            'success' => true,
            'message' => 'Order placed successfully',
            'order_id' => $orderId,
            'total_amount' => $totalAmount
        ];

    } catch (Exception $e) {
        mysqli_rollback($conn);
        http_response_code(500);
        return [
            'success' => false,
            'message' => 'Failed to place order',
            'error' => $e->getMessage()
        ];
    }
}
