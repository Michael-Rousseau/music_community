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
    <title>Mon Espace - Tempo</title>
    <link rel="stylesheet" href="assets/css/tempo.css">
    <style>
        /* Specific Dashboard Styles */
        .dashboard-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 30px;
        }

        @media (max-width: 768px) {
            .dashboard-container { grid-template-columns: 1fr; }
        }

        .avatar-circle {
            width: 100px;
            height: 100px;
            background-color: var(--primary);
            color: var(--dark);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            font-weight: 800;
            margin: 0 auto 15px;
        }

        .music-item {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-left: 5px solid var(--primary);
            transition: transform 0.2s;
        }

        .music-item:hover {
            transform: translateX(5px);
        }

        .music-info h3 {
            margin: 0 0 5px 0;
            font-size: 1.2rem;
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
        }

        .music-meta {
            font-size: 0.85rem;
            color: #888;
            margin-bottom: 10px;
            display: block;
        }

        .actions {
            display: flex;
            gap: 10px;
        }

        .btn-icon {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.2rem;
            transition: 0.2s;
        }
        .btn-icon:hover { transform: scale(1.2); }
        
        /* Fix missing styles from generic tempo.css */
        textarea, select, input[type="file"] {
            width: 100%;
            padding: 15px;
            background-color: var(--input-bg);
            border: none;
            border-radius: 8px;
            font-family: 'Montserrat', sans-serif;
            font-size: 1rem;
            box-sizing: border-box;
            margin-bottom: 15px;
        }
        
        label {
            font-weight: 600;
            margin-bottom: 8px;
            display: block;
            color: var(--dark);
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: bold;
            text-align: center;
        }
        .alert.success { background: #d4edda; color: #155724; }
        .alert.error { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>

<header>
        <a href="index.php" class="logo">
            <img src="assets/images/logo_tempo.png" alt="Tempo">
        </a>
        <nav>
            <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                <a href="admin.php" class="btn btn-secondary" style="border-color:gold; color:#b58900; margin-right:5px;">Admin</a>
            <?php endif; ?>
            <a href="index.php" class="btn btn-secondary">Accueil</a>
            <a href="logout.php" class="btn btn-primary" style="background-color: #ff6b6b; color: white;">Déconnexion</a>
        </nav>
    </header>

    <div class="dashboard-container">
        
        <div class="left-column">
            
            <div class="card" style="text-align:center; margin-bottom: 30px;">
                <div class="card-body">
                    <div class="avatar-circle">
                        <?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?>
                    </div>
                    <h2 style="font-size: 1.5rem; margin-bottom:5px;"><?php echo htmlspecialchars($_SESSION['user_name']); ?></h2>
                    <p style="color:#666; margin:0;"><?php echo isset($_SESSION['user_role']) ? ucfirst($_SESSION['user_role']) : 'Membre'; ?></p>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h2 style="font-size: 1.3rem; margin-bottom:20px;">Ajouter une musique</h2>
                    
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
        </div>

        <div class="right-column">
            <h2 style="margin-bottom: 20px;">Mes Musiques (<?php echo count($my_musics); ?>)</h2>

            <?php if (count($my_musics) > 0): ?>
                <div class="music-list">
                    <?php foreach ($my_musics as $music): ?>
                        <div class="music-item">
                            <div class="music-info" style="flex: 1;">
                                <h3><?php echo htmlspecialchars($music['title']); ?></h3>
                                <span class="music-meta">
                                    <?php echo ($music['visibility'] == 'public') ? '🌍 Public' : '🔒 Privé'; ?> 
                                    • Ajouté le <?php echo date('d/m/Y', strtotime($music['created_at'])); ?>
                                </span>
                                <br>
                                <audio controls style="margin-top:10px; height: 30px; width: 100%; max-width: 300px;">
                                    <source src="uploads/mp3/<?php echo htmlspecialchars($music['filename']); ?>" type="audio/mpeg">
                                    Votre navigateur ne supporte pas l'audio.
                                </audio>
                            </div>
                            
                            <div class="actions">
                                <a href="edit.php?id=<?php echo $music['id']; ?>" class="btn-icon" title="Modifier">✏️</a>
                                
                                <a href="dashboard.php?delete_id=<?php echo $music['id']; ?>" class="btn-icon" style="color:#dc3545;" title="Supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce titre ?');">🗑️</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="card" style="text-align:center; padding: 40px;">
                    <p style="color:#888; font-size:1.2rem;">Vous n'avez pas encore posté de musique.</p>
                    <p>Utilisez le formulaire à gauche pour commencer !</p>
                </div>
            <?php endif; ?>
        </div>

    </div>
</body>
</html>
