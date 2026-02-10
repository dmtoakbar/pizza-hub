<?php
require_once __DIR__ . '/../../config/constants/constants.php';
require_once __DIR__ . '/../../config/verify-each-request.php';

if (str_contains($uri, "$apiBasePath/" . $allowedVersions[0])) {

    if (preg_match("#$apiBasePath/(v\d+)/(.*)#", $uri, $matches)) {
        $apiVersion = $matches[1];
        $uri = $matches[2];

        if (!in_array($apiVersion, $allowedVersions)) {
            http_response_code(400);
            echo json_encode(['error' => 'Unsupported API version']);
            exit;
        }
    }

    switch ($uri) {
        case 'users':
            require_once __DIR__ . '/../users.php';
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

        case preg_match('/^auth\//', $uri) == 1:
            require_once __DIR__ . '/auth.php';
            break;

        case 'products':
            require_once __DIR__ . '/../features/product/get-product.php';
            send_json(getProducts());
            break;

         case 'home-banners':
            require_once __DIR__ . '/../features/homeBanner/home-banner.php';
            send_json(getHomeBanners());
            break;

         case 'product-category-list':
            require_once __DIR__ . '/../features/produtCategory/get-product-category-list.php';
            send_json(getCategories());
            break;

         case 'search':
            require_once __DIR__ . '/../features/search/search.php';
            send_json(advancedProductSearch());
            break;

        case 'extra-toppings':
            require_once __DIR__ . '/../features/extraToppings/extra-topping.php';
            send_json(getExtraToppings());
            break;

        case 'contact-us':
            require_once __DIR__ . '/../features/contactUs/contact-us.php';
            send_json(contactUs());
            break;

        case 'report-issue':
            require_once __DIR__ . '/../features/report/report.php';
            send_json(submitReport());
            break;

        case 'place-order':
            require_once __DIR__ . '/../features/orders/place-order/place-order.php';
            send_json(placeOrder());
            break;

        case 'order-history':
            require_once __DIR__ . '/../features/orders/history/history.php';
            send_json(getOrderHistory());
            break;

        case 'order-detail':
            require_once __DIR__ . '/../features/orders/order-detail/get_order_details.php';
            send_json(getOrderDetails());
            break;

        case 'order-status':
            require_once __DIR__ . '/../features/orders/order-status/order-status.php';
            send_json(getOrderStatus());
            break;
            
        case 'policy-pages':
            require_once __DIR__ . '/../features/policyPage/policy-page.php';
            send_json(getStaticPages());
            break;
        default:
            send_json(['error' => 'Invalid route'], 404);
    }
}
