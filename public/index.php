<?php
session_start();
require_once '../config/db.php';

$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$sql = "SELECT m.*, u.username, u.avatar FROM musics m 
        JOIN users u ON m.user_id = u.id 
        WHERE m.visibility = 'public'";

if ($search) {
    $sql .= " AND m.title LIKE :search";
}
$sql .= " ORDER BY m.created_at DESC";

$stmt = $pdo->prepare($sql);
if ($search) $stmt->bindValue(':search', "%$search%");
$stmt->execute();
$musics = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>MusicShare - Accueil</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f4f9; margin: 0; }
        nav { background: #333; padding: 1rem; display: flex; justify-content: space-between; align-items: center; color: white; }
        nav a { color: white; text-decoration: none; margin-left: 15px; font-weight: bold; }
        .hero { text-align: center; padding: 40px 20px; background: white; margin-bottom: 20px; }
        .search-bar input { padding: 10px; width: 300px; border: 1px solid #ddd; border-radius: 20px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; max-width: 1200px; margin: 0 auto; padding: 20px; }
        .card { background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.1); transition: transform 0.2s; }
        .card:hover { transform: translateY(-5px); }
        .card-img { height: 150px; background: #eee; display: flex; align-items: center; justify-content: center; color: #aaa; font-size: 3rem; }
        .card-body { padding: 15px; }
        .card-title { margin: 0 0 10px; font-size: 1.1rem; color: #333; }
        .card-user { font-size: 0.9rem; color: #666; display: flex; align-items: center; gap: 5px; }
        .btn-play { display: block; text-align: center; background: #007bff; color: white; padding: 8px; border-radius: 5px; text-decoration: none; margin-top: 10px; }
    </style>
</head>
<body>

<nav>
    <div style="font-size: 1.5rem;">🎵 MusicShare</div>
    <div>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="dashboard.php">Mon Espace</a>
            <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                <a href="admin.php" style="color:#ffc107;">Admin</a>
            <?php endif; ?>
            <a href="logout.php" style="color:#ff6b6b;">Déconnexion</a>
        <?php else: ?>
            <a href="connexion.php">Se connecter</a>
            <a href="inscription.php" style="background: #007bff; padding: 8px 15px; border-radius: 20px;">S'inscrire</a>
        <?php endif; ?>
    </div>
</nav>

<div class="hero">
    <h1>Découvrez les talents de demain</h1>
    <form class="search-bar" method="GET">
        <input type="text" name="q" placeholder="Rechercher un titre..." value="<?php echo htmlspecialchars($search); ?>">
    </form>
</div>

<div class="grid">
    <?php foreach ($musics as $m): ?>
        <div class="card">
            <div class="card-img">💿</div>
            <div class="card-body">
                <h3 class="card-title"><?php echo htmlspecialchars($m['title']); ?></h3>
                <div class="card-user">
                    <div style="width:20px;height:20px;background:#ccc;border-radius:50%;"></div>
                    <?php echo htmlspecialchars($m['username']); ?>
                </div>
                <a href="music.php?id=<?php echo $m['id']; ?>" class="btn-play">Écouter ▶</a>
            </div>
        </div>
    <?php endforeach; ?>
</div>

</body>
</html>
