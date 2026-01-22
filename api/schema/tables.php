<?php
require_once __DIR__ . '/../../config/verify-each-request.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use Ramsey\Uuid\Uuid;

function createTables($conn)
{
    // --- Users Table ---
    $userTable = "
    CREATE TABLE IF NOT EXISTS users (
        id CHAR(36) PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        phone VARCHAR(100) NOT NULL,
        address VARCHAR(255) NOT NULL,
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

    $otpVerificationTable = "
    CREATE TABLE IF NOT EXISTS otp_verifications (
        id CHAR(36) PRIMARY KEY,
        user_id CHAR(36) NOT NULL,
        otp VARCHAR(10) NOT NULL,
        purpose ENUM('email_verification', 'password_reset', 'login', 'two_factor', 'phone_verification') NOT NULL,
        expires_at DATETIME NOT NULL,
        verified TINYINT(1) NOT NULL DEFAULT 0,
        status ENUM('pending', 'verified', 'expired') NOT NULL DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_otp_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX idx_user_purpose (user_id, purpose)
    ) ENGINE=InnoDB;
    ";
    $conn->query($otpVerificationTable);



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


    $adminTable = "
        CREATE TABLE IF NOT EXISTS admins (
            id CHAR(36) PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            phone VARCHAR(100) NOT NULL,
            address VARCHAR(255) NOT NULL,
            email VARCHAR(150) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            role ENUM('super_admin', 'admin', 'editor', 'reader')
                NOT NULL DEFAULT 'admin',
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB;
        ";

    if (!$conn->query($adminTable)) {
        throw new Exception("Admins table error: " . $conn->error);
    }

    /* =========================
           DEFAULT ADMIN
        ========================== */
    $result = $conn->query("SELECT COUNT(*) AS total FROM admins");
    $adminCount = $result->fetch_assoc()['total'];

    if ($adminCount == 0) {
        $adminId = Uuid::uuid4()->toString();
        $passwordHash = password_hash('admin@123', PASSWORD_DEFAULT);

        if (!$conn->query("
                INSERT INTO admins (
                    id, name, phone, address, email, password, role
                ) VALUES (
                    '$adminId',
                    'Super Admin',
                    '9999999999',
                    'System Default Address',
                    'admin@example.com',
                    '$passwordHash',
                    'super_admin'
                )
            ")) {
            throw new Exception("Default admin insert error: " . $conn->error);
        }
    }


    /* =========================
           CONTACT US TABLE
           ============================ */

    $contactUsTable = "
        CREATE TABLE IF NOT EXISTS contact_us (
            id CHAR(36) NOT NULL PRIMARY KEY,

            name VARCHAR(150) NOT NULL,
            email VARCHAR(150) NOT NULL,
            phone VARCHAR(20) NOT NULL,

            subject VARCHAR(150) NOT NULL,
            message TEXT NOT NULL,

            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB;
        ";

    if (!$conn->query($contactUsTable)) {
        throw new Exception('Contact Us table error: ' . $conn->error);
    }


    // report
    $reportTable = "
        CREATE TABLE IF NOT EXISTS report (
            id CHAR(36) NOT NULL PRIMARY KEY,

            name VARCHAR(150) NOT NULL,
            email VARCHAR(150) NOT NULL,
            phone VARCHAR(20) NOT NULL,
            address TEXT NOT NULL,

            order_id VARCHAR(100) NOT NULL,
            issue VARCHAR(150) NOT NULL,
            issue_message TEXT NOT NULL,

            status ENUM('pending', 'in_progress', 'resolved') DEFAULT 'pending',

            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB;
        ";

    if (!$conn->query($reportTable)) {
        throw new Exception('Report table error: ' . $conn->error);
    }

    /* =========================
           PRODUCTS TABLE
        ========================== */
    $productTable = "
        CREATE TABLE IF NOT EXISTS products (
            id CHAR(36) NOT NULL PRIMARY KEY,  
            name VARCHAR(150) NOT NULL,  
            price DECIMAL(10,2) NOT NULL,  
            tag VARCHAR(100) NOT NULL, 
            tag_description VARCHAR(255) NOT NULL, 
            image VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB;
        ";

    if (!$conn->query($productTable)) {
        throw new Exception("Products table error: " . $conn->error);
    }


    $orders = "
        CREATE TABLE IF NOT EXISTS orders (
        id CHAR(36) NOT NULL PRIMARY KEY,

        username VARCHAR(150) NOT NULL,
        email VARCHAR(150) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        address TEXT NOT NULL,

        total_amount DECIMAL(10,2) NOT NULL,

        payment_method ENUM('cod','upi','card') DEFAULT 'cod',
        payment_status ENUM('unpaid','paid') DEFAULT 'unpaid',

        status ENUM(
            'pending',
            'accepted',
            'preparing',
            'ready',
            'out_for_delivery',
            'delivered',
            'cancelled'
        ) DEFAULT 'pending',

        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB;
        
        ";

    if (!$conn->query($orders)) {
        throw new Exception("Orders table error: " . $conn->error);
    }

    $order_items = "
             CREATE TABLE IF NOT EXISTS order_items (
            id INT AUTO_INCREMENT PRIMARY KEY,

            order_id CHAR(36) NOT NULL,
            product_id CHAR(36) NOT NULL,

            product_name VARCHAR(150) NOT NULL,
            product_price DECIMAL(10,2) NOT NULL,
            product_image VARCHAR(255) NOT NULL,

            quantity INT DEFAULT 1,

            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
        ) ENGINE=InnoDB;

        ";

    if (!$conn->query($order_items)) {
        throw new Exception("order_items table error: " . $conn->error);
    }


    $order_item_extras = "
            CREATE TABLE IF NOT EXISTS order_item_extras (
            id INT AUTO_INCREMENT PRIMARY KEY,

            order_item_id INT NOT NULL,

            extra_name VARCHAR(100) NOT NULL,
            extra_price DECIMAL(10,2) NOT NULL,

            FOREIGN KEY (order_item_id) REFERENCES order_items(id) ON DELETE CASCADE
        ) ENGINE=InnoDB;
        ";

    if (!$conn->query($order_item_extras)) {
        throw new Exception("order_item_extras table error: " . $conn->error);
    }
}
