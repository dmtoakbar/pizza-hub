<?php
require_once __DIR__ . '/../verify-each-request.php';

function createTables($conn) {
    // --- Users Table ---
    $userTable = "
    CREATE TABLE IF NOT EXISTS users (
        id CHAR(36) PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(150) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;
    ";
    $conn->query($userTable);

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
