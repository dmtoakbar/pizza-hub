<?php

header('Content-Type: application/json');

require_once __DIR__ . '/../../../../config/verify-each-request.php';
require_once __DIR__ . '/../../../../config/database.php';

function validateAndApplyCoupon()
{
    global $conn;

    try {

        /* =========================
           GET REQUEST DATA
        ========================== */

        $data = json_decode(
            file_get_contents('php://input'),
            true
        );

        if (!$data) {
            $data = $_POST;
        }

        $userId = trim($data['user_id'] ?? '');

        $couponCode = strtoupper(
            trim($data['coupon_code'] ?? '')
        );

        $orderAmount = isset($data['order_amount'])
            ? (float)$data['order_amount']
            : 0;

        /*
            Optional:
            Send delivery fee from frontend
        */
        $deliveryFee = isset($data['delivery_fee'])
            ? (float)$data['delivery_fee']
            : 2;

        /* =========================
           VALIDATION
        ========================== */

        if (empty($userId) || empty($couponCode)) {

            http_response_code(400);

            return [
                'success' => false,
                'message' =>
                    'User ID and coupon code are required'
            ];
        }

        /* =========================
           GET USER
        ========================== */

        $userStmt = $conn->prepare("
            SELECT id, email, created_at
            FROM users
            WHERE id = ?
            LIMIT 1
        ");

        $userStmt->bind_param("s", $userId);

        $userStmt->execute();

        $userResult = $userStmt->get_result();

        if ($userResult->num_rows === 0) {

            return [
                'success' => false,
                'message' => 'User not found'
            ];
        }

        $user = $userResult->fetch_assoc();

        $userStmt->close();

        /* =========================
           GET COUPON
        ========================== */

        $stmt = $conn->prepare("
            SELECT *
            FROM coupons
            WHERE code = ?
            AND status = 1

            AND (
                start_date IS NULL
                OR start_date <= NOW()
            )

            AND (
                end_date IS NULL
                OR end_date >= NOW()
            )

            AND (
                usage_limit IS NULL
                OR used_count < usage_limit
            )

            LIMIT 1
        ");

        $stmt->bind_param("s", $couponCode);

        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 0) {

            return [
                'success' => false,
                'message' =>
                    'Invalid or expired coupon'
            ];
        }

        $coupon = $result->fetch_assoc();

        $stmt->close();

        /* =========================
           USAGE LIMIT CHECK
        ========================== */

        if (
            $coupon['usage_limit'] !== null
            &&
            $coupon['used_count']
            >= $coupon['usage_limit']
        ) {

            return [
                'success' => false,
                'message' =>
                    'Coupon usage limit exceeded'
            ];
        }

        /* =========================
           MIN ORDER CHECK
        ========================== */

        if (
            $orderAmount
            < $coupon['min_order_amount']
        ) {

            return [
                'success' => false,
                'message' =>
                    'Minimum order amount is ₹' .
                    $coupon['min_order_amount']
            ];
        }

        /* =========================
           NEW USER CHECK
        ========================== */

        if (
            (int)$coupon['is_new_user_only'] === 1
        ) {

            $orderCheck = $conn->prepare("
                SELECT id
                FROM orders
                WHERE email = ?
                LIMIT 1
            ");

            $orderCheck->bind_param(
                "s",
                $user['email']
            );

            $orderCheck->execute();

            $orderResult =
                $orderCheck->get_result();

            if ($orderResult->num_rows > 0) {

                return [
                    'success' => false,
                    'message' =>
                        'Coupon valid for new users only'
                ];
            }

            $orderCheck->close();
        }

        /* =========================
           PER USER LIMIT CHECK
        ========================== */

        $usageStmt = $conn->prepare("
            SELECT COUNT(*) as total
            FROM coupon_usages
            WHERE user_id = ?
            AND coupon_id = ?
        ");

        $usageStmt->bind_param(
            "ss",
            $userId,
            $coupon['id']
        );

        $usageStmt->execute();

        $usageResult = $usageStmt->get_result();

        $usageData = $usageResult->fetch_assoc();

        $usageStmt->close();

        if (
            $coupon['usage_per_user'] !== null
            &&
            $usageData['total']
            >= $coupon['usage_per_user']
        ) {

            return [
                'success' => false,
                'message' =>
                    'Coupon usage limit reached for this user'
            ];
        }

        /* =========================
           CATEGORY / PRODUCT CHECK
        ========================== */

        /*
            Optional:
            If you want category/product-specific
            coupon support in future
        */

        /* =========================
           CALCULATE DISCOUNT
        ========================== */

        $discountAmount = 0;

        switch ($coupon['discount_type']) {

            case 'percentage':

                $discountAmount =
                    (
                        $orderAmount
                        * $coupon['discount_value']
                    ) / 100;

                if (
                    $coupon['max_discount_amount']
                    !== null
                    &&
                    $discountAmount >
                    $coupon['max_discount_amount']
                ) {

                    $discountAmount =
                        $coupon['max_discount_amount'];
                }

                break;

            case 'flat':

                $discountAmount =
                    $coupon['discount_value'];

                break;

            case 'free_delivery':

                $discountAmount =
                    $deliveryFee;

                break;
        }

        /* =========================
           FINAL AMOUNT
        ========================== */

        $finalAmount = max(
            0,
            $orderAmount - $discountAmount
        );

        /* =========================
           SUCCESS RESPONSE
        ========================== */

        return [

            'success' => true,

            'message' =>
                'Coupon applied successfully',

            'data' => [

                'coupon_id' =>
                    $coupon['id'],

                'coupon_code' =>
                    $coupon['code'],

                'title' =>
                    $coupon['title'],

                'description' =>
                    $coupon['description'],

                'offer_type' =>
                    $coupon['offer_type'],

                'discount_type' =>
                    $coupon['discount_type'],

                'discount_value' =>
                    (float)$coupon['discount_value'],

                'discount_amount' =>
                    round($discountAmount, 2),

                'delivery_discount' =>
                    $coupon['discount_type']
                    === 'free_delivery'
                        ? round($discountAmount, 2)
                        : 0,

                'original_amount' =>
                    round($orderAmount, 2),

                'final_amount' =>
                    round($finalAmount, 2),

                'min_order_amount' =>
                    (float)$coupon['min_order_amount'],

                'max_discount_amount' =>
                    $coupon['max_discount_amount']
                        ? (float)$coupon['max_discount_amount']
                        : null,

                'free_delivery' =>
                    $coupon['discount_type']
                    === 'free_delivery',

                'badge_text' =>
                    $coupon['badge_text'],

                'button_text' =>
                    $coupon['button_text'],

                'background_color' =>
                    $coupon['background_color'],

                'coupon_image' =>
                    $coupon['coupon_image'],

                'start_date' =>
                    $coupon['start_date'],

                'end_date' =>
                    $coupon['end_date']
            ]
        ];

    } catch (Throwable $e) {

        http_response_code(500);

        return [

            'success' => false,

            'message' => $e->getMessage(),

            'file' => $e->getFile(),

            'line' => $e->getLine()
        ];
    }
}