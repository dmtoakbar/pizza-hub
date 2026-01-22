<?php
require_once __DIR__ . '/../../../../config/database.php';

session_start();

if (isset($_POST['updateReport'])) {

  $id = $_POST['report_id'];
  $status = $_POST['status'];

  $query = "UPDATE report SET status = ? WHERE id = ?";
  $stmt = $conn->prepare($query);
  $stmt->bind_param("ss", $status, $id);

  if ($stmt->execute()) {
    $_SESSION['status'] = "Report status updated successfully";
  } else {
    $_SESSION['status'] = "Failed to update report";
  }

 header("Location: ../../../report-issue.php");
  exit();
}
