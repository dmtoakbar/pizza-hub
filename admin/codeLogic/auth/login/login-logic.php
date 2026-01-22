<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../../../config/database.php';

if (isset($_POST['login_btn'])) {


/* =========================
   CAPTCHA CHECK
========================= */
if (empty($_POST['captcha']) || $_POST['captcha'] !== $_POST['code']) {
    $_SESSION['status'] = "<p style='color:red;'>Invalid Captcha!</p>";
    header('Location: ./login.php');
    exit;
}

/* =========================
   INPUT SANITIZATION
========================= */
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    $_SESSION['status'] = "<p style='color:red;'>Email and password are required!</p>";
    header('Location: ./login.php');
    exit;
}

/* =========================
   FETCH ADMIN (SECURE)
========================= */
$stmt = $conn->prepare("
    SELECT id, name, email, phone, password, role, is_active
    FROM admins
    WHERE email = ?
    LIMIT 1
");

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

/* =========================
   VALIDATE LOGIN
========================= */
if ($result->num_rows !== 1) {
    $_SESSION['status'] = "<p style='color:red;'>Invalid email or password!</p>";
    header('Location: ./login.php');
    exit;
}

$admin = $result->fetch_assoc();

/* =========================
   ACTIVE CHECK
========================= */
if ((int)$admin['is_active'] !== 1) {
    $_SESSION['status'] = "<p style='color:red;'>Account is disabled. Contact admin.</p>";
    header('Location: ./login.php');
    exit;
}

/* =========================
   PASSWORD VERIFY
========================= */
if (!password_verify($password, $admin['password'])) {
    $_SESSION['status'] = "<p style='color:red;'>Invalid email or password!</p>";
    header('Location: ./login.php');
    exit;
}

/* =========================
   LOGIN SUCCESS
========================= */
session_regenerate_id(true); // 🔒 Prevent session fixation

$_SESSION['auth'] = true;
$_SESSION['auth_role'] = $admin['role'];
$_SESSION['auth_user'] = [
    'user_id'    => $admin['id'],
    'user_name'  => $admin['name'],
    'user_email' => $admin['email'],
    'user_phone' => $admin['phone'],
];

$_SESSION['status'] = "<p style='color:blue;'>Logged in successfully!</p>";

header('Location: ./index.php');
exit;
}