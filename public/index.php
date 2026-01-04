<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Controllers\HomeController;
use App\Controllers\MusicController;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\AdminController;

session_start();

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath = ''; 
if (strpos($uri, $basePath) === 0) {
    $uri = substr($uri, strlen($basePath));
}

switch ($uri) {
    case '/':
    case '/index.php':
        (new HomeController())->index();
        break;

    case '/music':
        (new MusicController())->show();
        break;

    case '/music/stream':             
        (new MusicController())->stream(); 
        break;

    // Auth
    case '/login':
        (new AuthController())->login();
        break;
    case '/register':
        (new AuthController())->register();
        break;
    case '/verify':
        (new AuthController())->verify();
        break;
    case '/logout':
        (new AuthController())->logout();
        break;

    // Dashboard
    case '/dashboard':
        (new DashboardController())->index();
        break;
    case '/dashboard/edit':
        (new DashboardController())->edit();
        break;
    case '/dashboard/delete':
        (new DashboardController())->delete();
        break;

        // Admin
    case '/admin':
        (new AdminController())->index();
        break;

    default:
        http_response_code(404);
        echo "404 Not Found";
        break;
}
