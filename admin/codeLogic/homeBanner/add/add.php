<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../../../config/database.php';
require_once __DIR__ . '/../../../../vendor/autoload.php';

use Ramsey\Uuid\Uuid;

if (isset($_POST['addHomeBanner'])) {


    /* =========================
   INPUT SANITIZATION
========================== */
    $title         = trim($_POST['title']);
    $subtitle      = trim($_POST['subtitle'] ?? '');
    $discountText  = trim($_POST['discount_text'] ?? '');
    $validTill     = !empty($_POST['valid_till']) ? $_POST['valid_till'] : null;
    $sortOrder     = isset($_POST['sort_order']) ? (int)$_POST['sort_order'] : 0;

    /* =========================
   VALIDATION
========================== */
    if ($title === '') {
        $_SESSION['status'] = "Banner title is required!";
        header("Location: ./home-banners.php");
        exit;
    }

    /* =========================
   IMAGE UPLOAD
========================== */
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== 0) {
        $_SESSION['status'] = "Banner image is required!";
        header("Location: ./home-banners.php");
        exit;
    }

    $allowedTypes = ['jpg', 'jpeg', 'png', 'webp'];
    $imageExt = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

    if (!in_array($imageExt, $allowedTypes)) {
        $_SESSION['status'] = "Invalid image type!";
        header("Location: ./home-banners.php");
        exit;
    }

    $imageName = uniqid('banner_', true) . '.' . $imageExt;
    $uploadDir = __DIR__ . '/../../../../storage/home-banners/';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $imageName)) {
        $_SESSION['status'] = "Image upload failed!";
        header("Location: ./home-banners.php");
        exit;
    }

    $imageDbPath = 'home-banners/' . $imageName;

    /* =========================
   INSERT HOME BANNER
========================== */
    $bannerId = Uuid::uuid4()->toString();

    $stmt = $conn->prepare("
    INSERT INTO home_banners
    (id, title, subtitle, image, discount_text, valid_till, sort_order)
    VALUES (?, ?, ?, ?, ?, ?, ?)
");

    $stmt->bind_param(
        "ssssssi",
        $bannerId,
        $title,
        $subtitle,
        $imageDbPath,
        $discountText,
        $validTill,
        $sortOrder
    );

    if ($stmt->execute()) {
        $_SESSION['status'] = "Home banner added successfully!";
    } else {
        $_SESSION['status'] = "Failed to add home banner!";
    }

    $stmt->close();

    header("Location: ./home-banners.php");
    exit;
}
