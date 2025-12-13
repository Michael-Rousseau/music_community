<?php
session_start();
require_once '../config/db.php';

if (!isset($_GET['id'])) die("ID manquant");
$music_id = (int)$_GET['id'];

// --- 1. POST : AJOUTER COMMENTAIRE TEMPOREL ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment']) && isset($_SESSION['user_id'])) {
    $content = trim($_POST['comment']);
    $timestamp = isset($_POST['timestamp']) ? (int)$_POST['timestamp'] : 0;
    
    if (!empty($content)) {
        $stmt = $pdo->prepare("INSERT INTO comments (user_id, music_id, content, timestamp) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $music_id, $content, $timestamp]);
        header("Location: music.php?id=" . $music_id . "&drawer=open"); 
        exit();
    }
}

// --- 2. POST : NOTER ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rating']) && isset($_SESSION['user_id'])) {
    $val = (int)$_POST['rating'];
    if ($val >= 1 && $val <= 5) {
        $stmt = $pdo->prepare("INSERT INTO ratings (user_id, music_id, value) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE value = ?");
        $stmt->execute([$_SESSION['user_id'], $music_id, $val, $val]);
        header("Location: music.php?id=" . $music_id); 
        exit();
    }
}

// --- 3. DATA FETCHING ---
$stmt = $pdo->prepare("SELECT m.*, u.username FROM musics m JOIN users u ON m.user_id = u.id WHERE m.id = ?");
$stmt->execute([$music_id]);
$music = $stmt->fetch();
if (!$music) die("Musique introuvable");

$stmtAvg = $pdo->prepare("SELECT AVG(value) as moy FROM ratings WHERE music_id = ?");
$stmtAvg->execute([$music_id]);
$avgRating = round($stmtAvg->fetch()['moy'], 1);

$stmtC = $pdo->prepare("SELECT c.*, u.username FROM comments c JOIN users u ON c.user_id = u.id WHERE c.music_id = ? ORDER BY c.timestamp ASC");
$stmtC->execute([$music_id]);
$comments = $stmtC->fetchAll();

$openDrawer = (isset($_GET['drawer']) && $_GET['drawer'] === 'open') ? 'open' : '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($music['title']); ?> - Tempo</title>
    
    <script type="importmap">
    {
      "imports": {
        "three": "https://unpkg.com/three@0.158.0/build/three.module.js",
        "three/addons/": "https://unpkg.com/three@0.158.0/examples/jsm/"
      }
    }
    </script>
    
    <link rel="stylesheet" href="assets/css/tempo.css">
</head>
<body>

    <div id="canvas-container"></div>

    <div class="hud-layer">
        
        <div class="top-bar" style="display:flex; justify-content:space-between; align-items:center;">
             <div>
                 <a href="index.php" class="btn btn-secondary" style="border-radius:50px; padding:8px 20px;">
                    <i class="fas fa-arrow-left"></i> Retour
                 </a>
             </div>
             
             <div style="text-align:right;">
                <button id="themeToggle" class="theme-toggle" title="Changer de thème">
                    <i class="fas fa-moon"></i>
                </button>
             </div>
        </div>

        <div style="position: absolute; top: 20%; right: 5%; text-align: right;">
            <h1 class="song-title"><?php echo htmlspecialchars($music['title']); ?></h1>
            <div style="color:var(--primary); letter-spacing:2px; font-weight:bold; font-size:1.2rem;">
                <?php echo strtoupper(htmlspecialchars($music['username'])); ?>
            </div>
            
            <div class="rating-stars" id="starContainer" style="margin-top:10px;">
                <?php for($i=1; $i<=5; $i++): ?>
                    <span class="star <?php echo ($i <= round($avgRating)) ? 'filled' : ''; ?>" onclick="submitRating(<?php echo $i; ?>)">★</span>
                <?php endfor; ?>
            </div>
        </div>

        <button class="btn btn-secondary" onclick="toggleDrawer(true)" style="position:absolute; bottom:120px; right:40px;">
            <i class="fas fa-comment-alt"></i> Commentaires (<?php echo count($comments); ?>)
        </button>

        <div class="bottom-bar">
            <button id="playBtn" class="play-btn"><i class="fas fa-play"></i></button>
            
            <div class="progress-wrapper" id="progressContainer">
                <div class="progress-fill" id="progressBar"></div>
            </div>
            
            <div class="time" style="font-family:monospace; font-weight:bold; color:var(--text-main);">
                <span id="currTime">00:00</span>
            </div>
        </div>
    </div>

    <div class="drawer-overlay" id="overlay" onclick="toggleDrawer(false)" 
         style="position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); opacity:0; pointer-events:none; transition:0.3s; z-index:90;">
    </div>
    
    <div class="comments-drawer <?php echo $openDrawer; ?>" id="drawer">
        <div class="drawer-header" style="padding:20px; border-bottom:1px solid var(--border-color); display:flex; justify-content:space-between;">
            <span style="font-weight:800;">TIMELINE</span>
            <i class="fas fa-times" style="cursor:pointer;" onclick="toggleDrawer(false)"></i>
        </div>
        
        <div class="comments-list" id="commentsList" style="flex:1; overflow-y:auto; padding:20px;">
            <?php foreach($comments as $c): ?>
                <div class="comment-item" onclick="jumpTo(<?php echo $c['timestamp']; ?>)" style="cursor:pointer;">
                    <div>
                        <span class="comment-time">[<?php echo gmdate("i:s", $c['timestamp']); ?>]</span>
                        <span style="font-weight:bold; color:var(--text-main);"><?php echo htmlspecialchars($c['username']); ?></span>
                    </div>
                    <div style="color:var(--text-muted); margin-top:3px;"><?php echo htmlspecialchars($c['content']); ?></div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="drawer-footer" style="padding:20px; border-top:1px solid var(--border-color);">
            <?php if(isset($_SESSION['user_id'])): ?>
            <form method="POST" style="display:flex; gap:10px;">
                <input type="hidden" name="timestamp" id="timestampInput" value="0">
                <input type="text" name="comment" placeholder="Un avis ?" required autocomplete="off" style="margin:0;">
                <button type="submit" class="btn btn-primary" style="padding:10px 15px;">OK</button>
            </form>
            <?php else: ?>
                <a href="connexion.php" style="color:var(--primary); font-weight:bold;">Connectez-vous pour commenter</a>
            <?php endif; ?>
        </div>
    </div>

    <form id="ratingForm" method="POST" style="display:none;">
        <input type="hidden" name="rating" id="ratingInput">
    </form>
    
    <audio id="audio" src="uploads/mp3/<?php echo htmlspecialchars($music['filename']); ?>" crossorigin="anonymous"></audio>

    <script>
        const commentsData = <?php echo json_encode($comments); ?>;
        // FIX: Assign to window so module can see it
        window.isUserLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
    </script>

    <script src="assets/js/tempo.js"></script>
    <script type="module" src="assets/player.js"></script>

</body>
</html>
