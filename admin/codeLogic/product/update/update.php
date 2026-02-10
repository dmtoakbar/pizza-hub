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
    $id             = $_POST['id'];
    $category_id    = trim($_POST['category_id']);
    $name           = trim($_POST['name']);
    $description    = trim($_POST['description']);
    $price          = trim($_POST['price']);
    $discount_price = trim($_POST['discount_price']);
    $oldImage       = $_POST['old_image'];

    $is_popular  = isset($_POST['is_popular']) ? 1 : 0;
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $status      = isset($_POST['status']) ? 1 : 0;

    /* =========================
       VALIDATION
    ========================== */
    if ($id === '' || $category_id === '' || $name === '' || $price === '') {
        $_SESSION['status'] = "Required fields are missing";
        header("Location: ../../../product-edit.php?id=$id");
        exit;
    }

    if (!is_numeric($price)) {
        $_SESSION['status'] = "Invalid price value";
        header("Location: ../../../product-edit.php?id=$id");
        exit;
    }

    if ($discount_price !== '' && !is_numeric($discount_price)) {
        $_SESSION['status'] = "Invalid discount price";
        header("Location: ../../../product-edit.php?id=$id");
        exit;
    }

    /* =========================
       IMAGE HANDLING
    ========================== */
    $imagePath = $oldImage; // keep old image by default

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

            // 🗑 delete old image
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
            category_id    = ?,
            name           = ?,
            description    = ?,
            price          = ?,
            discount_price = ?,
            image          = ?,
            is_popular     = ?,
            is_featured    = ?,
            status         = ?
        WHERE id = ?
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param(
        "sssddsiiis",
        $category_id,
        $name,
        $description,
        $price,
        $discount_price,
        $imagePath,
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
