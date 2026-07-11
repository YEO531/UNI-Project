<?php
/**
 * Main Router
 * 
 * Handles routing for the application
 */

// Start session
session_start();

// Load configuration
require_once __DIR__ . '/../config/config.php';

// Load utility functions
require_once __DIR__ . '/../src/utils/Util.php';

// Define routes
$routes = [
    // Auth routes
    'GET|/login' => ['AuthController', 'login'],
    'POST|/login' => ['AuthController', 'processLogin'],
    'GET|/register' => ['AuthController', 'register'],
    'POST|/register' => ['AuthController', 'processRegister'],
    'GET|/logout' => ['AuthController', 'logout'],
    'GET|/forgot-password' => ['AuthController', 'forgotPassword'],
    'POST|/forgot-password' => ['AuthController', 'processForgotPassword'],
    'GET|/reset-password' => ['AuthController', 'resetPassword'],
    'POST|/reset-password' => ['AuthController', 'processResetPassword'],
    
    // Dashboard routes
    'GET|/' => ['DashboardController', 'index'],
    'GET|/dashboard' => ['DashboardController', 'index'],
    'GET|/notifications/mark-read/(\d+)' => ['DashboardController', 'markNotificationRead'],
    
    // Room routes
    'GET|/rooms' => ['RoomController', 'index'],
    'GET|/rooms/view/(\d+)' => ['RoomController', 'view'],
    'GET|/rooms/available' => ['RoomController', 'available'],
    
    // Admin room routes
    'GET|/admin/rooms/manage' => ['RoomController', 'manage'],
    'GET|/admin/rooms/add' => ['RoomController', 'add'],
    'POST|/admin/rooms/add' => ['RoomController', 'processAdd'],
    'GET|/admin/rooms/edit/(\d+)' => ['RoomController', 'edit'],
    'POST|/admin/rooms/edit/(\d+)' => ['RoomController', 'processEdit'],
    'POST|/admin/rooms/status/(\d+)' => ['RoomController', 'updateStatus'],
    'POST|/admin/rooms/delete/(\d+)' => ['RoomController', 'delete'],
    
    // Booking routes
    'GET|/bookings' => ['BookingController', 'index'],
    'GET|/bookings/view/(\d+)' => ['BookingController', 'view'],
    'GET|/bookings/book/(\d+)' => ['BookingController', 'book'],
    'POST|/bookings/book' => ['BookingController', 'processBooking'],
    'POST|/bookings/cancel/(\d+)' => ['BookingController', 'cancel'],
    
    // Admin booking routes
    'GET|/admin/bookings/manage' => ['BookingController', 'manage'],
    'POST|/admin/bookings/status/(\d+)' => ['BookingController', 'updateStatus'],
    'GET|/admin/bookings/statistics' => ['BookingController', 'statistics'],
    
    // API routes
    'GET|/api/rooms' => ['ApiController', 'getRooms'],
    'GET|/api/rooms/available' => ['ApiController', 'getAvailableRooms'],
    'GET|/api/rooms/(\d+)' => ['ApiController', 'getRoom'],
    'GET|/api/bookings' => ['ApiController', 'getBookings'],
    'GET|/api/bookings/(\d+)' => ['ApiController', 'getBooking'],
    'POST|/api/bookings' => ['ApiController', 'createBooking'],
    'DELETE|/api/bookings/(\d+)' => ['ApiController', 'cancelBooking'],
    'GET|/api/user/profile' => ['ApiController', 'getUserProfile'],
    'PUT|/api/user/profile' => ['ApiController', 'updateUserProfile']
];

// Get current URI
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

// Route the request
$routeFound = false;

foreach ($routes as $pattern => $callback) {
    // Split pattern into method and route
    list($routeMethod, $routePattern) = explode('|', $pattern, 2);
    
    // Check if method matches
    if ($routeMethod !== $method) {
        continue;
    }
    
    // Convert route pattern to regex
    $routePattern = str_replace('/', '\/', $routePattern);
    $routePattern = '/^' . $routePattern . '$/';
    
    // Check if route matches
    if (preg_match($routePattern, $uri, $matches)) {
        $routeFound = true;
        
        // Remove full match from matches
        array_shift($matches);
        
        // Load controller
        $controllerName = $callback[0];
        $methodName = $callback[1];
        
        require_once __DIR__ . "/../src/controllers/{$controllerName}.php";
        
        // Create controller instance
        $controller = new $controllerName();
        
        // Call method with parameters
        call_user_func_array([$controller, $methodName], $matches);
        
        break;
    }
}

// If no route found, show 404 page
if (!$routeFound) {
    header('HTTP/1.0 404 Not Found');
    echo '404 Not Found';
    exit;
}
