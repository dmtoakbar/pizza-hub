<?php
require_once __DIR__ . '/../../../config/verify-each-request.php';
require_once __DIR__ . '/../../../config/database.php';

function validateAndApplyCoupon()
{
    global $conn;

    /* =========================
       GET REQUEST DATA
    ========================== */

    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data) {
        $data = $_POST;
    }

    $userId     = trim($data['user_id'] ?? '');
    $couponCode = strtoupper(trim($data['coupon_code'] ?? ''));
    $orderAmount = isset($data['order_amount'])
        ? (float)$data['order_amount']
        : 0;

    /* =========================
       VALIDATION
    ========================== */

    if (empty($userId) || empty($couponCode)) {

        http_response_code(400);

        return [
            'success' => false,
            'message' => 'User ID and coupon code are required'
        ];
    }

    /* =========================
       GET USER
    ========================== */

    $userStmt = $conn->prepare("
        SELECT id, created_at
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

        LIMIT 1
    ");

    $stmt->bind_param("s", $couponCode);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows === 0) {

        return [
            'success' => false,
            'message' => 'Invalid or expired coupon'
        ];
    }

    $coupon = $result->fetch_assoc();

    $stmt->close();

    /* =========================
       CHECK USAGE LIMIT
    ========================== */

    if (
        $coupon['usage_limit'] !== null
        && $coupon['used_count'] >= $coupon['usage_limit']
    ) {

        return [
            'success' => false,
            'message' => 'Coupon usage limit exceeded'
        ];
    }

    /* =========================
       MIN ORDER CHECK
    ========================== */

    if ($orderAmount < $coupon['min_order_amount']) {

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

    if ((int)$coupon['is_new_user_only'] === 1) {

        $orderCheck = $conn->prepare("
            SELECT id
            FROM orders
            WHERE email = (
                SELECT email FROM users WHERE id = ?
            )
            LIMIT 1
        ");

        $orderCheck->bind_param("s", $userId);
        $orderCheck->execute();

        $orderResult = $orderCheck->get_result();

        if ($orderResult->num_rows > 0) {

            return [
                'success' => false,
                'message' => 'Coupon valid for new users only'
            ];
        }

        $orderCheck->close();
    }

    /* =========================
       PER USER LIMIT CHECK
    ========================== */

    /*
        OPTIONAL:
        You should create a coupon_usages table
        for proper tracking
    */

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
        $usageData['total'] >= $coupon['usage_per_user']
    ) {

        return [
            'success' => false,
            'message' => 'Coupon usage limit reached for this user'
        ];
    }

    /* =========================
       CALCULATE DISCOUNT
    ========================== */

    $discountAmount = 0;

    switch ($coupon['discount_type']) {

        case 'percentage':

            $discountAmount =
                ($orderAmount * $coupon['discount_value']) / 100;

            if (
                $coupon['max_discount_amount'] !== null
                && $discountAmount > $coupon['max_discount_amount']
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

            $discountAmount = 0;

            break;
    }

    /* =========================
       FINAL PAYABLE
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

        'message' => 'Coupon applied successfully',

        'data' => [

            'coupon_id' => $coupon['id'],

            'coupon_code' => $coupon['code'],

            'title' => $coupon['title'],

            'discount_type' =>
                $coupon['discount_type'],

            'discount_value' =>
                (float)$coupon['discount_value'],

            'discount_amount' =>
                round($discountAmount, 2),

            'original_amount' =>
                round($orderAmount, 2),

            'final_amount' =>
                round($finalAmount, 2),

            'free_delivery' =>
                $coupon['discount_type'] === 'free_delivery',

            'badge_text' =>
                $coupon['badge_text']
        ]
    ];
}