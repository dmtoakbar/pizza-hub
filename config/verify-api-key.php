<?php
function verify_api_key($headers) {
    
    global $API_KEY_PARAM;
    global $API_KEYS;

    if (!isset($headers[$API_KEY_PARAM])) {
        http_response_code(401);
        echo json_encode(['error' => 'Missing API key']);
        exit;
    }

    $api_key = trim($headers[$API_KEY_PARAM]);

    if (!in_array($api_key, $API_KEYS)) {
        http_response_code(403);
        echo json_encode(['error' => 'Invalid API key']);
        exit;
    }

    // ✅ API key is valid
    return true;
}

?>