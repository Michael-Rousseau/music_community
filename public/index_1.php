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
    <title>Tempo - Accueil</title>
    <link rel="stylesheet" href="assets/css/tempo.css">
</head>
<body>

<header>
    <a href="index.php" class="logo"><img src="assets/images/logo_tempo.png" alt="Tempo"></a>
    <div style="display:flex; align-items:center;">
        <button id="themeToggle" class="theme-toggle" title="Changer de thème"><i class="fas fa-moon"></i></button>
        
        <nav>
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                    <a href="admin.php" class="btn btn-secondary" style="border-color:gold; color:#b58900; margin-right:5px;">Admin</a>
                <?php endif; ?>
                <a href="dashboard.php" class="btn btn-primary">Mon Espace</a>
                <a href="logout.php" class="btn btn-secondary">Déconnexion</a>
            <?php else: ?>
                <a href="connexion.php" class="btn btn-primary">Connexion</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<script src="assets/js/tempo.js"></script>
    <div class="hero">
        <h1>Accueil</h1>
        <form class="search-bar" method="GET" style="max-width: 400px; margin: 0 auto;">
            <input type="text" name="q" placeholder="Rechercher un titre..." value="<?php echo htmlspecialchars($search); ?>">
        </form>
    </div>

    <div class="grid">
        <?php foreach ($musics as $m): ?>
            <div class="card">
                <div class="card-img">
                    <?php if(!empty($m['image']) && $m['image'] !== 'default_image.png'): ?>
                         <img src="uploads/images/<?= htmlspecialchars($m['image']) ?>" style="width:100%; height:100%; object-fit:cover;">
                    <?php else: ?>
                        💿
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <h3 class="card-title"><?php echo htmlspecialchars($m['title']); ?></h3>
                    <div class="card-user">
                        <span>par <?php echo htmlspecialchars($m['username']); ?></span>
                    </div>
                    <a href="music.php?id=<?php echo $m['id']; ?>" class="btn btn-primary" style="display:block; text-align:center;">Écouter ▶</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

</body>
</html>
