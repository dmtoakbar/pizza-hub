<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('ROUTER_INCLUDED', true);
require_once __DIR__ . '/../config/constants/constants.php';

$uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$uri = preg_replace("#^$basePath/#", '', $uri);




if (!function_exists('getallheaders')) {
    function getallheaders() {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $key = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                $headers[$key] = $value;
            }
        }
        return $headers;
    }
}
$headers = getallheaders();


// ✅ ADMIN ROUTES
if ($uri === '' || str_starts_with($uri, 'admin')) {
    require_once __DIR__ . '/../admin/login.php';
    exit;
}

// ✅ MEDIA (NO AUTH)
if (str_starts_with($uri, 'media/')) {
    require_once __DIR__ . '/../storage/get-media.php';
    exit;
}

// 🔐 API ROUTES ONLY
if (str_contains($uri, $apiBasePath)) {

    require_once __DIR__ . '/../config/index.php';
    require_once __DIR__ . '/../api/schema/tables.php';
    createTables($conn);

    verify_api_key($headers);
    require_once __DIR__ . '/../api/api-route/route.php';
    exit;
}

require_once __DIR__ . '/../config/handle-api-request.php';
// ❌ Fallback
send_json(['error' => 'Invalid route'], 404);
