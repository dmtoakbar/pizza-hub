<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../../../config/database.php';
require_once __DIR__ . '/../../../../vendor/autoload.php';

use Ramsey\Uuid\Uuid;

if (isset($_POST['addProduct'])) {

    /* =========================
       INPUT SANITIZATION
    ========================== */
    $category_id     = trim($_POST['category_id']);
    $name            = trim($_POST['name']);
    $description     = trim($_POST['description']);
    $price           = trim($_POST['price']);
    $discount_price  = trim($_POST['discount_price']);

    $is_popular   = isset($_POST['is_popular']) ? 1 : 0;
    $is_featured  = isset($_POST['is_featured']) ? 1 : 0;
    $status       = isset($_POST['status']) ? 1 : 0;

    /* =========================
       VALIDATION
    ========================== */
    if ($category_id === '' || $name === '' || $price === '') {
        $_SESSION['status'] = "Category, name and price are required!";
        header("Location: ../../../products.php");
        exit;
    }

    if (!is_numeric($price)) {
        $_SESSION['status'] = "Invalid price value!";
        header("Location: ../../../products.php");
        exit;
    }

    if ($discount_price !== '' && !is_numeric($discount_price)) {
        $_SESSION['status'] = "Invalid discount price!";
        header("Location: ../../../products.php");
        exit;
    }

    /* =========================
       IMAGE UPLOAD
    ========================== */
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== 0) {
        $_SESSION['status'] = "Product image is required!";
        header("Location: ../../../products.php");
        exit;
    }

    $allowedTypes = ['jpg', 'jpeg', 'png', 'webp'];
    $imageExt = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

    if (!in_array($imageExt, $allowedTypes)) {
        $_SESSION['status'] = "Invalid image type!";
        header("Location: ../../../products.php");
        exit;
    }

    $imageName = uniqid('product_', true) . '.' . $imageExt;
    $uploadDir = __DIR__ . '/../../../../storage/products/';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $imageName)) {
        $_SESSION['status'] = "Image upload failed!";
        header("Location: ../../../products.php");
        exit;
    }

    /* =========================
       INSERT PRODUCT
    ========================== */
    $productId   = Uuid::uuid4()->toString();
    $imageDbPath = 'products/' . $imageName;

    $stmt = $conn->prepare("
        INSERT INTO products (
            id,
            category_id,
            name,
            description,
            price,
            discount_price,
            image,
            is_popular,
            is_featured,
            status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "ssssddsiii",
        $productId,
        $category_id,
        $name,
        $description,
        $price,
        $discount_price,
        $imageDbPath,
        $is_popular,
        $is_featured,
        $status
    );

    if ($stmt->execute()) {
        $_SESSION['status'] = "Product added successfully!";
    } else {
        $_SESSION['status'] = "Failed to add product!";
    }

    $stmt->close();

    header("Location: ../../../products.php");
    exit;
}
