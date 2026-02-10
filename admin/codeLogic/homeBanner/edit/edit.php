<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../../../config/database.php';

if (isset($_POST['updateHomeBanner'])) {



    /* =========================
   INPUT
========================== */
    $id            = $_POST['banner_id'];
    $title         = trim($_POST['title']);
    $subtitle      = trim($_POST['subtitle'] ?? '');
    $discountText  = trim($_POST['discount_text'] ?? '');
    $validTill     = !empty($_POST['valid_till']) ? $_POST['valid_till'] : null;
    $sortOrder     = isset($_POST['sort_order']) ? (int)$_POST['sort_order'] : 0;
    $status        = isset($_POST['status']) ? 1 : 0;
    $oldImage      = $_POST['old_image'];

    /* =========================
   VALIDATION
========================== */
    if ($title === '') {
        $_SESSION['status'] = "Banner title is required";
        header("Location: ./home-banners.php");
        exit;
    }

    /* =========================
   IMAGE HANDLING
========================== */
    $imagePath = $oldImage; // keep old image by default

    if (!empty($_FILES['image']['name'])) {

        $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowedExt)) {
            $_SESSION['status'] = "Invalid image type";
            header("Location: ./home-banners.php");
            exit;
        }

        $newImage  = uniqid('banner_', true) . '.' . $ext;
        $uploadDir = __DIR__ . '/../../../../storage/home-banners/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $newImage)) {

            // 🗑 Delete old image
            if (!empty($oldImage)) {
                $oldFile = __DIR__ . '/../../../../storage/' . ltrim($oldImage, '/');
                if (file_exists($oldFile)) {
                    unlink($oldFile);
                }
            }

            $imagePath = 'home-banners/' . $newImage;
        }
    }

    /* =========================
   UPDATE DATABASE
========================== */
    $query = "
    UPDATE home_banners SET
        title         = ?,
        subtitle      = ?,
        image          = ?,
        discount_text = ?,
        valid_till    = ?,
        status        = ?,
        sort_order    = ?
    WHERE id = ?
";

    $stmt = $conn->prepare($query);
    $stmt->bind_param(
        "sssssiss",
        $title,
        $subtitle,
        $imagePath,
        $discountText,
        $validTill,
        $status,
        $sortOrder,
        $id
    );

    if ($stmt->execute()) {
        $_SESSION['status'] = "Home banner updated successfully";
    } else {
        $_SESSION['status'] = "Failed to update home banner";
    }

    $stmt->close();

    header("Location: ./home-banners.php");
    exit;
}
