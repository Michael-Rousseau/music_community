<?php
namespace Controllers;

use Models\Music;

class HomeController {
    
    private $pdo;
    private $musicModel;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->musicModel = new Music($pdo);
    }

    public function index() {
        // Fetch public musics for the homepage grid
        $search = isset($_GET['q']) ? trim($_GET['q']) : '';
        $musics = $this->musicModel->findAllPublic($search);

        include __DIR__ . '/../Views/home.php';
    }
}
