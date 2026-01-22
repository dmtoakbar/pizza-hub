<?php
session_start();
if (isset($_POST['logout-btn'])) {
    unset($_SESSION['auth']);
    unset($_SESSION['auth_user']);
    unset($_SESSION['email']);
    unset($_SESSION['password']);
    $_SESSION['status'] = "Logged Out Successfully !";
    header("Location: ../../../login.php");
    exit(0);
}
?>