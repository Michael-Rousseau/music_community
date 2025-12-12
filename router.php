<?php
// router.php (At the root of the project)
$publicFolder = __DIR__ . '/public';

$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
$file = $publicFolder . $path;

// If the file exists (like an image or CSS), serve it directly
if (is_file($file)) {
    return false; 
}

// Otherwise, send everything to index.php
require $publicFolder . '/index.php';
