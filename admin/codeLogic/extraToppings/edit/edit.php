<?php
require_once __DIR__ . '/../../../../config/database.php';

if (isset($_POST['updateTopping'])) {

    /* =========================
       INPUT
    ========================== */
    $id        = $_POST['topping_id'];
    $name      = trim($_POST['name']);
    $price     = trim($_POST['price']);
    $oldImage  = $_POST['old_image'];

    /* =========================
       VALIDATION
    ========================== */
    if ($name === '' || $price === '') {
        $_SESSION['status'] = "All fields are required";
        header("Location: ./toppings.php");
        exit;
    }

    if (!is_numeric($price)) {
        $_SESSION['status'] = "Invalid price value";
        header("Location: ./toppings.php");
        exit;
    }

    /* =========================
       IMAGE HANDLING
    ========================== */
    $imagePath = $oldImage; // default → keep old image

    // ✅ New image uploaded
    if (!empty($_FILES['image']['name'])) {

        $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowedExt)) {
            $_SESSION['status'] = "Invalid image type";
            header("Location: ./toppings.php");
            exit;
        }

        $newImage  = uniqid('topping_', true) . '.' . $ext;
        $uploadDir = __DIR__ . '/../../../../storage/toppings/';

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

            $imagePath = 'toppings/' . $newImage;
        }
    }

    /* =========================
       UPDATE DATABASE
    ========================== */
    $query = "
        UPDATE extra_toppings SET
            name  = ?,
            price = ?,
            image = ?
        WHERE id = ?
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param(
        "sdss",
        $name,
        $price,
        $imagePath,
        $id
    );

    if ($stmt->execute()) {
        $_SESSION['status'] = "Extra topping updated successfully";
    } else {
        $_SESSION['status'] = "Failed to update topping";
    }

    header("Location: ./toppings.php");
    exit;
}
