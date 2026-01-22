<?php
require_once __DIR__ . '/../../../../config/database.php';

if (isset($_POST['DeleteUserbtn'])) {



    if (
        $_SESSION['auth_role'] !== 'super_admin' &&
        $_SESSION['auth_role'] !== 'admin'
    ) {
        $_SESSION['status'] = "You are not authorized to perform this action!";
        header("Location: ./all-customers.php");
        exit;
    }



    $user_id = trim($_POST['delete_id']);

    if ($user_id === '') {
        $_SESSION['status'] = "Invalid user ID!";
        header("Location: ./all-customers.php");
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("s", $user_id);

    if ($stmt->execute()) {
        $_SESSION['status'] = "User deleted successfully!";
    } else {
        $_SESSION['status'] = "Failed to delete user!";
    }

    $stmt->close();

    header("Location: ./all-customers.php");
    exit;
}
