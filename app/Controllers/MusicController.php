<?php
namespace Controllers;

use Models\Music;
use Core\Auth;

class MusicController {

    private $pdo;
    private $musicModel;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->musicModel = new Music($pdo);
    }

    public function show($id) {
        $music = $this->musicModel->find($id);
        
        if (!$music) {
            http_response_code(404);
            echo "Music not found.";
            return;
        }

        // Fetch comments and rating for the player
        $comments = $this->musicModel->getComments($id);
        $avgRating = $this->musicModel->getAvgRating($id);

        // We do NOT use the standard layout because the player is full screen
        // We include the view directly
        include __DIR__ . "/../Views/music/musicPage.php";
    }

    public function postComment($id) {
        if (!Auth::check()) {
            header("Location: " . BASE_URL . "/login");
            exit;
        }

        if (isset($_POST['comment'])) {
            $content = trim($_POST['comment']);
            $timestamp = isset($_POST['timestamp']) ? (int)$_POST['timestamp'] : 0;
            
            if (!empty($content)) {
                $this->musicModel->addComment($_SESSION['user_id'], $id, $content, $timestamp);
            }
        }
        
        // Redirect back to the player with drawer open
        header("Location: " . BASE_URL . "/m/" . $id . "?drawer=open");
        exit;
    }

    public function uploadForm() {
        include __DIR__ . "/../Views/music/uploadForm.php";
    }
    public function success() {
        include __DIR__ . "/../Views/music/uploadSuccess.php";
    }
}
