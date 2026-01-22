<?php
require_once __DIR__ . '/../../../../config/database.php';
require_once __DIR__ . '/../../../../vendor/autoload.php';

use Ramsey\Uuid\Uuid;

if (isset($_POST['addAdmin'])) {

   if (
    $_SESSION['auth_role'] !== 'super_admin' &&
    $_SESSION['auth_role'] !== 'admin'
) {
    $_SESSION['status'] = "You are not authorized to perform this action!";
    header("Location: ./register.php");
    exit;
}

   
/* =========================
   INPUT VALIDATION
========================= */
$name  = trim($_POST['name']);
$phone = trim($_POST['phone']);
$email = trim($_POST['email']);
$password = $_POST['password'];
$confirm  = $_POST['confirm_password'];
$role = $_POST['role'];
$is_active = (int)$_POST['is_active'];

if ($password !== $confirm) {
    $_SESSION['status'] = "Passwords do not match!";
    header('Location: ./register.php');
    exit;
}

/* =========================
   EMAIL UNIQUE CHECK
========================= */
$stmt = $conn->prepare("SELECT id FROM admins WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $_SESSION['status'] = "Email already exists!";
    header('Location: ./register.php');
    exit;
}

/* =========================
   INSERT ADMIN
========================= */
$adminId = Uuid::uuid4()->toString();
$hash = password_hash($password, PASSWORD_DEFAULT);

$insert = $conn->prepare("
  INSERT INTO admins
  (id, name, phone, address, email, password, role, is_active)
  VALUES (?, ?, ?, 'N/A', ?, ?, ?, ?)
");

$insert->bind_param(
  "ssssssi",
  $adminId,
  $name,
  $phone,
  $email,
  $hash,
  $role,
  $is_active
);

if ($insert->execute()) {
    $_SESSION['status'] = "Admin added successfully!";
} else {
   $_SESSION['status'] = "Failed to add admin!";
}

header('Location: ./register.php');
exit;


}