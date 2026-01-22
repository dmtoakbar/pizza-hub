<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../../../config/database.php';

if (isset($_POST['deleteReport'])) {


    $id    = $_POST['delete_id'];

    if (empty($id)) {
        $_SESSION['status'] = "Invalid Report ID";
        header("Location: ../../../report-issue.php");
        exit;
    }

    $deleteStmt = $conn->prepare("DELETE FROM report WHERE id = ?");
    $deleteStmt->bind_param("s", $id);

    if ($deleteStmt->execute()) {
        $_SESSION['status'] = "Report deleted successfully";
    } else {
        $_SESSION['status'] = "Failed to delete Report";
    }

    header("Location: ../../../report-issue.php");
    exit;
}
