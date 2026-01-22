<?php
require_once __DIR__ . '/../../../../config/database.php';

if (isset($_POST['DeleteUserbtn'])) {

    // INPUT VALIDATION
  if (
    $_SESSION['auth_role'] !== 'super_admin' &&
    $_SESSION['auth_role'] !== 'admin'
) {
    $_SESSION['status'] = "You are not authorized to perform this action!";
    header("Location: ./register.php");
    exit;
}



    $user_id = trim($_POST['delete_id']);

    if ($user_id === '') {
        $_SESSION['status'] = "Invalid admin ID!";
        header("Location: ./register.php");
        exit;
    }

    // DELETE ADMIN

    $delete = $conn->prepare("DELETE FROM admins WHERE id = ?");
    $delete->bind_param("s", $user_id);

    if ($delete->execute()) {
        $_SESSION['status'] = "Admin deleted successfully!";
    } else {
        $_SESSION['status'] = "Failed to delete admin!";
    }

    $delete->close();

    header("Location: ./register.php");
    exit;
}
