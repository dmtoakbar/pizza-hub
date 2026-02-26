<?php
require_once __DIR__ . '/../../../config/verify-each-request.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../../config/database.php';

use Ramsey\Uuid\Uuid;

function registerDevice()
{
    global $conn;

    // Read JSON or POST
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        $data = $_POST;
    }

    $userId = isset($data['user_id']) ? trim($data['user_id']) : null;
    $fcmToken = isset($data['fcm_token']) ? trim($data['fcm_token']) : '';
    $deviceType = isset($data['device_type']) ? trim($data['device_type']) : 'android';

    // ✅ Basic validation
    if ($fcmToken === '') {
        http_response_code(400);
        return [
            'success' => false,
            'message' => 'FCM token is required'
        ];
    }

    // Optional: validate device type
    $allowedDevices = ['android', 'ios', 'web'];
    if (!in_array($deviceType, $allowedDevices)) {
        http_response_code(400);
        return [
            'success' => false,
            'message' => 'Invalid device type'
        ];
    }

    /*
        1️⃣ Check if token already exists
    */
    $stmt = $conn->prepare("
        SELECT id FROM user_fcm_tokens
        WHERE fcm_token = ?
    ");

    if (!$stmt) {
        http_response_code(500);
        return [
            'success' => false,
            'message' => 'Database error (prepare)'
        ];
    }

    $stmt->bind_param("s", $fcmToken);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {

        // Token exists → update user_id + device_type
        $stmt2 = $conn->prepare("
            UPDATE user_fcm_tokens
            SET user_id = ?,
                device_type = ?,
                updated_at = CURRENT_TIMESTAMP
            WHERE fcm_token = ?
        ");

        $stmt2->bind_param("sss", $userId, $deviceType, $fcmToken);
        $stmt2->execute();
        $stmt2->close();

    } else {

        // Token does not exist → insert new
        $id = Uuid::uuid4()->toString();

        $stmt2 = $conn->prepare("
            INSERT INTO user_fcm_tokens
            (id, user_id, fcm_token, device_type)
            VALUES (?, ?, ?, ?)
        ");

        $stmt2->bind_param("ssss", $id, $userId, $fcmToken, $deviceType);
        $stmt2->execute();
        $stmt2->close();
    }

    $stmt->close();

    return [
        'success' => true,
        'message' => 'Device registered successfully'
    ];
}