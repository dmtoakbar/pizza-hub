<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../../../config/database.php';

if (isset($_POST['deleteProduct'])) {


    if (
        $_SESSION['auth_role'] !== 'super_admin' &&
        $_SESSION['auth_role'] !== 'admin'
    ) {
        $_SESSION['status'] = "You are not authorized to perform this action!";
         header("Location: ../../../products.php");
        exit;
    }


    $id    = $_POST['delete_id'];

    if (empty($id)) {
        $_SESSION['status'] = "Invalid product ID";
        header("Location: ../../../products.php");
        exit;
    }

    // 🔎 Get product again (security check)
    $stmt = $conn->prepare("SELECT image FROM products WHERE id = ? LIMIT 1");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $_SESSION['status'] = "Product not found";
        header("Location: ../../../products.php");
        exit;
    }

    $product = $result->fetch_assoc();
    $image = $product['image'];

    // 🗑 Delete image from storage
    if (!empty($image)) {
        $imageFile =  __DIR__ . '/../../../../storage/' . ltrim($image, '/');

        if (file_exists($imageFile)) {
            unlink($imageFile);
        }
    }

    // ❌ Delete product record
    $deleteStmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $deleteStmt->bind_param("s", $id);

    if ($deleteStmt->execute()) {
        $_SESSION['status'] = "Product deleted successfully";
    } else {
        $_SESSION['status'] = "Failed to delete product";
    }

    header("Location: ../../../products.php");
    exit;
}
