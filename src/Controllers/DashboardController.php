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

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST) && empty($_FILES) && $_SERVER['CONTENT_LENGTH'] > 0) {
            $message = "Le fichier est trop volumineux ! Il dépasse la limite du serveur (post_max_size).";
            $message_type = "error";
        }

        // --- Handle Avatar Upload ---
        elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload_avatar') {
             if (isset($_FILES['avatar_file']) && $_FILES['avatar_file']['error'] === 0) {
                $ext = strtolower(pathinfo($_FILES['avatar_file']['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];

                if (in_array($ext, $allowed)) {
                    $newFilename = 'avatar_' . $_SESSION['user_id'] . '_' . time() . '.' . $ext;
                    $target = __DIR__ . '/../../public/uploads/avatars/' . $newFilename;
                    if (!is_dir(dirname($target))) mkdir(dirname($target), 0755, true); // 0755 for own
                    if (move_uploaded_file($_FILES['avatar_file']['tmp_name'], $target)) {
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

        elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload') {
            
            if (isset($_FILES['music_file']) && $_FILES['music_file']['error'] === UPLOAD_ERR_OK) {
                
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $_FILES['music_file']['tmp_name']);
                finfo_close($finfo);

                $allowedMimeTypes = ['audio/mpeg', 'audio/mp3', 'audio/x-mp3'];

                if (in_array($mimeType, $allowedMimeTypes)) {
                    $title = trim($_POST['title']);
                    $desc = trim($_POST['description']);
                    $visibility = $_POST['visibility'];
                    $ext = 'mp3'; 
                    $newFilename = uniqid() . "." . $ext;
                    $target = __DIR__ . '/../../public/uploads/mp3/' . $newFilename;

                    if (!is_dir(dirname($target))) mkdir(dirname($target), 0755, true);

                    if (move_uploaded_file($_FILES['music_file']['tmp_name'], $target)) {
                        $musicModel->create($_SESSION['user_id'], $title, $desc, $newFilename, $visibility);
                        $message = "Musique ajoutée !";
                        $message_type = "success";
                    } else {
                        $message = "Erreur lors de l'enregistrement sur le disque.";
                        $message_type = "error";
                    }
                } else {
                    $message = "Format invalide. Type détecté : $mimeType";
                    $message_type = "error";
                }

            } else {
                $errorCode = $_FILES['music_file']['error'] ?? 4; 
                switch ($errorCode) {
                    case UPLOAD_ERR_INI_SIZE:   $message = "Fichier trop lourd (limite php.ini dépassée)."; break;
                    case UPLOAD_ERR_FORM_SIZE:  $message = "Fichier trop lourd (limite formulaire HTML)."; break;
                    case UPLOAD_ERR_PARTIAL:    $message = "Upload interrompu."; break;
                    case UPLOAD_ERR_NO_FILE:    $message = "Aucun fichier envoyé."; break;
                    default:                    $message = "Erreur inconnue (Code $errorCode).";
                }
                $message_type = "error";
            }
        }

        // Fetch Data & Render
        $myMusics = $musicModel->findAllByUser($_SESSION['user_id']);
        
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

    public function edit() {
        if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) $this->redirect('/dashboard');

        $pdo = Database::getConnection();
        $musicModel = new Music($pdo);
        $id = (int)$_GET['id'];

        // security check: does this music belong to the user?
        $music = $musicModel->findById($id);
        if (!$music || $music['user_id'] != $_SESSION['user_id']) {
            die("Accès interdit");
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = trim($_POST['title']);
            $desc = trim($_POST['description']);
            $viz = $_POST['visibility'];

            $musicModel->update($id, $_SESSION['user_id'], $title, $desc, $viz);
            $this->redirect('/dashboard');
        }

        $this->render('dashboard/edit', ['music' => $music]);
    }

    public function delete() {
        if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) $this->redirect('/dashboard');

        $pdo = Database::getConnection();
        $musicModel = new Music($pdo);

        $musicModel->delete((int)$_GET['id'], $_SESSION['user_id']);

        $this->redirect('/dashboard');
    }
}
