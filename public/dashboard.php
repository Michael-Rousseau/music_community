<?php
// 1. --- SÉCURITÉ & SESSION ---
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit();
}

require_once '../config/db.php';

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
                $stmt = $pdo->prepare($sql);
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
    $stmt = $pdo->prepare("DELETE FROM musics WHERE id = ? AND user_id = ?");
    $stmt->execute([$delete_id, $_SESSION['user_id']]);
    header("Location: dashboard.php"); // On recharge pour nettoyer l'URL
    exit();
}


// Récupérer les musiques de l'utilisateur
$stmt = $pdo->prepare("SELECT * FROM musics WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$my_musics = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - MusicShare</title>
    <style>

        body { font-family: 'Segoe UI', sans-serif; background-color: #f4f4f9; margin: 0; padding: 0; }
        
        nav { background: #333; color: white; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; }
        nav a { color: white; text-decoration: none; margin-left: 15px; font-weight: bold; }
        nav a:hover { color: #ddd; }
        
        .container { max-width: 1200px; margin: 2rem auto; padding: 0 1rem; display: grid; grid-template-columns: 1fr 2fr; gap: 2rem; }
        
        .card { background: white; padding: 1.5rem; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 2rem; }
        h2 { margin-top: 0; color: #333; border-bottom: 2px solid #f4f4f9; padding-bottom: 10px; }

        .profile-header { text-align: center; margin-bottom: 20px; }
        .avatar-circle { width: 80px; height: 80px; background-color: #007bff; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; margin: 0 auto 10px; font-weight: bold; }
        
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: 600; }
        input[type="text"], textarea, select, input[type="file"] { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button.btn-upload { width: 100%; background-color: #28a745; color: white; padding: 10px; border: none; border-radius: 5px; cursor: pointer; font-size: 1rem; }
        button.btn-upload:hover { background-color: #218838; }

        .music-item { display: flex; justify-content: space-between; align-items: center; background: #f9f9f9; padding: 10px; border-radius: 6px; margin-bottom: 10px; border-left: 4px solid #007bff; }
        .music-info h3 { margin: 0; font-size: 1.1rem; }
        .music-info span { font-size: 0.85rem; color: #666; }
        .actions a { text-decoration: none; margin-left: 10px; font-size: 1.2rem; }
        .btn-play { color: #007bff; cursor: pointer; }
        .btn-edit { color: #ffc107; }
        .btn-delete { color: #dc3545; }

        /* Alertes */
        .alert { padding: 10px; margin-bottom: 15px; border-radius: 5px; text-align: center; }
        .alert.success { background: #d4edda; color: #155724; }
        .alert.error { background: #f8d7da; color: #721c24; }

        @media (max-width: 768px) { .container { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

    <nav>
        <div class="logo">🎵 MusicShare</div>
        <div class="links">
            <a href="index.php">Accueil</a>
            <a href="dashboard.php">Mon Espace</a>
            <a href="logout.php" style="color: #ff6b6b;">Déconnexion</a>
        </div>
    </nav>

    <div class="container">
        
        <div class="left-column">
            
            <div class="card">
                <h2>Mon Profil</h2>
                <div class="profile-header">
                    <div class="avatar-circle">
                        <?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?>
                    </div>
                    <h3><?php echo htmlspecialchars($_SESSION['user_name']); ?></h3>
                    <p style="color:#666;"><?php echo isset($_SESSION['user_role']) ? ucfirst($_SESSION['user_role']) : 'Membre'; ?></p>
                </div>
            </div>

            <div class="card">
                <h2>Ajouter une musique</h2>
                
                <?php if (!empty($message)): ?>
                    <div class="alert <?php echo $message_type; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <form action="dashboard.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="upload">
                    
                    <div class="form-group">
                        <label>Titre du morceau</label>
                        <input type="text" name="title" required placeholder="Ex: Mon super remix">
                    </div>

                    <div class="form-group">
                        <label>Fichier MP3</label>
                        <input type="file" name="music_file" accept=".mp3,audio/mpeg" required>
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" rows="3" placeholder="Racontez l'histoire de ce titre..."></textarea>
                    </div>

                    <div class="form-group">
                        <label>Visibilité</label>
                        <select name="visibility">
                            <option value="public">Public (Tout le monde)</option>
                            <option value="private">Privé (Moi seul)</option>
                        </select>
                    </div>

                    <button type="submit" class="btn-upload">Uploader 🚀</button>
                </form>
            </div>
        </div>

        <div class="right-column">
            <div class="card">
                <h2>Mes Musiques (<?php echo count($my_musics); ?>)</h2>

                <?php if (count($my_musics) > 0): ?>
                    <div class="music-list">
                        <?php foreach ($my_musics as $music): ?>
                            <div class="music-item">
                                <div class="music-info">
                                    <h3><?php echo htmlspecialchars($music['title']); ?></h3>
                                    <span>
                                        <?php echo ($music['visibility'] == 'public') ? '🌍 Public' : '🔒 Privé'; ?> 
                                        • Ajouté le <?php echo date('d/m/Y', strtotime($music['created_at'])); ?>
                                    </span>
                                    <br>
                                    <audio controls style="margin-top:5px; height: 30px; width: 250px;">
                                        <source src="uploads/mp3/<?php echo htmlspecialchars($music['filename']); ?>" type="audio/mpeg">
                                        Votre navigateur ne supporte pas l'audio.
                                    </audio>
                                </div>
                                
                                <div class="actions">
                                    <a href="edit.php?id=<?php echo $music['id']; ?>" class="btn-edit" title="Modifier">✏️</a>
                                    
                                    <a href="dashboard.php?delete_id=<?php echo $music['id']; ?>" class="btn-delete" title="Supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce titre ?');">🗑️</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p style="text-align:center; color:#888;">Vous n'avez pas encore posté de musique. Utilisez le formulaire à gauche !</p>
                <?php endif; ?>
            </div>
        </div>

    </div>
</body>
</html>
