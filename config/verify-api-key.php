<?php
date_default_timezone_set('Asia/Kolkata');
function verify_api_key($headers)
{
    global $API_KEYS;

    // Normalize headers to lowercase
    $normalized = [];
    foreach ($headers as $key => $value) {
        $normalized[strtolower($key)] = $value;
    }

    // Check API key (case-insensitive)
    if (!isset($normalized['x-api-key'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Missing API key']);
        exit;
    }

    $api_key = trim($normalized['x-api-key']);

    if (!in_array($api_key, $API_KEYS, true)) {
        http_response_code(403);
        echo json_encode(['error' => 'Invalid API key']);
        exit;
    }

    return true;
}
