<?php
require_once __DIR__ . '/../../api/verify-each-request.php';
// Simple Router
switch ($uri) {
    case 'users':
        require_once __DIR__ . '/../../api/users.php';
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
        require_once __DIR__ . '/../../api/products.php';
        if ($method === 'GET') {
            send_json(get_products());
        }
        break;

    default:
        send_json(['error' => 'Invalid route'], 404);
}
?>