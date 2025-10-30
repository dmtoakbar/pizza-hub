<?php
require_once __DIR__ . '/../config/config.php';

function send_json($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}


function verify_api_key($headers) {
    
    global $API_KEYS;

    if (!isset($headers['X-API-KEY'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Missing API key']);
        exit;
    }

    $api_key = trim($headers['X-API-KEY']);

    if (!in_array($api_key, $API_KEYS)) {
        http_response_code(403);
        echo json_encode(['error' => 'Invalid API key']);
        exit;
    }

    // ✅ API key is valid
    return true;
}

?>