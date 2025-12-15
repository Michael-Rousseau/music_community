<?php
namespace App\Controllers;

use App\Config\Database;
use App\Models\Music;

class DashboardController extends Controller {
    
    // 1. LIST & UPLOAD (Existing)
    public function index() {
        if (!isset($_SESSION['user_id'])) $this->redirect('/login');
        
        $pdo = Database::getConnection();
        $musicModel = new Music($pdo);
        $message = '';
        $message_type = '';

        // Handle Avatar Upload
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload_avatar') {
            if (isset($_FILES['avatar_file']) && $_FILES['avatar_file']['error'] === 0) {
                $ext = strtolower(pathinfo($_FILES['avatar_file']['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                
                if (in_array($ext, $allowed)) {
                    $newFilename = 'avatar_' . $_SESSION['user_id'] . '_' . time() . '.' . $ext;
                    $target = __DIR__ . '/../../public/uploads/avatars/' . $newFilename;
                    
                    if (!is_dir(dirname($target))) mkdir(dirname($target), 0777, true);

                    if (move_uploaded_file($_FILES['avatar_file']['tmp_name'], $target)) {
                        // Update database
                        $stmt = $pdo->prepare("UPDATE users SET avatar = ? WHERE id = ?");
                        $stmt->execute([$newFilename, $_SESSION['user_id']]);
                        
                        $message = "Photo de profil mise à jour !";
                        $message_type = "success";
                    } else {
                        $message = "Erreur lors de l'upload.";
                        $message_type = "error";
                    }
                } else {
                    $message = "Format invalide (JPG, PNG, GIF uniquement).";
                    $message_type = "error";
                }
            }
        }

        // Handle Music Upload
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload') {
            $title = trim($_POST['title']);
            $desc = trim($_POST['description']);
            $visibility = $_POST['visibility'];

            if (isset($_FILES['music_file']) && $_FILES['music_file']['error'] === 0) {
                $ext = pathinfo($_FILES['music_file']['name'], PATHINFO_EXTENSION);
                if ($ext === 'mp3') {
                    $newFilename = uniqid() . ".mp3";
                    // Correct path relative to src/Controllers/
                    $target = __DIR__ . '/../../public/uploads/mp3/' . $newFilename;
                    
                    if (!is_dir(dirname($target))) mkdir(dirname($target), 0777, true);

                    if (move_uploaded_file($_FILES['music_file']['tmp_name'], $target)) {
                        $musicModel->create($_SESSION['user_id'], $title, $desc, $newFilename, $visibility);
                        $message = "Musique ajoutée !";
                        $message_type = "success";
                    } else {
                        $message = "Erreur lors de l'upload.";
                        $message_type = "error";
                    }
                } else {
                    $message = "Format invalide (MP3 uniquement).";
                    $message_type = "error";
                }
            }
        }

        $myMusics = $musicModel->findAllByUser($_SESSION['user_id']);
        
        // Get user avatar
        $stmt = $pdo->prepare("SELECT avatar FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user_avatar = $stmt->fetchColumn();
        
        $this->render('dashboard/index', [
            'my_musics' => $myMusics,
            'message' => $message,
            'message_type' => $message_type,
            'user_avatar' => $user_avatar
        ]);
    }

    // 2. EDIT MUSIC
    public function edit() {
        if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) $this->redirect('/dashboard');
        
        $pdo = Database::getConnection();
        $musicModel = new Music($pdo);
        $id = (int)$_GET['id'];
        
        // Security check: Does this music belong to the user?
        $music = $musicModel->findById($id);
        if (!$music || $music['user_id'] != $_SESSION['user_id']) {
            die("Accès interdit");
        }

        // Handle Form Submit
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = trim($_POST['title']);
            $desc = trim($_POST['description']);
            $viz = $_POST['visibility'];
            
            $musicModel->update($id, $_SESSION['user_id'], $title, $desc, $viz);
            $this->redirect('/dashboard');
        }

        $this->render('dashboard/edit', ['music' => $music]);
    }

    // 3. DELETE MUSIC
    public function delete() {
        if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) $this->redirect('/dashboard');
        
        $pdo = Database::getConnection();
        $musicModel = new Music($pdo);
        
        $musicModel->delete((int)$_GET['id'], $_SESSION['user_id']);
        
        $this->redirect('/dashboard');
    }
}