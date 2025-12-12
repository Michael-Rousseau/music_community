<?php
namespace Controllers;

use Models\Music;
use Core\Auth;

class MusicController {

    private $pdo;
    private $musicModel;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->musicModel = new Music($pdo);
    }

    // --- Player ---
    public function show($id) {
        $music = $this->musicModel->find($id);
        if (!$music) { http_response_code(404); echo "Music not found."; return; }
        $comments = $this->musicModel->getComments($id);
        $avgRating = $this->musicModel->getAvgRating($id);
        $openDrawer = (isset($_GET['drawer']) && $_GET['drawer'] === 'open') ? 'open' : '';
        include __DIR__ . "/../Views/music/musicPage.php";
    }

    public function postComment($id) {
        if (!Auth::check()) { header("Location: " . BASE_URL . "/login"); exit; }
        if (isset($_POST['comment'])) {
            $this->musicModel->addComment($_SESSION['user_id'], $id, trim($_POST['comment']), (int)($_POST['timestamp'] ?? 0));
        }
        header("Location: " . BASE_URL . "/m/" . $id . "?drawer=open");
        exit;
    }

    public function rate($id) {
        if (!Auth::check()) { header("Location: " . BASE_URL . "/login"); exit; }
        if (isset($_POST['rating'])) $this->musicModel->addRating($_SESSION['user_id'], $id, (int)$_POST['rating']);
        header("Location: " . BASE_URL . "/m/" . $id);
        exit;
    }

    // --- Upload ---
    public function uploadForm() {
        if (!Auth::check()) { header("Location: " . BASE_URL . "/login"); exit; }
        include __DIR__ . "/../Views/music/uploadForm.php";
    }

    public function create() {
        if (!Auth::check()) { header("Location: " . BASE_URL . "/login"); exit; }
        
        // (Copy your upload logic here from previous steps, abbreviated for brevity)
        // ... [Insert Upload Logic Here] ... 
        // Ensure you call $this->musicModel->create(...) and redirect to success.
    }
    
    public function success() { include __DIR__ . "/../Views/music/uploadSuccess.php"; }

    // --- Edit & Delete (NEW) ---
    public function editForm($id) {
        if (!Auth::check()) { header("Location: " . BASE_URL . "/login"); exit; }
        $music = $this->musicModel->find($id);
        
        // Security: Check if user owns music
        if (!$music || $music['user_id'] != $_SESSION['user_id']) {
            header("Location: " . BASE_URL . "/profile"); exit;
        }
        include __DIR__ . "/../Views/music/edit.php";
    }

    public function update($id) {
        if (!Auth::check()) { header("Location: " . BASE_URL . "/login"); exit; }
        $music = $this->musicModel->find($id);
        
        if ($music && $music['user_id'] == $_SESSION['user_id']) {
            $this->musicModel->update($id, $_POST['title'], $_POST['description'], $_POST['visibility']);
        }
        header("Location: " . BASE_URL . "/profile");
        exit;
    }

    public function delete($id) {
        if (!Auth::check()) { header("Location: " . BASE_URL . "/login"); exit; }
        $music = $this->musicModel->find($id);
        
        if ($music && $music['user_id'] == $_SESSION['user_id']) {
            $this->musicModel->delete($id);
        }
        header("Location: " . BASE_URL . "/profile");
        exit;
    }
}
