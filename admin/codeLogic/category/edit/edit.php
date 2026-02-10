<?php
require_once __DIR__ . '/../../../../config/database.php';

if (isset($_POST['updateCategory'])) {

    /* =========================
       INPUT
    ========================== */
    $id       = $_POST['category_id'];
    $name     = trim($_POST['name']);
    $oldImage = $_POST['old_image'];

    /* =========================
       VALIDATION
    ========================== */
    if ($name === '') {
        $_SESSION['status'] = "Category name is required";
        header("Location: ./categories.php");
        exit;
    }

    /* =========================
       IMAGE HANDLING
    ========================== */
    $imagePath = $oldImage; // default → keep old image

    // ✅ New image uploaded
    if (!empty($_FILES['image']['name'])) {

        $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowedExt)) {
            $_SESSION['status'] = "Invalid image type";
            header("Location: ./categories.php");
            exit;
        }

        $newImage  = uniqid('category_', true) . '.' . $ext;
        $uploadDir = __DIR__ . '/../../../../storage/categories/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $newImage)) {

            // 🗑 Delete old image
            if (!empty($oldImage)) {
                $oldFile = __DIR__ . '/../../../../storage/' . ltrim($oldImage, '/');
                if (file_exists($oldFile)) {
                    unlink($oldFile);
                }
            }

            $imagePath = 'categories/' . $newImage;
        }
    }

    /* =========================
       UPDATE DATABASE
    ========================== */
    $query = "
        UPDATE categories SET
            name  = ?,
            image = ?
        WHERE id = ?
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param(
        "sss",
        $name,
        $imagePath,
        $id
    );

    if ($stmt->execute()) {
        $_SESSION['status'] = "Category updated successfully";
    } else {
        $_SESSION['status'] = "Failed to update category";
    }

    header("Location: ./categories.php");
    exit;
}
