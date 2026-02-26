<?php

require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../../config/database.php';

use Ramsey\Uuid\Uuid;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\AndroidNotification;

if (isset($_POST['sendNotification'])) {

    $title = $_POST['title'];
    $message = $_POST['message'];
    $type = $_POST['type'] ?? '';
    $userId = !empty($_POST['user_id']) ? $_POST['user_id'] : null;
    $referenceId = !empty($_POST['reference_id']) ? $_POST['reference_id'] : null;

    $notificationId = Uuid::uuid4()->toString();

    /*
    1️⃣ Insert into notifications table
    */
    $stmt = $conn->prepare("
        INSERT INTO notifications (id, user_id, title, message, type, reference_id)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("ssssss", $notificationId, $userId, $title, $message, $type, $referenceId);
    $stmt->execute();
    $stmt->close();

    /*
    2️⃣ Load Firebase Messaging
    */
    $messaging = require __DIR__ . '/../../../config/firebase.php';

    /*
    3️⃣ Get Tokens
    */
    if ($userId) {
        $stmt = $conn->prepare("
            SELECT user_id, fcm_token 
            FROM user_fcm_tokens 
            WHERE user_id = ?
        ");
        $stmt->bind_param("s", $userId);
    } else {
        $stmt = $conn->prepare("
            SELECT user_id, fcm_token 
            FROM user_fcm_tokens
        ");
    }

    $stmt->execute();
    $result = $stmt->get_result();

    /*
    4️⃣ Send Notification to each token
    */
    while ($row = $result->fetch_assoc()) {
        try {
            // Notification object
            $notification = Notification::create($title, $message);

            // Android-specific config
            $androidConfig = AndroidConfig::fromArray([
                'priority' => 'high', // ensures delivery in background/terminated
                'notification' => [
                    'channel_id' => 'high_importance_channel', // must match manifest
                    'sound' => 'default',
                ],
            ]);

            // Build message
            $cloudMessage = CloudMessage::withTarget('token', $row['fcm_token'])
                ->withNotification($notification)
                ->withData([
                    'type' => $type,
                    'reference_id' => $referenceId,
                    'notification_id' => $notificationId
                ])
                ->withAndroidConfig($androidConfig);

            // Send message
            $messaging->send($cloudMessage);

        } catch (Exception $e) {
            // Log error if needed
            error_log("FCM Error for token {$row['fcm_token']}: ".$e->getMessage());
        }
    }

    $stmt->close();

    $_SESSION['status'] = "Notification Sent Successfully!";
    header("Location: notifications.php");
    exit;
}