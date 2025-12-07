<?php
session_start();
require_once '../config/db.php';

if (!isset($_GET['id'])) die("ID manquant");
$music_id = (int)$_GET['id'];

// 1. TRAITEMENT : Ajouter un commentaire (AVANT l'affichage)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment']) && isset($_SESSION['user_id'])) {
    $content = trim($_POST['comment']);
    if (!empty($content)) {
        $sqlComm = "INSERT INTO comments (user_id, music_id, content) VALUES (?, ?, ?)";
        $stmtComm = $pdo->prepare($sqlComm);
        $stmtComm->execute([$_SESSION['user_id'], $music_id, $content]);
        
        // On redirige pour éviter le renvoi du formulaire au F5 (Post-Redirect-Get)
        header("Location: music.php?id=" . $music_id . "&drawer=open"); 
        exit();
    }
}

// 2. Récupération des données BDD
$stmt = $pdo->prepare("SELECT m.*, u.username FROM musics m JOIN users u ON m.user_id = u.id WHERE m.id = ?");
$stmt->execute([$music_id]);
$music = $stmt->fetch();
if (!$music) die("Musique introuvable");

// Moyenne des notes
$stmtAvg = $pdo->prepare("SELECT AVG(value) as moy FROM ratings WHERE music_id = ?");
$stmtAvg->execute([$music_id]);
$avgRating = round($stmtAvg->fetch()['moy'], 1);

// Commentaires
$stmtC = $pdo->prepare("SELECT c.*, u.username FROM comments c JOIN users u ON c.user_id = u.id WHERE c.music_id = ? ORDER BY c.created_at DESC");
$stmtC->execute([$music_id]);
$comments = $stmtC->fetchAll();

