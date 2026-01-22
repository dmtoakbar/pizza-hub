<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../../../../config/database.php';
require_once __DIR__ . '/../../../../vendor/autoload.php';

use Ramsey\Uuid\Uuid;

if (isset($_POST['addProduct'])) {

 
      // INPUT SANITIZATION
   
    $name            = trim($_POST['name']);
    $price           = trim($_POST['price']);
    $tag             = trim($_POST['tag']);
    $tag_description = trim($_POST['tag_description']);

  
      // VALIDATION
   
    if ($name === '' || $price === '' || $tag === '' || $tag_description === '') {
        $_SESSION['status'] = "All fields are required!";
        header("Location: ../../../products.php");
        exit;
    }

    if (!is_numeric($price)) {
        $_SESSION['status'] = "Invalid price value!";
        header("Location: ../../../products.php");
        exit;
    }

    
      // IMAGE UPLOAD
  
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
    $imagePath = $uploadDir . $imageName;

    if (!move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
        $_SESSION['status'] = "Image upload failed!";
        header("Location: ../../../products.php");
        exit;
    }

   
     //  INSERT PRODUCT

    $productId = Uuid::uuid4()->toString();

    $imageDbPath = 'products/' . $imageName;

    $stmt = $conn->prepare("
        INSERT INTO products 
        (id, name, price, tag, tag_description, image)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "ssdsss",
        $productId,
        $name,
        $price,
        $tag,
        $tag_description,
        $imageDbPath
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
