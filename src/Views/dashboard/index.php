
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Espace - Tempo</title>
    <link rel="stylesheet" href="assets/css/tempo.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* DASHBOARD STYLES (Updated for Dark Mode) */
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
            width: 100px; height: 100px;
            background-color: var(--primary);
            color: var(--dark);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 2.5rem; font-weight: 800;
            margin: 0 auto 15px;
        }

        .music-item {
            background: var(--bg-card); /* Adapted background */
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px var(--shadow); /* Adapted shadow */
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-left: 5px solid var(--primary);
            transition: transform 0.2s, background 0.3s;
        }

        .music-item:hover { transform: translateX(5px); }

        .music-info h3 {
            margin: 0 0 5px 0;
            font-size: 1.2rem;
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            color: var(--text-main); /* Adapted text color */
        }

        .music-meta {
            font-size: 0.85rem;
            color: var(--text-muted); /* Adapted muted text */
            margin-bottom: 10px;
            display: block;
        }

        .actions { display: flex; gap: 10px; }

        .btn-icon {
            background: none; border: none;
            cursor: pointer; font-size: 1.2rem;
            transition: 0.2s;
            color: var(--text-muted);
        }
        .btn-icon:hover { transform: scale(1.2); color: var(--primary); }
        
        .alert {
            padding: 15px; border-radius: 8px; margin-bottom: 20px;
            font-weight: bold; text-align: center;
        }
        .alert.success { background: #d4edda; color: #155724; }
        .alert.error { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>

<header>
    <a href="index.php" class="logo"><img src="assets/images/logo_tempo.png" alt="Tempo"></a>
    <div style="display:flex; align-items:center;">
        <button id="themeToggle" class="theme-toggle"><i class="fas fa-moon"></i></button>
        <nav>
            <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                <a href="admin.php" class="btn btn-secondary" style="border-color:gold; color:#b58900;">Admin</a>
            <?php endif; ?>
            <a href="index.php" class="btn btn-secondary">Accueil</a>
            <a href="logout.php" class="btn btn-primary" style="background:#ff6b6b; color:white;">Déconnexion</a>
        </nav>
    </div>
</header>
<script src="assets/js/tempo.js"></script>

    <div class="dashboard-container">
        
        <div class="left-column">
            
            <div class="card" style="text-align:center; margin-bottom: 30px; width: 100%; max-width: none;">
                <div class="card-body">
                    <div class="avatar-circle">
                        <?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?>
                    </div>
                    <h2 style="font-size: 1.5rem; margin-bottom:5px; color:var(--text-main);">
                        <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                    </h2>
                    <p style="color:var(--text-muted); margin:0;">
                        <?php echo isset($_SESSION['user_role']) ? ucfirst($_SESSION['user_role']) : 'Membre'; ?>
                    </p>
                </div>
            </div>

            <div class="card" style="width: 100%; max-width: none;">
                <div class="card-body">
                    <h2 style="font-size: 1.3rem; margin-bottom:20px; color:var(--text-main);">Ajouter une musique</h2>
                    
                    <?php if (!empty($message)): ?>
                        <div class="alert <?php echo $message_type; ?>">
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>

                    <form action="dashboard.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="upload">
                        
                        <label>Titre</label>
                        <input type="text" name="title" required placeholder="Ex: Mon super remix">

                        <label>Fichier MP3</label>
                        <input type="file" name="music_file" accept=".mp3,audio/mpeg" required>

                        <label>Description</label>
                        <textarea name="description" rows="3" placeholder="Racontez l'histoire..."></textarea>

                        <label>Visibilité</label>
                        <select name="visibility">
                            <option value="public">Public (Tout le monde)</option>
                            <option value="private">Privé (Moi seul)</option>
                        </select>

                        <button type="submit" class="btn btn-primary" style="width:100%; border-radius:50px;">Uploader 🚀</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="right-column">
            <h2 style="margin-bottom: 20px; color:var(--text-main);">Mes Musiques (<?php echo count($my_musics); ?>)</h2>

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
                                <audio controls style="margin-top:10px; height: 30px; width: 100%; max-width: 300px;">
                                    <source src="uploads/mp3/<?php echo htmlspecialchars($music['filename']); ?>" type="audio/mpeg">
                                    Votre navigateur ne supporte pas l'audio.
                                </audio>
                            </div>
                            
                            <div class="actions">
                                <a href="edit.php?id=<?php echo $music['id']; ?>" class="btn-icon" title="Modifier">
                                    <i class="fas fa-pen"></i>
                                </a>
                                <a href="dashboard.php?delete_id=<?php echo $music['id']; ?>" class="btn-icon" style="color:#dc3545;" title="Supprimer" onclick="return confirm('Êtes-vous sûr ?');">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="card" style="text-align:center; padding: 40px; width: 100%; max-width: none;">
                    <p style="color:var(--text-muted); font-size:1.2rem;">Vous n'avez pas encore posté de musique.</p>
                </div>
            <?php endif; ?>
        </div>

    </div>
</body>
</html>
