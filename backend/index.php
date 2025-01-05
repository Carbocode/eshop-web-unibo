<?php

// Error reporting
require 'error.php';

// Composer autoloader
require 'vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();
$dotenv->required(['JWT_SECRET', 'DB_USER', 'DB_PWD', 'DB_PORT']);

// Load application files
require_once __DIR__ . '/src/config/database.php';
require_once __DIR__ . '/src/utils/ApiResponse.php';
require_once __DIR__ . '/src/middleware/Auth.php';
require_once __DIR__ . '/src/controllers/BaseController.php';
require_once __DIR__ . '/src/controllers/AuthController.php';
require_once __DIR__ . '/src/controllers/CartController.php';
require_once __DIR__ . '/src/controllers/CheckoutController.php';
require_once __DIR__ . '/src/controllers/TeamsController.php';
require_once __DIR__ . '/src/controllers/OrderController.php';

// Import required classes
use App\Utils\ApiResponse;
use App\Middleware\Auth;
use App\Controllers\AuthController;
use App\Controllers\CartController;
use App\Controllers\CheckoutController;
use App\Controllers\TeamsController;
use App\Controllers\OrderController;

// Initialize authentication
Auth::init();

// Set CORS headers
ApiResponse::setCorsHeaders();

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Route the request
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

try {
    switch (true) {
        case strpos($uri, '/auth') === 0:
            $controller = new AuthController();
            break;
            
        case strpos($uri, '/cart') === 0:
            $controller = new CartController();
            break;
            
        case strpos($uri, '/checkout') === 0:
            $controller = new CheckoutController();
            break;
            
        case strpos($uri, '/teams') === 0:
            $controller = new TeamsController();
            break;

        case strpos($uri, '/orders') === 0:
            $controller = new OrderController();
            break;
            
        case $uri === '/info':
            phpinfo();
            exit;
            
        default:
            ApiResponse::error('Not Found', 404);
    }

    $controller->processRequest();
    
} catch (Exception $e) {
    ApiResponse::error(
        $e->getMessage(),
        $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500
    );
}