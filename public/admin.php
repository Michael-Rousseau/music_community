<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    die("Accès interdit (Réservé aux administrateurs).");
}

if (isset($_GET['del_user'])) {
    $uid = (int)$_GET['del_user'];
    $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$uid]);
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

$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
$musics = $pdo->query("SELECT m.*, u.username FROM musics m JOIN users u ON m.user_id = u.id ORDER BY m.created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Administration</title>
    <style>
        body { font-family: sans-serif; padding: 20px; background: #eee; }
        .container { display: flex; gap: 20px; max-width: 1200px; margin: 0 auto; }
        .panel { flex: 1; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        h2 { margin-top: 0; border-bottom: 2px solid #333; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { text-align: left; padding: 8px; border-bottom: 1px solid #ddd; font-size: 0.9rem; }
        .btn-del { background: #dc3545; color: white; padding: 4px 8px; text-decoration: none; border-radius: 3px; font-size: 0.8rem; }
        nav { margin-bottom: 20px; }
    </style>
</head>
<body>
    <nav>
        <a href="index.php">← Retour au site</a>
    </nav>
    <h1>Panneau d'Administration</h1>
    
    <div class="container">
        <div class="panel">
            <h2>Utilisateurs</h2>
            <table>
                <tr><th>ID</th><th>Nom</th><th>Email</th><th>Action</th></tr>
                <?php foreach($users as $u): ?>
                <tr>
                    <td><?php echo $u['id']; ?></td>
                    <td><?php echo htmlspecialchars($u['username']); ?></td>
                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                    <td>
                        <?php if($u['role'] !== 'admin'): ?>
                        <a href="admin.php?del_user=<?php echo $u['id']; ?>" class="btn-del" onclick="return confirm('Bannir cet utilisateur ?');">Bannir</a>
                        <?php else: ?>
                        <span style="color:gold;">Admin</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <div class="panel">
            <h2>Musiques</h2>
            <table>
                <tr><th>Titre</th><th>Auteur</th><th>Action</th></tr>
                <?php foreach($musics as $m): ?>
                <tr>
                    <td><?php echo htmlspecialchars($m['title']); ?></td>
                    <td><?php echo htmlspecialchars($m['username']); ?></td>
                    <td>
                        <a href="admin.php?del_music=<?php echo $m['id']; ?>" class="btn-del" onclick="return confirm('Supprimer cette musique ?');">Supprimer</a>
                        <a href="music.php?id=<?php echo $m['id']; ?>" target="_blank">Voir</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</body>
</html>
