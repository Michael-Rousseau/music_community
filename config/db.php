<?php
// config/db.php

$host = 'localhost';
$db   = 'music_community';
$user = 'root';
$pass = 'Collector10'; 
$charset = 'utf8mb4';

// --- 2. Configuration du DSN (Data Source Name) ---
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Affiche les erreurs SQL
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,     // Retourne des tableaux associatifs
    PDO::ATTR_EMULATE_PREPARES   => false,                // Sécurité native
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>
