<?php

require_once __DIR__ . '/../../../../config/database.php';

if (isset($_POST['deleteTopping'])) {

    // 🔐 AUTH
    if (
        $_SESSION['auth_role'] !== 'super_admin' &&
        $_SESSION['auth_role'] !== 'admin'
    ) {
        $_SESSION['status'] = "You are not authorized to perform this action!";
        header("Location: ./toppings.php");
        exit;
    }

    // ✅ VALIDATION
    $id = $_POST['delete_id'] ?? '';
    if (empty($id)) {
        $_SESSION['status'] = "Invalid topping ID";
        header("Location: ./toppings.php");
        exit;
    }

    // 🔎 FETCH TOPPING
    $stmt = $conn->prepare("SELECT image FROM extra_toppings WHERE id = ? LIMIT 1");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $_SESSION['status'] = "Topping not found";
        header("Location: ./toppings.php");
        exit;
    }

    $topping = $result->fetch_assoc();
    $image = $topping['image'];
    $stmt->close();

    // 🗑 DELETE IMAGE
    if (!empty($image)) {
        $imageFile = realpath(__DIR__ . '/../../../../storage/' . ltrim($image, '/'));
        if ($imageFile && file_exists($imageFile)) {
            unlink($imageFile);
        }
    }

    // ❌ DELETE DB RECORD
    $deleteStmt = $conn->prepare("DELETE FROM extra_toppings WHERE id = ?");
    $deleteStmt->bind_param("s", $id);

    if ($deleteStmt->execute()) {
        $_SESSION['status'] = "Extra topping deleted successfully";
    } else {
        $_SESSION['status'] = "Failed to delete topping";
    }

    $deleteStmt->close();

    header("Location: ./toppings.php");
    exit;
}
