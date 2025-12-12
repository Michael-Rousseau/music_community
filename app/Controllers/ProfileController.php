<?php
namespace Controllers;

use Models\User;
use Models\Music;
use Core\Auth;

class ProfileController {
    private $pdo;
    private $userModel;
    private $musicModel;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->userModel = new User($pdo);
        $this->musicModel = new Music($pdo);
    }

    public function index() {
        $user = Auth::user();
        if (!$user) { header("Location: " . BASE_URL . "/login"); exit; }

        // Fetch My Musics
        $my_musics = $this->musicModel->findAllByUser($_SESSION['user_id']);
        $userName = $_SESSION['username'];
        $userRole = $_SESSION['role'];

        include __DIR__ . '/../Views/profile.php';
    }
}
