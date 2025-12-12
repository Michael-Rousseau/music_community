<?php
// router.php
$publicFolder = __DIR__ . '/public';

$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
$file = $publicFolder . $path;

if (is_file($file)) {
    return false; // serve the file normally
}

require $publicFolder . '/index.php';
