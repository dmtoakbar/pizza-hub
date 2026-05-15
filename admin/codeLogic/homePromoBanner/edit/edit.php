<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../../../config/database.php';

if (isset($_POST['updatePromoSlider'])) {

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
       INPUT
    ========================== */
    $id           = $_POST['slider_id'];

    $title        = trim($_POST['title']);
    $subtitle     = trim($_POST['subtitle'] ?? '');
    $buttonText   = trim($_POST['button_text'] ?? '');

    $sortOrder    = isset($_POST['sort_order'])
        ? (int)$_POST['sort_order']
        : 0;

    $startDate    = !empty($_POST['start_date'])
        ? $_POST['start_date']
        : null;

    $endDate      = !empty($_POST['end_date'])
        ? $_POST['end_date']
        : null;

    $status = isset($_POST['status']) ? (int)$_POST['status'] : 1;

    $oldImage     = $_POST['old_image'];

    /* =========================
       VALIDATION
    ========================== */
    if ($title === '') {
        $_SESSION['status'] = "Promo slider title is required";
        header("Location: ./promo-slider-banners.php");
        exit;
    }

    /* =========================
       IMAGE HANDLING
    ========================== */
    $imagePath = $oldImage;

    if (!empty($_FILES['image']['name'])) {

        $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];

        $ext = strtolower(
            pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION)
        );

        if (!in_array($ext, $allowedExt)) {

            $_SESSION['status'] = "Invalid image type";
            header("Location: ./promo-slider-banners.php");
            exit;
        }

        $newImage = uniqid('promo_slider_', true) . '.' . $ext;

        $uploadDir = __DIR__ . '/../../../../storage/promo-slider-banners/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        if (
            move_uploaded_file(
                $_FILES['image']['tmp_name'],
                $uploadDir . $newImage
            )
        ) {

            /* DELETE OLD IMAGE */
            if (!empty($oldImage)) {

                $oldFile = __DIR__ .
                    '/../../../../storage/' .
                    ltrim($oldImage, '/');

                if (file_exists($oldFile)) {
                    unlink($oldFile);
                }
            }

            $imagePath = 'promo-slider-banners/' . $newImage;
        }
    }

    /* =========================
       UPDATE DATABASE
    ========================== */
    $query = "
        UPDATE promo_slider_banners SET
            title       = ?,
            subtitle    = ?,
            image       = ?,
            button_text = ?,
            status      = ?,
            sort_order  = ?,
            start_date  = ?,
            end_date    = ?
        WHERE id = ?
    ";

    $stmt = $conn->prepare($query);

    $stmt->bind_param(
        "ssssiisss",
        $title,
        $subtitle,
        $imagePath,
        $buttonText,
        $status,
        $sortOrder,
        $startDate,
        $endDate,
        $id
    );

    if ($stmt->execute()) {
        $_SESSION['status'] = "Promo slider updated successfully";
    } else {
        $_SESSION['status'] = "Failed to update promo slider";
    }

    $stmt->close();

    header("Location: ./promo-slider-banners.php");
    exit;
}
