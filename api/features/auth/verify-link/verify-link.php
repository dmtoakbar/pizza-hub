<?php
require_once __DIR__ . '/../../../../config/database.php';

function verifyEmailLink($uid, $token) {
    
    global $conn;

    $stmt = $conn->prepare("
        SELECT * FROM email_verifications 
        WHERE uid = ? AND token = ? AND verified = 0 
        LIMIT 1
    ");
    $stmt->bind_param("ss", $uid, $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        return ['success' => false, 'message' => 'Invalid or already verified link.'];
    }

    $record = $result->fetch_assoc();

    if (strtotime($record['expires_at']) < time()) {
        return ['success' => false, 'message' => 'Verification link expired.'];
    }

    $update = $conn->prepare("UPDATE email_verifications SET verified = 1 WHERE id = ?");
    $update->bind_param("i", $record['id']);
    $update->execute();

    if ($update->affected_rows > 0) {
        return ['success' => true, 'message' => 'Email verified successfully.'];
    }

    return ['success' => false, 'message' => 'Verification failed.'];
}

// Handle request directly from link
if (isset($_GET['uid']) && isset($_GET['token'])) {
    $response = verifyEmailLink($conn, $_GET['uid'], $_GET['token']);
    echo json_encode($response);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
?>
