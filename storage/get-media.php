<?php
require_once __DIR__ . '/../config/verify-each-request.php';

if (str_contains($uri, 'media/')) {
    $uri = preg_replace('#^media/#', '', $uri, 1);
    $storageRoot = realpath(__DIR__);
    $filePath = realpath($storageRoot . '/' . $uri);
    
    if ($filePath === false || strpos($filePath, $storageRoot) !== 0) {
        http_response_code(403);
        echo json_encode(['error' => 'Access denied']);
        exit;
    }

    if (file_exists($filePath)) {
        header('Content-Type: ' . mime_content_type($filePath));
        readfile($filePath);
        exit;
    }

    http_response_code(404);
    echo json_encode(['error' => 'File not found']);
    exit;
}

?>







