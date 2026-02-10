<?php
require_once __DIR__ . '/../../../../config/database.php';
require_once __DIR__ . '/../../../../vendor/autoload.php';

use Ramsey\Uuid\Uuid;

if (isset($_POST['addCategory'])) {

    /* =========================
       INPUT SANITIZATION
    ========================== */

    $name = trim($_POST['name']);

    /* =========================
       VALIDATION
    ========================== */

    if ($name === '') {
        $_SESSION['status'] = "Category name is required!";
        header("Location: ./categories.php");
        exit;
    }

    /* =========================
       IMAGE UPLOAD
    ========================== */

    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== 0) {
        $_SESSION['status'] = "Category image is required!";
        header("Location: ./categories.php");
        exit;
    }

    $allowedTypes = ['jpg', 'jpeg', 'png', 'webp'];
    $imageExt = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

    if (!in_array($imageExt, $allowedTypes)) {
        $_SESSION['status'] = "Invalid image type!";
        header("Location: ./categories.php");
        exit;
    }

    $imageName = uniqid('category_', true) . '.' . $imageExt;
    $uploadDir = __DIR__ . '/../../../../storage/categories/';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $imagePath = $uploadDir . $imageName;

    if (!move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
        $_SESSION['status'] = "Image upload failed!";
        header("Location: ./categories.php");
        exit;
    }

    /* =========================
       INSERT CATEGORY
    ========================== */

    $categoryId  = Uuid::uuid4()->toString();
    $imageDbPath = 'categories/' . $imageName;

    $stmt = $conn->prepare("
        INSERT INTO categories
        (id, name, image)
        VALUES (?, ?, ?)
    ");

    $stmt->bind_param(
        "sss",
        $categoryId,
        $name,
        $imageDbPath
    );

    if ($stmt->execute()) {
        $_SESSION['status'] = "Category added successfully!";
    } else {
        $_SESSION['status'] = "Failed to add category!";
    }

    $stmt->close();

    header("Location: ./categories.php");
    exit;
}
