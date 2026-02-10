<?php

require_once __DIR__ . '/../../../../config/database.php';

if (isset($_POST['deleteCategory'])) {

    // 🔐 AUTH
    if (
        $_SESSION['auth_role'] !== 'super_admin' &&
        $_SESSION['auth_role'] !== 'admin'
    ) {
        $_SESSION['status'] = "You are not authorized to perform this action!";
        header("Location: ./categories.php");
        exit;
    }

    // ✅ VALIDATION
    $id = $_POST['delete_id'] ?? '';
    if (empty($id)) {
        $_SESSION['status'] = "Invalid category ID";
        header("Location: ./categories.php");
        exit;
    }

    /* =========================
       BLOCK DELETE IF PRODUCTS EXIST
    ========================== */
    $check = $conn->prepare(
        "SELECT COUNT(*) AS total FROM products WHERE category_id = ?"
    );
    $check->bind_param("s", $id);
    $check->execute();
    $check->bind_result($totalProducts);
    $check->fetch();
    $check->close();

    if ($totalProducts > 0) {
        $_SESSION['status'] = "Cannot delete category: products are assigned to it.";
        header("Location: ./categories.php");
        exit;
    }

    /* =========================
       FETCH CATEGORY
    ========================== */
    $stmt = $conn->prepare(
        "SELECT image FROM categories WHERE id = ? LIMIT 1"
    );
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $_SESSION['status'] = "Category not found";
        header("Location: ./categories.php");
        exit;
    }

    $category = $result->fetch_assoc();
    $image = $category['image'];
    $stmt->close();

    // 🗑 DELETE IMAGE
    if (!empty($image)) {
        $imageFile = realpath(
            __DIR__ . '/../../../../storage/' . ltrim($image, '/')
        );
        if ($imageFile && file_exists($imageFile)) {
            unlink($imageFile);
        }
    }

    // ❌ DELETE DB RECORD
    $deleteStmt = $conn->prepare(
        "DELETE FROM categories WHERE id = ?"
    );
    $deleteStmt->bind_param("s", $id);

    if ($deleteStmt->execute()) {
        $_SESSION['status'] = "Category deleted successfully";
    } else {
        $_SESSION['status'] = "Failed to delete category";
    }

    $deleteStmt->close();

    header("Location: ./categories.php");
    exit;
}
