<?php
require_once __DIR__ . '/../api/verify-each-request.php';

$uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

if (str_contains($uri, 'storage/')) {

   $storageRoot = realpath(__DIR__);
   $filePath = realpath($storageRoot . '/' . str_replace('storage/', '', $uri));

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







