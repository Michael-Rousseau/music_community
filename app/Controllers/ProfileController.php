<?php

namespace Controllers;

use Models\User;
use Core\Auth;
use PDOException;

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

        $userName = $_SESSION['username'];

        // 1. --- SÉCURITÉ & SESSION ---
        session_start();
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "login");
            exit();
        }


        $message = '';
        $message_type = '';

        // 2. --- TRAITEMENT DE L'UPLOAD (AJOUTER UNE MUSIQUE) ---
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload') {

            $title = trim($_POST['title']);
            $description = trim($_POST['description']);
            $visibility = $_POST['visibility'];

            // Gestion du fichier MP3
            if (isset($_FILES['music_file']) && $_FILES['music_file']['error'] === 0) {

                $allowed = ['mp3' => 'audio/mpeg'];
                $filename = $_FILES['music_file']['name'];
                $filetype = $_FILES['music_file']['type'];
                $filesize = $_FILES['music_file']['size'];
                $ext = pathinfo($filename, PATHINFO_EXTENSION);

                // Vérification de l'extension et du type MIME
                if (!array_key_exists($ext, $allowed)) die("Erreur : Veuillez sélectionner un format de fichier valide (MP3 uniquement).");

                // Limite de taille (ex: 10MB)
                $maxsize = 10 * 1024 * 1024;
                if ($filesize > $maxsize) die("Erreur : Le fichier est trop volumineux (Max 10Mo).");

                // Nommage unique pour éviter les écrasements
                $new_filename = uniqid() . "." . $ext;
                $upload_dir = "uploads/mp3/";

                // Création du dossier si inexistant
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

                if (move_uploaded_file($_FILES['music_file']['tmp_name'], $upload_dir . $new_filename)) {
                    // Insertion en BDD
                    try {
                        $sql = "INSERT INTO musics (user_id, title, description, filename, visibility) VALUES (?, ?, ?, ?, ?)";
                        $stmt = $this->pdo->prepare($sql);
                        $stmt->execute([$_SESSION['user_id'], $title, $description, $new_filename, $visibility]);

                        $message = "Musique ajoutée avec succès !";
                        $message_type = "success";
                    } catch (PDOException $e) {
                        $message = "Erreur BDD : " . $e->getMessage();
                        $message_type = "error";
                    }
                } else {
                    $message = "Erreur lors de l'upload du fichier.";
                    $message_type = "error";
                }
            } else {
                $message = "Veuillez choisir un fichier MP3.";
                $message_type = "error";
            }
        }

        // 3. --- TRAITEMENT DE LA SUPPRESSION ---
        if (isset($_GET['delete_id'])) {
            $delete_id = (int)$_GET['delete_id'];
            // Sécurité : On vérifie que la musique appartient bien à l'utilisateur connecté
            $stmt = $this->pdo->prepare("DELETE FROM musics WHERE id = ? AND user_id = ?");
            $stmt->execute([$delete_id, $_SESSION['user_id']]);
            header("Location: " . BASE_URL . "/profile");
            exit();
        }


        // Récupérer les musiques de l'utilisateur
        $stmt = $this->pdo->prepare("SELECT * FROM musics WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$_SESSION['user_id']]);
        $my_musics = $stmt->fetchAll();

        include __DIR__ . '/../Views/profile.php';
    }
}
