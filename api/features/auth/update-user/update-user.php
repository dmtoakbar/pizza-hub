<?php
require_once __DIR__ . '/../../../../config/verify-each-request.php';
require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once __DIR__ . '/../../../../config/database.php';

function updateUser()
{
    global $conn;

    // =========================
    // READ INPUT
    // =========================
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data) {
        $data = $_POST;
    }

    $userId   = trim($data['user_id'] ?? '');
    $name     = trim($data['name'] ?? '');
    $phone    = trim($data['phone'] ?? '');
    $address  = trim($data['address'] ?? '');
    $password = $data['password'] ?? null;

    // Old profile image
    $oldProfile = trim($data['old_user_profile'] ?? '');

    // =========================
    // VALIDATION
    // =========================
    if ($userId === '' || $name === '' || $phone === '' || $address === '') {
        return [
            'success' => false,
            'message' => 'User ID, name, phone, and address are required'
        ];
    }

    // Phone validation
    if (!preg_match('/^[0-9]{10}$/', $phone)) {
        return [
            'success' => false,
            'message' => 'Invalid phone number'
        ];
    }

    // =========================
    // CHECK USER EXISTS
    // =========================
    $check = $conn->prepare("
        SELECT id, user_profile 
        FROM users 
        WHERE id = ? 
        LIMIT 1
    ");

    $check->bind_param("s", $userId);
    $check->execute();

    $result = $check->get_result();

    if ($result->num_rows === 0) {
        $check->close();

        return [
            'success' => false,
            'message' => 'User not found'
        ];
    }

    $user = $result->fetch_assoc();

    $check->close();

    // =========================
    // IMAGE UPLOAD
    // =========================
    $profileImage = $user['user_profile'];

    if (!empty($_FILES['user_profile']['name'])) {

        $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];

        $ext = strtolower(
            pathinfo(
                $_FILES['user_profile']['name'],
                PATHINFO_EXTENSION
            )
        );

        if (!in_array($ext, $allowedExt)) {
            return [
                'success' => false,
                'message' => 'Invalid profile image type'
            ];
        }

        $newImage = uniqid('profile_', true) . '.' . $ext;

        $uploadDir = __DIR__ . '/../../../../storage/user-profile/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        if (
            move_uploaded_file(
                $_FILES['user_profile']['tmp_name'],
                $uploadDir . $newImage
            )
        ) {

            // Delete old image
            if (!empty($profileImage)) {

                $oldFile = __DIR__ . '/../../../../storage/' .
                    ltrim($profileImage, '/');

                if (file_exists($oldFile)) {
                    unlink($oldFile);
                }
            }

            $profileImage = 'user-profile/' . $newImage;
        }
    }

    // =========================
    // UPDATE QUERY
    // =========================
    if ($password && strlen($password) >= 6) {

        $hashedPassword = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        $stmt = $conn->prepare("
            UPDATE users
            SET
                name = ?,
                phone = ?,
                address = ?,
                user_profile = ?,
                password = ?
            WHERE id = ?
            LIMIT 1
        ");

        if (!$stmt) {
            return [
                'success' => false,
                'message' => 'Database prepare failed',
                'error'   => $conn->error
            ];
        }

        $stmt->bind_param(
            "ssssss",
            $name,
            $phone,
            $address,
            $profileImage,
            $hashedPassword,
            $userId
        );
    } else {

        $stmt = $conn->prepare("
            UPDATE users
            SET
                name = ?,
                phone = ?,
                address = ?,
                user_profile = ?
            WHERE id = ?
            LIMIT 1
        ");

        if (!$stmt) {
            return [
                'success' => false,
                'message' => 'Database prepare failed',
                'error'   => $conn->error
            ];
        }

        $stmt->bind_param(
            "sssss",
            $name,
            $phone,
            $address,
            $profileImage,
            $userId
        );
    }

    // =========================
    // EXECUTE
    // =========================
    if ($stmt->execute()) {

        $stmt->close();

        return [
            'success' => true,
            'message' => 'User profile updated successfully',
            'data' => [
                'user_profile' => $profileImage
            ]
        ];
    }

    $error = $stmt->error;

    $stmt->close();

    return [
        'success' => false,
        'message' => 'Profile update failed',
        'error'   => $error
    ];
}
?>