// On vérifie si on doit ouvrir le drawer au chargement (après un post)
$autoOpenDrawer = isset($_GET['drawer']) && $_GET['drawer'] === 'open';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($music['title']); ?> - Visualizer</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/simplex-noise/2.4.0/simplex-noise.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;900&family=Rajdhani:wght@300;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #bd00ff;
            --secondary: #00f3ff;
            --bg: #050505;
        }

        body { 
            margin: 0; 
            background: var(--bg); 
            color: white; 
            font-family: 'Rajdhani', sans-serif; 
            overflow: hidden; 
        }

        /* --- VISUALISEUR --- */
        #canvas-container { position: fixed; top: 0; left: 0; width: 100%; height: 100vh; z-index: 1; }

        /* --- HUD (Interface) --- */
        .hud-layer {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 10;
            pointer-events: none; /* Click-through */
            display: flex; flex-direction: column; justify-content: space-between;
            padding: 40px; box-sizing: border-box;
            background: radial-gradient(circle at center, transparent 0%, rgba(5,5,10,0.8) 100%);
        }

        /* Top Bar */
        .top-bar { pointer-events: auto; display: flex; justify-content: space-between; align-items: flex-start; }
        .back-btn {
            color: white; text-decoration: none; font-size: 1.2rem; display: flex; align-items: center; gap: 10px;
            opacity: 0.7; transition: 0.3s;
        }
        .back-btn:hover { opacity: 1; color: var(--secondary); text-shadow: 0 0 10px var(--secondary); }

        .song-info { text-align: right; }
        .song-title { 
            font-family: 'Orbitron'; font-size: 3rem; margin: 0; line-height: 1;
            text-transform: uppercase; letter-spacing: 2px;
            text-shadow: 0 0 20px rgba(189, 0, 255, 0.6);
        }
        .artist-name { font-size: 1.5rem; color: var(--secondary); margin-top: 5px; letter-spacing: 4px; }
        .rating { color: #ffd700; margin-top: 10px; font-size: 1.2rem; }

        /* Trigger Commentaires */
        .comments-trigger {
            position: absolute; right: 40px; bottom: 120px; pointer-events: auto;
            color: rgba(255,255,255,0.7); cursor: pointer; transition: 0.3s; text-align: right;
            border: 1px solid rgba(255,255,255,0.2); padding: 10px 20px; border-radius: 30px;
            background: rgba(0,0,0,0.3);
        }
        .comments-trigger:hover { color: white; border-color: var(--secondary); background: rgba(0, 243, 255, 0.1); }

        /* Lecteur */
        .bottom-bar {
            pointer-events: auto; width: 100%; max-width: 800px; margin: 0 auto;
            background: rgba(20, 20, 30, 0.4); backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.1); border-radius: 20px;
            padding: 20px 30px; display: flex; align-items: center; gap: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }
        .play-btn {
            background: var(--primary); border: none; width: 60px; height: 60px; border-radius: 50%;
            color: white; font-size: 1.5rem; cursor: pointer; box-shadow: 0 0 20px var(--primary);
            transition: 0.2s; display: flex; align-items: center; justify-content: center;
        }
        .play-btn:hover { transform: scale(1.1); background: #d94dff; }
        .progress-wrapper { flex-grow: 1; position: relative; height: 5px; background: rgba(255,255,255,0.1); cursor: pointer; }
        .progress-fill { height: 100%; background: var(--secondary); width: 0%; box-shadow: 0 0 10px var(--secondary); position: relative; }
        .progress-fill::after { content:''; position: absolute; right: -5px; top: -5px; width: 15px; height: 15px; background: white; border-radius: 50%; box-shadow: 0 0 10px white; }
        .time { font-family: monospace; font-size: 1rem; color: #ccc; width: 100px; text-align: right; }

        /* --- DRAWER COMMENTAIRES (Le panneau latéral) --- */
        .drawer-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 90;
            background: rgba(0,0,0,0.5); opacity: 0; pointer-events: none; transition: 0.3s;
        }
        .drawer-overlay.active { opacity: 1; pointer-events: auto; }

        .comments-drawer {
            position: fixed; top: 0; right: -450px; width: 400px; height: 100%; z-index: 100;
            background: rgba(10, 10, 20, 0.85); backdrop-filter: blur(15px);
            border-left: 1px solid var(--secondary);
            box-shadow: -10px 0 30px rgba(0,0,0,0.8);
            transition: right 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            display: flex; flex-direction: column;
        }
        .comments-drawer.open { right: 0; }

        .drawer-header {
            padding: 20px; border-bottom: 1px solid rgba(255,255,255,0.1);
            display: flex; justify-content: space-between; align-items: center;
        }
        .close-btn { background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer; }
        
        .comments-list { flex-grow: 1; overflow-y: auto; padding: 20px; }
        .comment-item { margin-bottom: 20px; animation: fadeIn 0.3s ease; }
        .comment-user { color: var(--secondary); font-weight: bold; font-family: 'Orbitron'; font-size: 0.9rem; }
        .comment-date { font-size: 0.7rem; color: #666; margin-left: 10px; }
        .comment-content { background: rgba(255,255,255,0.05); padding: 10px; border-radius: 0 10px 10px 10px; margin-top: 5px; line-height: 1.4; border-left: 2px solid var(--primary); }

        .drawer-footer { padding: 20px; border-top: 1px solid rgba(255,255,255,0.1); }
        .comment-form { display: flex; gap: 10px; }
        .comment-input {
            flex-grow: 1; background: rgba(0,0,0,0.3); border: 1px solid #444; color: white;
            padding: 10px; border-radius: 5px; font-family: 'Rajdhani'; outline: none;
        }
        .comment-input:focus { border-color: var(--secondary); }
        .send-btn {
            background: var(--primary); color: white; border: none; padding: 0 15px;
            border-radius: 5px; cursor: pointer; font-family: 'Orbitron';
        }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
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
                <div class="artist-name"><?php echo strtoupper(htmlspecialchars($music['username'])); ?></div>
                <div class="rating">
                    <?php for($i=0; $i<5; $i++) echo ($i < round($avgRating)) ? '★' : '☆'; ?>
                </div>
            </div>
        </div>

        <div class="comments-trigger" onclick="openDrawer()">
            <i class="fas fa-comment-alt"></i> Ouvrir le Chat (<?php echo count($comments); ?>)
        </div>

        <div class="bottom-bar">
            <button id="playBtn" class="play-btn"><i class="fas fa-play"></i></button>
