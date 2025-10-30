<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('ROUTER_INCLUDED', true);
require_once __DIR__ . '/get-media.php';
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


require_once __DIR__ . '/api-route/route.php';

?>