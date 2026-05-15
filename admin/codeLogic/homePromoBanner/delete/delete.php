<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../../../config/database.php';

if (isset($_POST['deletePromoSlider'])) {

    /* =========================
       AUTHORIZATION
    ========================== */
    if (
        $_SESSION['auth_role'] !== 'super_admin' &&
        $_SESSION['auth_role'] !== 'admin'
    ) {
        $_SESSION['status'] = "You are not authorized to perform this action!";
        header("Location: ./promo-slider-banners.php");
        exit;
    }

    /* =========================
       VALIDATION
    ========================== */
    $id = $_POST['delete_id'] ?? '';

    if (empty($id)) {
        $_SESSION['status'] = "Invalid promo slider ID";
        header("Location: ./promo-slider-banners.php");
        exit;
    }

    /* =========================
       FETCH PROMO SLIDER
    ========================== */
    $stmt = $conn->prepare(
        "SELECT image FROM promo_slider_banners WHERE id = ? LIMIT 1"
    );

    $stmt->bind_param("s", $id);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $_SESSION['status'] = "Promo slider not found";
        header("Location: ./promo-slider-banners.php");
        exit;
    }

    $slider = $result->fetch_assoc();
    $image  = $slider['image'];

    $stmt->close();

    /* =========================
       DELETE IMAGE
    ========================== */
    if (!empty($image)) {

        $imageFile = realpath(
            __DIR__ . '/../../../../storage/' . ltrim($image, '/')
        );

        if ($imageFile && file_exists($imageFile)) {
            unlink($imageFile);
        }
    }

    /* =========================
       DELETE DATABASE RECORD
    ========================== */
    $deleteStmt = $conn->prepare(
        "DELETE FROM promo_slider_banners WHERE id = ?"
    );

    $deleteStmt->bind_param("s", $id);

    if ($deleteStmt->execute()) {
        $_SESSION['status'] = "Promo slider deleted successfully";
    } else {
        $_SESSION['status'] = "Failed to delete promo slider";
    }

    $deleteStmt->close();

    header("Location: ./promo-slider-banners.php");
    exit;
}
?>