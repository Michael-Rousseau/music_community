<?php
session_start();
require_once '../config/db.php';

// 1. --- SECURITY CHECK ---
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// 2. --- ACTIONS ---
if (isset($_GET['del_user'])) {
    $uid = (int)$_GET['del_user'];
    if ($uid !== $_SESSION['user_id']) {
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$uid]);
    }
    header("Location: admin.php"); exit;
}

if (isset($_GET['del_music'])) {
    $mid = (int)$_GET['del_music'];
    $stmt = $pdo->prepare("SELECT filename FROM musics WHERE id = ?");
    $stmt->execute([$mid]);
    $file = $stmt->fetchColumn();
    if ($file && file_exists("uploads/mp3/$file")) unlink("uploads/mp3/$file");
    
    $pdo->prepare("DELETE FROM musics WHERE id = ?")->execute([$mid]);
    header("Location: admin.php"); exit;
}

// 3. --- DATA FETCHING ---
$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
$musics = $pdo->query("SELECT m.*, u.username FROM musics m JOIN users u ON m.user_id = u.id ORDER BY m.created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Administration - Tempo</title>
    <link rel="stylesheet" href="assets/css/tempo.css">
</head>
<body>

<header>
    <a href="index.php" class="logo"><img src="assets/images/logo_tempo.png" alt="Tempo"></a>
    <div style="display:flex; align-items:center;">
        <button id="themeToggle" class="theme-toggle"><i class="fas fa-moon"></i></button>
        <nav>
            <a href="index.php" class="btn btn-secondary">Retour au site</a>
            <a href="logout.php" class="btn btn-primary" style="background:#ff6b6b; color:white;">Déconnexion</a>
        </nav>
    </div>
</header>

<div class="hero" style="padding-bottom: 20px;">
    <h1>Panneau d'Administration</h1>
    <p>Gérez les utilisateurs et le contenu de la plateforme.</p>
    
    <div style="display: flex; gap: 20px; justify-content: center; margin-top: 20px;">
        <div class="stat-card">
            <i class="fas fa-users"></i> <?php echo count($users); ?> Utilisateurs
        </div>
        <div class="stat-card secondary">
            <i class="fas fa-music"></i> <?php echo count($musics); ?> Musiques
        </div>
    </div>
</div>

<div class="admin-container">
    
    <div class="card" style="max-width: 100%;">
        <h2 style="margin: 0 0 20px 0; display:flex; align-items:center; gap:10px;">
            <i class="fas fa-user-shield" style="color:var(--primary);"></i> Utilisateurs
        </h2>
        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr><th>Utilisateur</th><th>Email</th><th>Rôle</th><th style="text-align:right;">Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach($users as $u): ?>
                    <tr>
                        <td>
                            <div style="display:flex; align-items:center;">
                                <div class="user-avatar"><?php echo strtoupper(substr($u['username'], 0, 1)); ?></div>
                                <strong><?php echo htmlspecialchars($u['username']); ?></strong>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                        <td>
                            <?php if($u['role'] === 'admin'): ?>
                                <span class="badge-admin">ADMIN</span>
                            <?php else: ?>
                                <span style="color:var(--text-muted); font-size:0.9rem;">Membre</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align:right;">
                            <?php if($u['role'] !== 'admin'): ?>
                                <a href="admin.php?del_user=<?php echo $u['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bannir ?');"><i class="fas fa-ban"></i></a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card" style="max-width: 100%;">
        <h2 style="margin: 0 0 20px 0; display:flex; align-items:center; gap:10px;">
            <i class="fas fa-compact-disc" style="color:var(--primary);"></i> Musiques
        </h2>
        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr><th>Titre</th><th>Auteur</th><th>Date</th><th style="text-align:right;">Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach($musics as $m): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($m['title']); ?></strong></td>
                        <td><?php echo htmlspecialchars($m['username']); ?></td>
                        <td style="color:var(--text-muted);"><?php echo date('d/m/Y', strtotime($m['created_at'])); ?></td>
                        <td style="text-align:right;">
                            <a href="music.php?id=<?php echo $m['id']; ?>" target="_blank" class="btn btn-secondary btn-sm"><i class="fas fa-external-link-alt"></i></a>
                            <a href="admin.php?del_music=<?php echo $m['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer ?');"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="assets/js/tempo.js"></script>
</body>
</html>
