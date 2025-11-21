<?php
// Start session for user authentication
session_start();

// Autoload classes
spl_autoload_register(function ($class) {
    $file = __DIR__ . '/../app/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

// Include config
require __DIR__ . '/../config/config.php';

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

// Authenticated user routes
$router->get('/profile', 'Controllers\ProfileController@index');
$router->get('/profile/musics', 'Controllers\ProfileController@musics');
$router->get('/m/new', 'Controllers\MusicController@create');
$router->post('/m/new', 'Controllers\MusicController@store');
$router->get('/m/new/success', 'Controllers\MusicController@success');

// Admin routes
$router->get('/admin', 'Controllers\AdminController@index');
$router->get('/admin/users', 'Controllers\AdminController@users');
$router->get('/admin/comments', 'Controllers\AdminController@comments');
$router->get('/admin/musics', 'Controllers\AdminController@musics');

// Dispatch the request
$router->dispatch($uri, $_SERVER['REQUEST_METHOD']);
