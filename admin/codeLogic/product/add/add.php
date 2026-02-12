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
    $category_id = trim($_POST['category_id'] ?? '');
    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    $sizes       = $_POST['sizes'] ?? [];
    $discount    = $_POST['discount_percentage'] ?? 0;

    $is_popular  = isset($_POST['is_popular']) ? 1 : 0;
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $status      = isset($_POST['status']) ? 1 : 0;

    /* =========================
       VALIDATION
    ========================== */
    if ($category_id === '' || $name === '') {
        $_SESSION['status'] = "Category and product name are required!";
        header("Location: ../../../products.php");
        exit;
    }

    // Validate sizes (S, M, L required)
    $requiredSizes = ['S', 'M', 'L'];
    foreach ($requiredSizes as $size) {
        if (!isset($sizes[$size]) || !is_numeric($sizes[$size]) || $sizes[$size] <= 0) {
            $_SESSION['status'] = "Valid price required for size {$size}!";
            header("Location: ../../../products.php");
            exit;
        }
    }

    if (!is_numeric($discount) || $discount < 0 || $discount > 100) {
        $_SESSION['status'] = "Discount must be between 0 and 100!";
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
       PREPARE DATA
    ========================== */
    $productId   = Uuid::uuid4()->toString();
    $imageDbPath = 'products/' . $imageName;

    // Encode sizes to JSON
    $sizesJson = json_encode([
        'S' => (float)$sizes['S'],
        'M' => (float)$sizes['M'],
        'L' => (float)$sizes['L'],
    ], JSON_UNESCAPED_UNICODE);

    /* =========================
       INSERT PRODUCT
    ========================== */
    $stmt = $conn->prepare("
        INSERT INTO products (
            id,
            category_id,
            name,
            description,
            image,
            sizes,
            discount_percentage,
            is_popular,
            is_featured,
            status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "ssssssdiis",
        $productId,
        $category_id,
        $name,
        $description,
        $imageDbPath,
        $sizesJson,
        $discount,
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
