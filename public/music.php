<?php
session_start();
require_once '../config/db.php';

if (!isset($_GET['id'])) die("ID manquant");
$music_id = (int)$_GET['id'];

$stmt = $pdo->prepare("SELECT m.*, u.username, u.avatar FROM musics m JOIN users u ON m.user_id = u.id WHERE m.id = ?");
$stmt->execute([$music_id]);
$music = $stmt->fetch();
if (!$music) die("Musique introuvable");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rating']) && isset($_SESSION['user_id'])) {
    $rating = (int)$_POST['rating'];
    $sqlRate = "INSERT INTO ratings (user_id, music_id, value) VALUES (?, ?, ?) 
                ON DUPLICATE KEY UPDATE value = ?";
    $stmtRate = $pdo->prepare($sqlRate);
    $stmtRate->execute([$_SESSION['user_id'], $music_id, $rating, $rating]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment']) && isset($_SESSION['user_id'])) {
    $content = trim($_POST['comment']);
    if (!empty($content)) {
        $sqlComm = "INSERT INTO comments (user_id, music_id, content) VALUES (?, ?, ?)";
        $stmtComm = $pdo->prepare($sqlComm);
        $stmtComm->execute([$_SESSION['user_id'], $music_id, $content]);
    }
}

$stmtAvg = $pdo->prepare("SELECT AVG(value) as moy FROM ratings WHERE music_id = ?");
$stmtAvg->execute([$music_id]);
$avgRating = round($stmtAvg->fetch()['moy'], 1);

$stmtC = $pdo->prepare("SELECT c.*, u.username FROM comments c JOIN users u ON c.user_id = u.id WHERE c.music_id = ? ORDER BY c.created_at DESC");
$stmtC->execute([$music_id]);
$comments = $stmtC->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($music['title']); ?></title>
    <style>
        /* Style simple inspiré du schéma */
        body { font-family: sans-serif; margin: 0; background: #f9f9f9; }
        .container { max-width: 800px; margin: 20px auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .header { text-align: center; }
        .cover { width: 100%; height: 200px; background: #333; color: white; display: flex; align-items: center; justify-content: center; font-size: 4rem; border-radius: 10px; }
        audio { width: 100%; margin: 20px 0; }
        .stars { color: #ffc107; font-size: 1.5rem; }
        .comment-box { margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px; }
        .comment { background: #f4f4f9; padding: 10px; margin-bottom: 10px; border-radius: 5px; }
        .btn { background: #007bff; color: white; border: none; padding: 10px; cursor: pointer; border-radius: 5px; }
    </style>
</head>
<body>

<div class="container">
    <a href="index.php">← Retour</a>
    
    <div class="header">
        <div class="cover">🎵</div>
        <h1><?php echo htmlspecialchars($music['title']); ?></h1>
        <p>Par <strong><?php echo htmlspecialchars($music['username']); ?></strong></p>
        
        <div class="rating">
            <span class="stars">★ <?php echo $avgRating ?: '-'; ?>/5</span>
            <?php if(isset($_SESSION['user_id'])): ?>
            <form method="POST" style="display:inline-block;">
                <select name="rating" onchange="this.form.submit()">
                    <option value="">Noter</option>
                    <option value="5">5 ★</option>
                    <option value="4">4 ★</option>
                    <option value="3">3 ★</option>
                    <option value="2">2 ★</option>
                    <option value="1">1 ★</option>
                </select>
            </form>
            <?php endif; ?>
        </div>
    </div>

    <audio controls>
        <source src="uploads/mp3/<?php echo htmlspecialchars($music['filename']); ?>" type="audio/mpeg">
        Votre navigateur ne supporte pas l'audio.
    </audio>

    <p><?php echo nl2br(htmlspecialchars($music['description'])); ?></p>

    <div class="comment-box">
        <h3>Commentaires (<?php echo count($comments); ?>)</h3>
        
        <?php if(isset($_SESSION['user_id'])): ?>
        <form method="POST" style="margin-bottom: 20px; display:flex; gap:10px;">
            <input type="text" name="comment" placeholder="Écrire un commentaire..." style="flex:1; padding:10px;" required>
            <button type="submit" class="btn">Envoyer</button>
        </form>
        <?php else: ?>
            <p><a href="connexion.php">Connectez-vous</a> pour commenter.</p>
        <?php endif; ?>

        <?php foreach($comments as $c): ?>
            <div class="comment">
                <strong><?php echo htmlspecialchars($c['username']); ?></strong> 
                <small style="color:#888;"><?php echo $c['created_at']; ?></small>
                <p style="margin:5px 0 0;"><?php echo htmlspecialchars($c['content']); ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</div>

</body>
</html>
