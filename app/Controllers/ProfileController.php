<?php
namespace Controllers;

use Models\User;
use Core\Auth;

class ProfileController
{
    private $pdo;
    private $userModel;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->userModel = new User($pdo);
    }

    public function index()
    {
        // Must be logged in (route already calls requireLogin())
        $user = Auth::user(); // return $_SESSION['user']

        if (!$user) {
            header("Location: " . BASE_URL . "login");
            exit;
        }

        // Fetch fresh data from DB
        
        $profile = $this->userModel->findById($_SESSION['user_id']);

        if (!$profile) {
            die("Utilisateur introuvable.");
        }

        include __DIR__ . '/../Views/profile.php';
    }
}
