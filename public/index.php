<?php
// Start session for user authentication
session_start();
require_once '../config/db.php';

// Autoload classes
spl_autoload_register(function ($class) {
    $file = __DIR__ . '/../app/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

// Include config
require __DIR__ . '/../config/config.php';


// Create PDO connection
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

require __DIR__ . '/../app/Core/helpers.php';
require __DIR__ . '/../vendor/autoload.php';



use Controllers\AuthController;
use Controllers\ProfileController;
use Controllers\MusicController;


// Simple Router
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Remove trailing slashes
$uri = rtrim($uri, '/');

use Core\Router;
$router = new Router();

// Public routes
$router->get('/', 'Controllers\HomeController@index');
$router->get('', 'Controllers\HomeController@index');
$router->get('/m/(\d+)', 'Controllers\MusicController@show');

// Auth routes
$router->get('/login', function() use ($pdo){ (new AuthController($pdo))->showLogin(); });
$router->post('/login', function() use ($pdo){ (new AuthController($pdo))->login(); });
$router->get('/signup', function() use ($pdo){ (new AuthController($pdo))->showSignup(); });
$router->post('/signup', function() use ($pdo){ (new AuthController($pdo))->signup(); });
$router->get('/verify', function() use ($pdo){ (new AuthController($pdo))->verify(); });
$router->get('/logout', function() use ($pdo){ (new AuthController($pdo))->logout(); });

$router->post('/m/(\d+)/comment', 'Controllers\MusicController@postComment');

// Authenticated user routes
$router->get('/profile', function() use ($pdo) {
    requireLogin();  // blocks anonymous users
    (new ProfileController($pdo))->index();
});

$router->get('/m/new', function() use ($pdo) {
    requireLogin();  // blocks anonymous users
    (new MusicController($pdo))->uploadForm();
});
$router->post('/m/new', function() use ($pdo) {
    requireLogin();  // blocks anonymous users
    (new MusicController($pdo))->create();
});
$router->get('/m/new/success', function() use ($pdo) {
    requireLogin();  // blocks anonymous users
    (new MusicController($pdo))->success();
});


// Admin routes
$router->get('/admin', 'Controllers\AdminController@index');
$router->get('/admin/users', 'Controllers\AdminController@users');
$router->get('/admin/comments', 'Controllers\AdminController@comments');
$router->get('/admin/musics', 'Controllers\AdminController@musics');

// Dispatch the request
$router->dispatch($uri, $_SERVER['REQUEST_METHOD']);
