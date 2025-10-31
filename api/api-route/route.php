<?php
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/verify-each-request.php';

// Simple Router
if (str_contains($uri, "$apiBasePath/".$allowedVersions[0])) {

if (preg_match("#$apiBasePath/(v\d+)/(.*)#", $uri, $matches)) {
    $apiVersion = $matches[1]; // "v1"
    $uri = $matches[2];        // "users"

    if (!in_array($apiVersion, $allowedVersions)) {
        http_response_code(400);
        echo json_encode(['error' => 'Unsupported API version']);
        exit;
    }

}

switch ($uri) {
    case 'users':
        require_once __DIR__ . '/../users.php';
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
        require_once __DIR__ . '/../products.php';
        if ($method === 'GET') {
            send_json(get_products());
        }
        break;

    default:
        send_json(['error' => 'Invalid route'], 404);
}

 }
?>