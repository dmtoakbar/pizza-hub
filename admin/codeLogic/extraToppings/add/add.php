<?php
require_once __DIR__ . '/../../../../config/database.php';
require_once __DIR__ . '/../../../../vendor/autoload.php';

use Ramsey\Uuid\Uuid;

if (isset($_POST['addTopping'])) {

    /* =========================
       INPUT SANITIZATION
    ========================== */

    $name  = trim($_POST['name']);
    $price = trim($_POST['price']);

    /* =========================
       VALIDATION
    ========================== */

    if ($name === '' || $price === '') {
        $_SESSION['status'] = "All fields are required!";
        header("Location: ./toppings.php");
        exit;
    }

    if (!is_numeric($price)) {
        $_SESSION['status'] = "Invalid price value!";
        header("Location: ./toppings.php");
        exit;
    }

    /* =========================
       IMAGE UPLOAD
    ========================== */

    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== 0) {
        $_SESSION['status'] = "Topping image is required!";
        header("Location: ./toppings.php");
        exit;
    }

    $allowedTypes = ['jpg', 'jpeg', 'png', 'webp'];
    $imageExt = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

    if (!in_array($imageExt, $allowedTypes)) {
        $_SESSION['status'] = "Invalid image type!";
        header("Location: ./toppings.php");
        exit;
    }

    $imageName = uniqid('topping_', true) . '.' . $imageExt;
    $uploadDir = __DIR__ . '/../../../../storage/toppings/';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $imagePath = $uploadDir . $imageName;

    if (!move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
        $_SESSION['status'] = "Image upload failed!";
        header("Location: ./toppings.php");
        exit;
    }

    /* =========================
       INSERT TOPPING
    ========================== */

    $toppingId  = Uuid::uuid4()->toString();
    $imageDbPath = 'toppings/' . $imageName;

    $stmt = $conn->prepare("
        INSERT INTO extra_toppings
        (id, name, image, price)
        VALUES (?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "sssd",
        $toppingId,
        $name,
        $imageDbPath,
        $price
    );

    if ($stmt->execute()) {
        $_SESSION['status'] = "Extra topping added successfully!";
    } else {
        $_SESSION['status'] = "Failed to add topping!";
    }

    $stmt->close();

    header("Location: ./toppings.php");
    exit;
}
