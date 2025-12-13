<?php

namespace Controllers;

use PDO;

class HomeController
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function index()
    {
        // Fetch public musics with author username, ordered by newest
        $sql = "SELECT m.*, u.username 
                FROM musics m 
                JOIN users u ON m.user_id = u.id 
                WHERE m.visibility = 'public' 
                ORDER BY m.created_at DESC";
        
        $stmt = $this->pdo->query($sql);
        $musics = $stmt->fetchAll(PDO::FETCH_ASSOC);

        include __DIR__ . '/../Views/home.php';
    }
}
