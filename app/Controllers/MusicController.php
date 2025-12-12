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

    public function create() {
        if (!Auth::check()) {
            header("Location: " . BASE_URL . "/login");
            exit;
        }

        $error = '';
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');

        // Upload Directories
        $publicDir = __DIR__ . '/../../public';
        $uploadDirMp3 = $publicDir . '/uploads/mp3/';
        $uploadDirImg = $publicDir . '/uploads/images/';

        // Create folders if they don't exist
        if (!is_dir($uploadDirMp3)) mkdir($uploadDirMp3, 0777, true);
        if (!is_dir($uploadDirImg)) mkdir($uploadDirImg, 0777, true);

        // Handle MP3
        $mp3Name = '';
        if (isset($_FILES['mp3']) && $_FILES['mp3']['error'] === 0) {
            $ext = pathinfo($_FILES['mp3']['name'], PATHINFO_EXTENSION);
            if (strtolower($ext) !== 'mp3') {
                $error = "Seuls les fichiers MP3 sont acceptés.";
            } else {
                $mp3Name = uniqid('music_') . '.' . $ext;
                if (!move_uploaded_file($_FILES['mp3']['tmp_name'], $uploadDirMp3 . $mp3Name)) {
                    $error = "Erreur lors de l'upload du MP3.";
                }
            }
        } else {
            $error = "Fichier MP3 requis.";
        }

        // Handle Image
        $imageName = 'default.jpg'; // default or null
        if (empty($error) && isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            if (in_array(strtolower($ext), $allowed)) {
                $imageName = uniqid('cover_') . '.' . $ext;
                move_uploaded_file($_FILES['image']['tmp_name'], $uploadDirImg . $imageName);
            }
        }

        if ($error) {
            include __DIR__ . "/../Views/music/uploadForm.php";
            return;
        }

        // Save to DB
        $this->musicModel->create([
            'user_id' => $_SESSION['user_id'],
            'title' => $title,
            'description' => $description,
            'filename' => $mp3Name,
            'image' => $imageName
        ]);

        header("Location: " . BASE_URL . "/m/new/success");
        exit;
    }

    public function success() {
        include __DIR__ . "/../Views/music/uploadSuccess.php";
    }
}
