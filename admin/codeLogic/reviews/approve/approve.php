<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

require_once __DIR__ . '/../../../../config/database.php';
require_once __DIR__ . '/../../../../vendor/autoload.php';

if (isset($_POST['review_id'])) {
    $review_id = trim($_POST['review_id']);


    if (!preg_match('/^[a-f0-9]{8}-([a-f0-9]{4}-){3}[a-f0-9]{12}$/i', $review_id)) {
        $_SESSION['status'] = "Invalid UUID format for review ID.";
        $_SESSION['status_code'] = "error";
        header("Location: ../../../customer_reviews.php");
        exit;
    }

    $stmt = mysqli_prepare($conn, "UPDATE product_reviews SET approval_status = 'approved' WHERE id = ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $review_id);
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['status'] = "Review approved successfully.";
        } else {
            $_SESSION['status'] = "Failed to approve review: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['status'] = "Database prepare error: " . mysqli_error($conn);
    }

    header("Location: ../../../customer_reviews.php");
    exit;
}
