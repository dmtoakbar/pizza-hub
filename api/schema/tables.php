<?php
require_once __DIR__ . '/../../config/verify-each-request.php';

function createTables($conn)
{
    // --- Users Table ---
    $userTable = "
    CREATE TABLE IF NOT EXISTS users (
        id CHAR(36) PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(150) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        email_verified VARCHAR(10) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;
    ";
    $conn->query($userTable);

   $emailVerificationTable = "
    CREATE TABLE IF NOT EXISTS email_verifications (
        id CHAR(36) PRIMARY KEY,
        user_id CHAR(36) NOT NULL,
        uid CHAR(36) NOT NULL,
        token VARCHAR(255) NOT NULL,
        expires_at DATETIME NOT NULL,
        verified TINYINT(1) NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_email_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE KEY unique_uid_token (uid, token)
    ) ENGINE=InnoDB;
    ";
    $conn->query($emailVerificationTable);


    $userSessionTable = "
    CREATE TABLE IF NOT EXISTS user_sessions (
        id CHAR(36) NOT NULL PRIMARY KEY,
        user_id CHAR(36) NOT NULL,
        token VARCHAR(255) NOT NULL UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        expires_at TIMESTAMP NULL,
        CONSTRAINT fk_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB;
    ";
    $conn->query($userSessionTable);

    // --- Products Table ---
    $productTable = "
    CREATE TABLE IF NOT EXISTS products (
        id CHAR(36) PRIMARY KEY,
        name VARCHAR(150) NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;
    ";
    $conn->query($productTable);

    // --- API Keys Table ---
    $apiTable = "
    CREATE TABLE IF NOT EXISTS api_keys (
        id CHAR(36) PRIMARY KEY,
        api_key VARCHAR(255) NOT NULL UNIQUE,
        owner VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;
    ";
    $conn->query($apiTable);
}
