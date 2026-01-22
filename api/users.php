<?php
require_once __DIR__ . '/../config/verify-each-request.php';  

function get_users() {
    return [
        ['id' => 1, 'name' => 'Amit'],
        ['id' => 2, 'name' => 'John'],
    ];
}

function get_user_by_id($id) {
    $users = get_users();
    foreach ($users as $user) {
        if ($user['id'] == $id) return $user;
    }
    return null;
}
