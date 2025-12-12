<?php
// public/index.php

session_start();

// Load Config
require_once __DIR__ . '/../config/config.php';

// Manual Autoloading
require_once __DIR__ . '/../app/Core/Router.php';
require_once __DIR__ . '/../app/Core/Auth.php';
require_once __DIR__ . '/../app/Core/helpers.php';
require_once __DIR__ . '/../app/Models/User.php';
require_once __DIR__ . '/../app/Models/Music.php';
require_once __DIR__ . '/../app/Controllers/HomeController.php';
require_once __DIR__ . '/../app/Controllers/AuthController.php';
require_once __DIR__ . '/../app/Controllers/MusicController.php';
require_once __DIR__ . '/../app/Controllers/ProfileController.php';

// Database Connection
try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB Connection failed: " . $e->getMessage());
}

use Core\Router;
$router = new Router();

// --- Routes ---

// Home
$router->get('/', 'Controllers\HomeController@index');

// Auth
$router->get('/login', 'Controllers\AuthController@showLogin');
$router->post('/login', 'Controllers\AuthController@login');
$router->get('/signup', 'Controllers\AuthController@showSignup');
$router->post('/signup', 'Controllers\AuthController@signup');
$router->get('/logout', 'Controllers\AuthController@logout');

// Profile
$router->get('/profile', 'Controllers\ProfileController@index');

// Music - Upload
$router->get('/m/new', 'Controllers\MusicController@uploadForm');
$router->post('/m/new', 'Controllers\MusicController@create');
$router->get('/m/new/success', 'Controllers\MusicController@success');

// Music - Player
$router->get('/m/(\d+)', 'Controllers\MusicController@show');
$router->post('/m/(\d+)/comment', 'Controllers\MusicController@postComment');
$router->post('/m/(\d+)/rate', 'Controllers\MusicController@rate');

// Music - Edit & Delete (ADDED THESE)
$router->get('/m/edit/(\d+)', 'Controllers\MusicController@editForm');
$router->post('/m/edit/(\d+)', 'Controllers\MusicController@update');
$router->post('/m/delete/(\d+)', 'Controllers\MusicController@delete');

// Dispatch
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];
$router->dispatch($uri, $method);
