<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../../../config/database.php';

if (isset($_POST['updateProduct'])) {

    /* =========================
       INPUT
    ========================== */
    $id          = $_POST['id'];
    $category_id = trim($_POST['category_id']);
    $name        = trim($_POST['name']);
    $description = trim($_POST['description']);
    $oldImage    = $_POST['old_image'];

    $sizes              = $_POST['sizes'] ?? [];
    $discountPercentage = $_POST['discount_percentage'] ?? 0;

    $is_popular  = isset($_POST['is_popular']) ? 1 : 0;
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $status      = isset($_POST['status']) ? 1 : 0;

    /* =========================
       VALIDATION
    ========================== */
    if ($id === '' || $category_id === '' || $name === '') {
        $_SESSION['status'] = "Required fields are missing";
        header("Location: ../../../product-edit.php?id=$id");
        exit;
    }

    // Validate sizes
    foreach (['S', 'M', 'L'] as $size) {
        if (!isset($sizes[$size]) || $sizes[$size] === '' || !is_numeric($sizes[$size])) {
            $_SESSION['status'] = "Invalid price for size $size";
            header("Location: ../../../product-edit.php?id=$id");
            exit;
        }
    }

    if (!is_numeric($discountPercentage) || $discountPercentage < 0 || $discountPercentage > 100) {
        $_SESSION['status'] = "Invalid discount percentage";
        header("Location: ../../../product-edit.php?id=$id");
        exit;
    }

    $sizesJson = json_encode([
        'S' => (float)$sizes['S'],
        'M' => (float)$sizes['M'],
        'L' => (float)$sizes['L'],
    ]);

    /* =========================
       IMAGE HANDLING
    ========================== */
    $imagePath = $oldImage;

    if (!empty($_FILES['image']['name'])) {

        $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowedExt)) {
            $_SESSION['status'] = "Invalid image type";
            header("Location: ../../../product-edit.php?id=$id");
            exit;
        }

        $newImage  = uniqid('product_', true) . '.' . $ext;
        $uploadDir = __DIR__ . '/../../../../storage/products/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $newImage)) {

            // delete old image
            if (!empty($oldImage)) {
                $oldFile = __DIR__ . '/../../../../storage/' . ltrim($oldImage, '/');
                if (file_exists($oldFile)) {
                    unlink($oldFile);
                }
            }

            $imagePath = 'products/' . $newImage;
        }
    }

    /* =========================
       UPDATE DATABASE
    ========================== */
    $query = "
        UPDATE products SET
            category_id = ?,
            name = ?,
            description = ?,
            image = ?,
            sizes = ?,
            discount_percentage = ?,
            is_popular = ?,
            is_featured = ?,
            status = ?
        WHERE id = ?
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param(
        "sssssdiiis",
        $category_id,
        $name,
        $description,
        $imagePath,
        $sizesJson,
        $discountPercentage,
        $is_popular,
        $is_featured,
        $status,
        $id
    );

    if ($stmt->execute()) {
        $_SESSION['status'] = "Product updated successfully";
    } else {
        $_SESSION['status'] = "Failed to update product";
    }

    $stmt->close();

    header("Location: ../../../products.php");
    exit;
}
