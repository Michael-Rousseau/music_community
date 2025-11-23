<?php
namespace Controllers;

use Models\Music;

class MusicController {

    private $pdo;
    private $musicModel;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->musicModel = new Music($pdo);
    }

    // Show a music page (later)
    public function show($id) {
        $music = $this->musicModel->find($id);
        if (!$music) {
            http_response_code(404);
            echo "Music not found.";
            return;
        }

        include __DIR__ . "/../Views/music/musicPage.php";
    }

    // Show upload form
    public function uploadForm() {
        include __DIR__ . "/../Views/music/uploadForm.php";
    }

    // Handle upload
    public function create() {

        // Must be logged in
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "/login");
            exit;
        }

        $user_id = $_SESSION['user_id'];

        // Basic validations
        if (empty($_POST['title']) || empty($_POST['description'])) {
            $error = "Veuillez fournir un titre et une description.";
            include __DIR__ . "/../Views/music/uploadForm.php";
            return;
        }

        // === MP3 validation ===
         if (!isset($_FILES['mp3'])) {
            $error = "Fichier MP3 manquant";
            include __DIR__ . "/../Views/music/uploadForm.php";
            return;
        }

        $err = $_FILES['mp3']['error'] ?? UPLOAD_ERR_NO_FILE;
        if ($err !== UPLOAD_ERR_OK) {
            // map error to message
            $map = [
                UPLOAD_ERR_INI_SIZE => "Le fichier dépasse la limite serveur (upload_max_filesize).",
                UPLOAD_ERR_FORM_SIZE => "Le fichier dépasse la limite du formulaire.",
                UPLOAD_ERR_PARTIAL => "Le fichier a été partiellement transféré.",
                UPLOAD_ERR_NO_FILE => "Aucun fichier envoyé.",
                UPLOAD_ERR_NO_TMP_DIR => "Pas de dossier temporaire sur le serveur.",
                UPLOAD_ERR_CANT_WRITE => "Impossible d'écrire le fichier sur le disque.",
                UPLOAD_ERR_EXTENSION => "Upload stoppé par une extension PHP."
            ];
            $error = "Fichier MP3 invalide. Code: $err. " . ($map[$err] ?? '');
            include __DIR__ . "/../Views/music/uploadForm.php";
            return;
        }


        $mp3_ext = strtolower(pathinfo($_FILES['mp3']['name'], PATHINFO_EXTENSION));

        if ($mp3_ext !== "mp3") {
            $error = "Le fichier doit être un MP3.";
            include __DIR__ . "/../Views/music/uploadForm.php";
            return;
        }

        if ($_FILES['mp3']['type'] !== "audio/mpeg") {
            $error = "Format MP3 invalide.";
            include __DIR__ . "/../Views/music/uploadForm.php";
            return;
        }

        $mp3_tmp = $_FILES['mp3']['tmp_name'];
        $mp3_name = time() . "_mp3_" . basename($_FILES['mp3']['name']);
        $mp3_path = __DIR__ . "/../../public/uploads/mp3/" . $mp3_name;


        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $error = "Vous devez fournir une image.";
            include __DIR__ . "/../Views/music/uploadForm.php";
            return;
        }

        // MP3 validation
        $mp3_tmp = $_FILES['mp3']['tmp_name'];
        $mp3_name = time() . "_mp3_" . basename($_FILES['mp3']['name']);
        $mp3_path = __DIR__ . "/../../public/uploads/mp3/" . $mp3_name;

        $info = pathinfo($mp3_name, PATHINFO_EXTENSION);
        if (strtolower($info) !== "mp3") {
            $error = "Le fichier doit être un MP3.";
            include __DIR__ . "/../Views/music/uploadForm.php";
            return;
        }

        // Image validation
        $img_tmp = $_FILES['image']['tmp_name'];
        $img_name = time() . "_img_" . basename($_FILES['image']['name']);
        $img_path = __DIR__ . "/../../public/uploads/images/" . $img_name;

        $allowed_images = ["jpg", "jpeg", "png", "webp"];
        $ext = strtolower(pathinfo($img_name, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed_images)) {
            $error = "L’image doit être JPG, JPEG, PNG ou WEBP.";
            include __DIR__ . "/../Views/music/uploadForm.php";
            return;
        }

        // Create folders if missing
        if (!file_exists(__DIR__ . "/../../public/uploads/mp3")) {
            mkdir(__DIR__ . "/../../public/uploads/mp3", 0777, true);
        }
        if (!file_exists(__DIR__ . "/../../public/uploads/images")) {
            mkdir(__DIR__ . "/../../public/uploads/images", 0777, true);
        }

        // Move files
        move_uploaded_file($mp3_tmp, $mp3_path);
        move_uploaded_file($img_tmp, $img_path);

        // Save music record
        $this->musicModel->create([
            "user_id" => $user_id,
            "title" => $_POST['title'],
            "description" => $_POST['description'],
            "filename" => $mp3_name,
            "image" => $img_name
        ]);

        header("Location: " . BASE_URL . "/m/new/success");
        exit;
    }

    public function success() {
        include __DIR__ . "/../Views/music/uploadSuccess.php";
    }
}
