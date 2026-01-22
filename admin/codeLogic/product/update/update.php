<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../../../../config/database.php';
require_once __DIR__ . '/../../../../vendor/autoload.php';

if (isset($_POST['updateProduct'])) {



    $id              = $_POST['id'];
    $name            = trim($_POST['name']);
    $price           = trim($_POST['price']);
    $tag             = trim($_POST['tag']);
    $tag_description = trim($_POST['tag_description']);
    $oldImage        = $_POST['old_image'];

    if ($name === '' || $price === '' || $tag === '' || $tag_description === '') {
        $_SESSION['status'] = "All fields are required";
        header("Location: ../../../product-edit.php?id=$id");
        exit;
    }

    if (!is_numeric($price)) {
        $_SESSION['status'] = "Invalid price value";
        header("Location: ../../../product-edit.php?id=$id");
        exit;
    }

    $imagePath = $oldImage; // default → keep old image

    // ✅ If new image uploaded
    if (!empty($_FILES['image']['name'])) {

        $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowedExt)) {
            $_SESSION['status'] = "Invalid image type";
            header("Location: ../../../product-edit.php?id=$id");
            exit;
        }

        $newImage = uniqid('product_', true) . '.' . $ext;
        $uploadDir = __DIR__ . '/../../../../storage/products/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $newImage)) {

            // delete old image (filesystem)
            $oldFile = __DIR__ . '/../../../../storage/' . ltrim($oldImage, '/');
            if (file_exists($oldFile)) {
                unlink($oldFile);
            }

            $imagePath = 'products/' . $newImage;
        }
    }

    // ✅ Update DB
    $query = "
    UPDATE products SET
        name = ?,
        price = ?,
        tag = ?,
        tag_description = ?,
        image = ?
    WHERE id = ?
";

    $stmt = $conn->prepare($query);
    $stmt->bind_param(
        "sdssss",
        $name,
        $price,
        $tag,
        $tag_description,
        $imagePath,
        $id
    );

    if ($stmt->execute()) {
        $_SESSION['status'] = "Product updated successfully";
    } else {
        $_SESSION['status'] = "Update failed";
    }

    header("Location: ../../../products.php");
    exit;
}
