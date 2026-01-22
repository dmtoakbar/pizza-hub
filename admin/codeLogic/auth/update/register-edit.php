<?php

require_once __DIR__ . '/../../../../config/database.php';
require_once __DIR__ . '/../../../../vendor/autoload.php';

if (isset($_POST['updateUser'])) {

    // INPUT SANITIZATION

    if (
        $_SESSION['auth_role'] !== 'super_admin' &&
        $_SESSION['auth_role'] !== 'admin'
    ) {
        $_SESSION['status'] = "You are not authorized to perform this action!";
        header("Location: ./register.php");
        exit;
    }


    $user_id   = trim($_POST['user_id']);
    $name      = trim($_POST['name']);
    $phone     = trim($_POST['phone']);
    $password  = trim($_POST['password']);
    $role      = trim($_POST['role']);
    $is_active = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;

    // BASIC VALIDATION

    if ($user_id === '' || $name === '' || $phone === '' || $role === '') {
        $_SESSION['status'] = "All required fields must be filled.";
        header("Location: ./register-edit.php?user_id=$user_id");
        exit;
    }


    //  ROLE VALIDATION (ENUM SAFE)

    $allowed_roles = ['super_admin', 'admin', 'editor', 'reader'];
    if (!in_array($role, $allowed_roles, true)) {
        $_SESSION['status'] = "Invalid role selected!";
        header("Location: ./register-edit.php?user_id=$user_id");
        exit;
    }


    //  PASSWORD HANDLING

    if ($password !== '') {
        // New password entered → hash it
        $hash = password_hash($password, PASSWORD_DEFAULT);
    } else {
        // No password entered → keep existing password
        $stmt = $conn->prepare("SELECT password FROM admins WHERE id = ?");
        $stmt->bind_param("s", $user_id);
        $stmt->execute();
        $stmt->bind_result($hash);
        $stmt->fetch();
        $stmt->close();
    }

    $update = $conn->prepare("
    UPDATE admins 
    SET 
        name = ?, 
        phone = ?, 
        password = ?, 
        role = ?, 
        is_active = ?
    WHERE id = ?
");

    $update->bind_param(
        "ssssis",
        $name,
        $phone,
        $hash,
        $role,
        $is_active,
        $user_id
    );

    if ($update->execute()) {
        $_SESSION['status'] = "Admin updated successfully!";
    } else {
        $_SESSION['status'] = "Failed to update admin!";
    }

    $update->close();

    header("Location: ./register.php");
    exit;
}
