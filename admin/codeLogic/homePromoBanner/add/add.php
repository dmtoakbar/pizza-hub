<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../../../config/database.php';
require_once __DIR__ . '/../../../../vendor/autoload.php';

use Ramsey\Uuid\Uuid;

if (isset($_POST['addPromoSlider'])) {

    /* =========================
       INPUT SANITIZATION
    ========================== */
    $title       = trim($_POST['title']);
    $subtitle    = trim($_POST['subtitle'] ?? '');
    $buttonText  = trim($_POST['button_text'] ?? '');

    $sortOrder   = isset($_POST['sort_order'])
        ? (int)$_POST['sort_order']
        : 0;

    $startDate   = !empty($_POST['start_date'])
        ? $_POST['start_date']
        : null;

    $endDate     = !empty($_POST['end_date'])
        ? $_POST['end_date']
        : null;

    /* =========================
       VALIDATION
    ========================== */
    if ($title === '') {
        $_SESSION['status'] = "Promo slider title is required!";
        header("Location: ./promo-slider-banners.php");
        exit;
    }

    /* =========================
       IMAGE VALIDATION
    ========================== */
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== 0) {
        $_SESSION['status'] = "Promo slider image is required!";
        header("Location: ./promo-slider-banners.php");
        exit;
    }

    $allowedTypes = ['jpg', 'jpeg', 'png', 'webp'];

    $imageExt = strtolower(
        pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION)
    );

    if (!in_array($imageExt, $allowedTypes)) {
        $_SESSION['status'] = "Invalid image type!";
        header("Location: ./promo-slider-banners.php");
        exit;
    }

    /* =========================
       IMAGE UPLOAD
    ========================== */
    $imageName = uniqid('promo_slider_', true) . '.' . $imageExt;

    $uploadDir = __DIR__ . '/../../../../storage/promo-slider-banners/';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    if (
        !move_uploaded_file(
            $_FILES['image']['tmp_name'],
            $uploadDir . $imageName
        )
    ) {
        $_SESSION['status'] = "Image upload failed!";
        header("Location: ./promo-slider-banners.php");
        exit;
    }

    $imageDbPath = 'promo-slider-banners/' . $imageName;

    /* =========================
       INSERT PROMO SLIDER
    ========================== */
    $sliderId = Uuid::uuid4()->toString();

    $stmt = $conn->prepare("
        INSERT INTO promo_slider_banners
        (
            id,
            title,
            subtitle,
            image,
            button_text,
            sort_order,
            start_date,
            end_date
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "sssssiss",
        $sliderId,
        $title,
        $subtitle,
        $imageDbPath,
        $buttonText,
        $sortOrder,
        $startDate,
        $endDate
    );

    if ($stmt->execute()) {
        $_SESSION['status'] = "Promo slider added successfully!";
    } else {
        $_SESSION['status'] = "Failed to add promo slider!";
    }

    $stmt->close();

    header("Location: ./promo-slider-banners.php");
    exit;
}
?>