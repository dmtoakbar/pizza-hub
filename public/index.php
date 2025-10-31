<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('ROUTER_INCLUDED', true);
require_once __DIR__ . '/../config/constants.php';

$uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

// remove base url
$uri = preg_replace("#^$basePath/#", '', $uri);

// Handle media requests
if (str_starts_with($uri, 'media/')) {
    require_once __DIR__ . '/../storage/get-media.php';
    exit;
}

require_once __DIR__ . '/../config/index.php';
require_once __DIR__ . '/../api/schema/tables.php';

// ✅ Create tables if not exist
createTables($conn);

// Get headers
$headers = getallheaders();
verify_api_key($headers);

// Get path and method
$method = $_SERVER['REQUEST_METHOD'];

require_once __DIR__ . '/../api/api-route/route.php';

send_json(['error' => 'Invalid route'], 404);

?>