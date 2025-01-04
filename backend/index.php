<?php
require 'error.php';
require 'vendor/autoload.php';
// index.php - Main router
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

switch ($request) {
    case '/auth/login':
        require __DIR__ . '/src/api/auth.php';
        break;
    case '/auth/register':
        require __DIR__ . '/src/api/auth.php';
        break;
    case '/cart':
    case '/cart/summary':
        require __DIR__ . '/src/api/cart.php';
        break;
    case '/checkout':
        require __DIR__ . '/src/api/checkout.php';
        break;
    case '/info':
        echo phpinfo();
        break;
    default:
        http_response_code(404);
        echo $request;
        echo json_encode(['error' => 'Not Found']);
        break;
}
?>