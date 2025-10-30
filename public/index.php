<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
define('ROUTER_INCLUDED', true);
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../api/helpers.php';
require_once __DIR__ . '/../api/schema/tables.php';

// ✅ Create tables if not exist
createTables($conn);

// Get headers
$headers = getallheaders();
verify_api_key($headers);

// Get path and method
$basePath = 'api-structure';
$uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$uri = preg_replace("#^$basePath/#", '', $uri);
$method = $_SERVER['REQUEST_METHOD'];


// Simple Router
switch ($uri) {
    case 'users':
        require_once __DIR__ . '/../api/users.php';
        if ($method === 'GET') {
            if (isset($_GET['id'])) {
                $user = get_user_by_id($_GET['id']);
                if ($user) send_json($user);
                send_json(['error' => 'User not found'], 404);
            } else {
                send_json(get_users());
            }
        }
        break;

    case 'products':
        require_once __DIR__ . '/../api/products.php';
        if ($method === 'GET') {
            send_json(get_products());
        }
        break;

    default:
        send_json(['error' => 'Invalid route'], 404);
}


?>