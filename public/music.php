<?php
session_start();
require_once '../config/db.php';

if (!isset($_GET['id'])) die("ID manquant");
$music_id = (int)$_GET['id'];

// --- 1. POST : AJOUTER COMMENTAIRE TEMPOREL ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment']) && isset($_SESSION['user_id'])) {
    $content = trim($_POST['comment']);
    // On récupère le timestamp envoyé par le JS (ou 0 par défaut)
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

// On récupère les commentaires triés par timestamp pour l'affichage chronologique
$stmtC = $pdo->prepare("SELECT c.*, u.username FROM comments c JOIN users u ON c.user_id = u.id WHERE c.music_id = ? ORDER BY c.timestamp ASC");
$stmtC->execute([$music_id]);
$comments = $stmtC->fetchAll();

$openDrawer = (isset($_GET['drawer']) && $_GET['drawer'] === 'open') ? 'open' : '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($music['title']); ?></title>
    <script type="importmap">
    {
      "imports": {
        "three": "https://unpkg.com/three@0.158.0/build/three.module.js",
        "three/addons/": "https://unpkg.com/three@0.158.0/examples/jsm/"
      }
    }
    </script>
    <link rel="stylesheet" href="assets/style.css"> <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;900&family=Rajdhani:wght@300;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* CSS Spécifique au lecteur immersif */
        body { overflow: hidden; }
        
        #canvas-container { position: fixed; top: 0; left: 0; width: 100%; height: 100vh; z-index: 1; }
        
        .hud-layer {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 10;
            pointer-events: none; display: flex; flex-direction: column; justify-content: space-between;
            padding: 40px; box-sizing: border-box;
            background: radial-gradient(circle at center, transparent 0%, rgba(5,5,10,0.8) 100%);
        }

        /* Top Bar */
        .top-bar { pointer-events: auto; display: flex; justify-content: space-between; align-items: flex-start; }
        .back-btn { color: white; opacity: 0.7; transition: 0.3s; display: flex; align-items: center; gap: 10px; }
        .back-btn:hover { opacity: 1; color: var(--secondary); text-shadow: 0 0 10px var(--secondary); }
        .song-title { font-family: 'Orbitron'; font-size: 3rem; margin: 0; line-height: 1; text-transform: uppercase; text-shadow: 0 0 20px rgba(189, 0, 255, 0.6); text-align: right; }
        .rating-stars { margin-top: 10px; font-size: 1.5rem; cursor: pointer; display: inline-block; float: right; }
        .star.filled { color: #ffd700; text-shadow: 0 0 10px #ffd700; }

        /* Comment Pop-up (Le message qui apparait au bon moment) */
        .comment-popup {
            position: absolute; bottom: 140px; left: 40px;
            text-align: left; opacity: 0; transition: opacity 0.3s, transform 0.3s;
            pointer-events: none; max-width: 500px; transform: translateY(10px);
        }
        .comment-popup.active { opacity: 1; transform: translateY(0); }
        .popup-user { color: var(--secondary); font-family: 'Orbitron'; font-size: 1.2rem; display: block; margin-bottom: 5px; text-shadow: 0 0 10px var(--secondary); }
        .popup-content { font-size: 1.5rem; background: rgba(0,0,0,0.6); padding: 10px 20px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.1); backdrop-filter: blur(5px); }

        /* Player Controls */
        .bottom-bar {
            pointer-events: auto; width: 100%; max-width: 800px; margin: 0 auto;
            background: rgba(20, 20, 30, 0.4); backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.1); border-radius: 20px; padding: 20px 30px;
            display: flex; align-items: center; gap: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }
        .play-btn { background: var(--primary); border: none; width: 60px; height: 60px; border-radius: 50%; color: white; font-size: 1.5rem; cursor: pointer; box-shadow: 0 0 20px var(--primary); display: flex; align-items: center; justify-content: center; transition: 0.2s; }
        .play-btn:hover { transform: scale(1.1); background: #d94dff; }
        
        /* Timeline avec marqueurs */
        .progress-wrapper { flex-grow: 1; position: relative; height: 6px; background: rgba(255,255,255,0.1); cursor: pointer; border-radius: 3px; }
        .progress-fill { height: 100%; background: var(--secondary); width: 0%; box-shadow: 0 0 10px var(--secondary); border-radius: 3px; position: relative; z-index: 2; }
        
        /* Les petits points (marqueurs) sur la timeline */
        .comment-marker {
            position: absolute; top: -3px; width: 4px; height: 12px; background: white;
            z-index: 3; border-radius: 2px; opacity: 0.7; transition: 0.2s; pointer-events: none;
        }
        .comment-marker.active { background: var(--primary); height: 16px; top: -5px; box-shadow: 0 0 10px var(--primary); opacity: 1; }

        /* Drawer */
        .comments-trigger { position: absolute; right: 40px; bottom: 120px; pointer-events: auto; color: rgba(255,255,255,0.7); cursor: pointer; padding: 10px 20px; border: 1px solid rgba(255,255,255,0.2); border-radius: 30px; background: rgba(0,0,0,0.3); transition: 0.3s; }
        .comments-trigger:hover { border-color: var(--secondary); background: rgba(0, 243, 255, 0.1); color: white; }

        .drawer-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 90; background: rgba(0,0,0,0.5); opacity: 0; pointer-events: none; transition: 0.3s; }
        .drawer-overlay.visible { opacity: 1; pointer-events: auto; }
        .comments-drawer { position: fixed; top: 0; right: -450px; width: 400px; height: 100%; z-index: 100; background: rgba(10, 10, 20, 0.9); backdrop-filter: blur(15px); border-left: 1px solid var(--secondary); transition: right 0.4s cubic-bezier(0.16, 1, 0.3, 1); display: flex; flex-direction: column; }
        .comments-drawer.open { right: 0; }
        
        .drawer-header { padding: 20px; border-bottom: 1px solid rgba(255,255,255,0.1); display: flex; justify-content: space-between; align-items: center; font-family: 'Orbitron'; }
        .comments-list { flex-grow: 1; overflow-y: auto; padding: 20px; }
        .comment-item { margin-bottom: 15px; border-left: 2px solid var(--primary); padding-left: 10px; cursor: pointer; transition: 0.2s; }
        .comment-item:hover { background: rgba(255,255,255,0.05); }
        .comment-time { color: var(--secondary); font-weight: bold; font-family: monospace; margin-right: 5px; }
        
        .drawer-footer { padding: 20px; border-top: 1px solid rgba(255,255,255,0.1); }
        .comment-input-group { display: flex; gap: 10px; }
        .comment-input { flex-grow: 1; background: rgba(0,0,0,0.3); border: 1px solid #444; color: white; padding: 10px; border-radius: 5px; font-family: 'Rajdhani'; outline: none; }
        .comment-input:focus { border-color: var(--secondary); }
        .send-btn { background: var(--primary); color: white; border: none; padding: 0 15px; border-radius: 5px; cursor: pointer; font-family: 'Orbitron'; font-weight: bold; }
        
        audio { display: none; }
    </style>
</head>
<body>

    <div id="canvas-container"></div>

    <div class="hud-layer">
        <div class="top-bar">
             <a href="index.php" class="back-btn"><i class="fas fa-arrow-left"></i> RETOUR</a>
             <div class="song-info">
                <h1 class="song-title"><?php echo htmlspecialchars($music['title']); ?></h1>
                <div style="text-align:right; color:var(--secondary); letter-spacing:3px; margin-bottom:5px;">
                    <?php echo strtoupper(htmlspecialchars($music['username'])); ?>
                </div>
                <div class="rating-stars" id="starContainer">
                    <?php for($i=1; $i<=5; $i++): ?>
                        <span class="star <?php echo ($i <= round($avgRating)) ? 'filled' : ''; ?>" onclick="submitRating(<?php echo $i; ?>)">★</span>
                    <?php endfor; ?>
                </div>
            </div>
        </div>

        <div class="comment-popup" id="popup">
            <span class="popup-user" id="popupUser">USER</span>
            <span class="popup-content" id="popupContent">This is a comment</span>
        </div>

        <div class="comments-trigger" onclick="toggleDrawer(true)">
            <i class="fas fa-comment-alt"></i> Commentaires (<?php echo count($comments); ?>)
        </div>

        <div class="bottom-bar">
            <button id="playBtn" class="play-btn"><i class="fas fa-play"></i></button>
            <div class="progress-wrapper" id="progressContainer">
                <div class="progress-fill" id="progressBar"></div>
            </div>
            <div class="time"><span id="currTime">00:00</span></div>
        </div>
    </div>

    <div class="drawer-overlay" id="overlay" onclick="toggleDrawer(false)"></div>
    <div class="comments-drawer <?php echo $openDrawer; ?>" id="drawer">
        <div class="drawer-header">
            <span>TIMELINE_LOGS</span>
            <i class="fas fa-times" style="cursor:pointer;" onclick="toggleDrawer(false)"></i>
        </div>
        <div class="comments-list" id="commentsList">
            <?php foreach($comments as $c): ?>
                <div class="comment-item" onclick="jumpTo(<?php echo $c['timestamp']; ?>)">
                    <div>
                        <span class="comment-time">[<?php echo gmdate("i:s", $c['timestamp']); ?>]</span>
                        <span style="font-weight:bold; color:#ddd;"><?php echo htmlspecialchars($c['username']); ?></span>
                    </div>
                    <div style="color:#aaa; margin-top:3px;"><?php echo htmlspecialchars($c['content']); ?></div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="drawer-footer">
            <?php if(isset($_SESSION['user_id'])): ?>
            <form method="POST" class="comment-input-group">
                <input type="hidden" name="timestamp" id="timestampInput" value="0">
                <input type="text" name="comment" class="comment-input" placeholder="Commentez à cet instant..." required autocomplete="off">
                <button type="submit" class="send-btn">SEND</button>
            </form>
            <?php else: ?>
                <a href="connexion.php" style="color:var(--secondary)">Connexion requise</a>
            <?php endif; ?>
        </div>
    </div>

    <form id="ratingForm" method="POST" style="display:none;">
        <input type="hidden" name="rating" id="ratingInput">
    </form>
    
    <audio id="audio" src="uploads/mp3/<?php echo htmlspecialchars($music['filename']); ?>" crossorigin="anonymous"></audio>

    <script>
        const commentsData = <?php echo json_encode($comments); ?>;
        const isUserLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
    </script>

    <script type="module" src="assets/player.js"></script>

</body>
</html>
