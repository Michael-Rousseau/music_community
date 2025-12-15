<?php
namespace App\Config;

use PDO;
use PDOException;

class Database {
    private static $pdo = null;

    public static function getConnection() {
        if (self::$pdo === null) {
            $host = 'localhost';
            $db   = 'music_community';
            $user = 'root';
            $pass = 'Tempo528491'; 

            try {
                self::$pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                die("DB Error: " . $e->getMessage());
            }
        }
        return self::$pdo;
    }
}
