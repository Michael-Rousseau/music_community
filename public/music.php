<?php
session_start();
require_once '../config/db.php';

if (!isset($_GET['id'])) die("ID manquant");
$music_id = (int)$_GET['id'];

<<<<<<< HEAD
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
=======
// Récupération des données (identique à avant)
>>>>>>> parent of 1c2316c (update three js again)
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
    <title><?php echo htmlspecialchars($music['title']); ?> - Futur Player</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Rajdhani:wght@300;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        :root {
<<<<<<< HEAD
            --primary: #bd00ff;
            --secondary: #00f3ff;
            --bg: #050505;
=======
            --neon-blue: #00f3ff;
            --neon-purple: #bc13fe;
            --bg-dark: #050510;
            --glass: rgba(255, 255, 255, 0.05);
>>>>>>> parent of 1c2316c (update three js again)
        }

        body { 
            margin: 0; 
            background: var(--bg-dark); 
            color: white; 
            font-family: 'Rajdhani', sans-serif; 
<<<<<<< HEAD
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
=======
            overflow-x: hidden;
        }

        /* --- LAYOUT --- */
        #scene-container {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100vh;
            z-index: 0;
        }

        .interface-layer {
            position: relative;
            z-index: 10;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: flex-end; /* Interface en bas pour laisser la place à la 3D */
            padding-bottom: 50px;
        }

        /* --- HEADER & RETOUR --- */
        .top-bar {
            position: absolute; top: 20px; left: 20px;
        }
        .btn-back {
            color: var(--neon-blue);
            text-decoration: none;
            font-family: 'Orbitron', sans-serif;
            font-size: 0.8rem;
            border: 1px solid var(--neon-blue);
            padding: 8px 15px;
            transition: 0.3s;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .btn-back:hover { background: var(--neon-blue); color: black; box-shadow: 0 0 15px var(--neon-blue); }

        /* --- PLAYER HUD (Lecteur Custom) --- */
        .player-hud {
            background: rgba(10, 10, 20, 0.7);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-left: 4px solid var(--neon-purple);
            padding: 30px;
            border-radius: 0 20px 0 20px; /* Bordures futuristes */
            box-shadow: 0 0 30px rgba(0,0,0,0.5);
            margin-top: 300px; /* Espace pour la sphère 3D au dessus */
        }

        .track-info h1 {
            font-family: 'Orbitron', sans-serif;
            margin: 0;
            font-size: 2.5rem;
            text-transform: uppercase;
            text-shadow: 0 0 10px rgba(188, 19, 254, 0.5);
            background: linear-gradient(90deg, #fff, var(--neon-blue));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .track-info p { color: #aaa; letter-spacing: 1px; margin-top: 5px; font-size: 1.1rem; }

        /* Contrôles Custom */
        .controls-row {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-top: 20px;
>>>>>>> parent of 1c2316c (update three js again)
        }
        .play-btn {
<<<<<<< HEAD
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
=======
            background: transparent;
            border: 2px solid var(--neon-blue);
            color: var(--neon-blue);
            width: 60px; height: 60px;
            border-radius: 50%;
            font-size: 1.5rem;
            cursor: pointer;
            transition: 0.3s;
            display: flex; align-items: center; justify-content: center;
        }
        .play-btn:hover {
            background: var(--neon-blue);
            color: black;
            box-shadow: 0 0 20px var(--neon-blue);
        }

        /* Barre de progression Néon */
        .progress-container {
            flex-grow: 1;
            height: 6px;
            background: rgba(255,255,255,0.1);
            cursor: pointer;
            position: relative;
        }
        .progress-bar {
            height: 100%;
            background: var(--neon-purple);
            width: 0%;
            position: relative;
            box-shadow: 0 0 10px var(--neon-purple);
            transition: width 0.1s linear;
        }
        .progress-bar::after {
            content: '';
            position: absolute;
            right: -5px; top: -4px;
            width: 14px; height: 14px;
            background: white;
            border-radius: 50%;
            box-shadow: 0 0 10px white;
        }
        .time-display { font-family: 'Orbitron'; font-size: 0.8rem; color: var(--neon-blue); min-width: 80px; text-align: right; }

        /* --- COMMENTAIRES --- */
        .comments-section {
            margin-top: 20px;
            background: var(--glass);
            padding: 20px;
            border-radius: 10px;
        }
        .comment {
            border-bottom: 1px solid rgba(255,255,255,0.05);
            padding: 10px 0;
            font-size: 0.95rem;
        }
        .comment strong { color: var(--neon-blue); }

        /* Input comment stylisé */
        .input-group { display: flex; gap: 10px; margin-bottom: 15px; }
        input[type="text"] {
            background: rgba(0,0,0,0.3);
            border: 1px solid #333;
            color: white;
            padding: 10px;
            flex-grow: 1;
            font-family: 'Rajdhani';
        }
        input[type="text"]:focus { outline: none; border-color: var(--neon-blue); }
        .btn-send {
            background: var(--neon-purple);
            border: none;
            color: white;
            padding: 0 20px;
            cursor: pointer;
            font-weight: bold;
            text-transform: uppercase;
        }

>>>>>>> parent of 1c2316c (update three js again)
    </style>
</head>
<body>

<div id="scene-container"></div>

<div class="interface-layer">
    
    <div class="top-bar">
        <a href="index.php" class="btn-back"><i class="fas fa-chevron-left"></i> Accueil</a>
    </div>

    <div class="player-hud">
        <div class="track-info">
            <h1><?php echo htmlspecialchars($music['title']); ?></h1>
            <p>ARTIST // <?php echo strtoupper(htmlspecialchars($music['username'])); ?></p>
            <div style="color: gold; margin-top: 5px;">
                ★ <?php echo $avgRating ?: '0'; ?>/5 
                <span style="font-size:0.8rem; color:#666; margin-left:10px;">(SYNC_ID: <?php echo $music['id']; ?>)</span>
            </div>
        </div>

<<<<<<< HEAD
        <div class="comments-trigger" onclick="openDrawer()">
            <i class="fas fa-comment-alt"></i> Ouvrir le Chat (<?php echo count($comments); ?>)
        </div>

        <div class="bottom-bar">
            <button id="playBtn" class="play-btn"><i class="fas fa-play"></i></button>
=======
        <div class="controls-row">
            <button id="playBtn" class="play-btn"><i class="fas fa-play"></i></button>
            
            <div class="progress-container" id="progressContainer">
                <div class="progress-bar" id="progressBar"></div>
            </div>
            
            <div class="time-display">
                <span id="currTime">00:00</span> / <span id="durTime">00:00</span>
            </div>
        </div>
        
        <audio id="audio" src="uploads/mp3/<?php echo htmlspecialchars($music['filename']); ?>" crossorigin="anonymous"></audio>
    </div>

    <div class="comments-section">
        <h3 style="font-family:'Orbitron'; color:var(--neon-blue);">DATA_LOGS (<?php echo count($comments); ?>)</h3>
        
        <?php if(isset($_SESSION['user_id'])): ?>
        <form method="POST" class="input-group">
            <input type="text" name="comment" placeholder="Transmettre un message..." required>
            <button type="submit" class="btn-send">Envoyer</button>
        </form>
        <?php else: ?>
            <p style="font-style:italic; opacity:0.6;">Accès écriture restreint. <a href="connexion.php" style="color:var(--neon-blue)">Connexion requise</a>.</p>
        <?php endif; ?>

        <div style="max-height: 200px; overflow-y: auto;">
            <?php foreach($comments as $c): ?>
                <div class="comment">
                    <strong>[<?php echo htmlspecialchars($c['username']); ?>]</strong> : 
                    <?php echo htmlspecialchars($c['content']); ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>

<script>
    // --- 1. CONFIGURATION DU LECTEUR CUSTOM ---
    const audio = document.getElementById('audio');
    const playBtn = document.getElementById('playBtn');
    const playIcon = playBtn.querySelector('i');
    const progressBar = document.getElementById('progressBar');
    const progressContainer = document.getElementById('progressContainer');
    const currTime = document.getElementById('currTime');
    const durTime = document.getElementById('durTime');

    let isPlaying = false;

    // Play/Pause
    playBtn.addEventListener('click', () => {
        if (isPlaying) {
            audio.pause();
            playIcon.classList.remove('fa-pause');
            playIcon.classList.add('fa-play');
            // Effet visuel
            playBtn.style.boxShadow = "none";
        } else {
            audio.play();
            playIcon.classList.remove('fa-play');
            playIcon.classList.add('fa-pause');
            // Démarrer l'audio context si ce n'est pas fait
            initAudioContext();
            playBtn.style.boxShadow = "0 0 20px var(--neon-blue)";
        }
        isPlaying = !isPlaying;
    });

    // Mise à jour barre progression
    audio.addEventListener('timeupdate', (e) => {
        const { duration, currentTime } = e.srcElement;
        const progressPercent = (currentTime / duration) * 100;
        progressBar.style.width = `${progressPercent}%`;
        
        // Formatage temps
        if(duration) {
            durTime.innerText = formatTime(duration);
            currTime.innerText = formatTime(currentTime);
        }
    });

    // Clic sur la barre
    progressContainer.addEventListener('click', (e) => {
        const width = progressContainer.clientWidth;
        const clickX = e.offsetX;
        const duration = audio.duration;
        audio.currentTime = (clickX / width) * duration;
    });

    function formatTime(time) {
        let min = Math.floor(time / 60);
        let sec = Math.floor(time % 60);
        if(sec < 10) sec = `0${sec}`;
        return `${min}:${sec}`;
    }


    // --- 2. VISUALISATION 3D REACTIVE (PARTICULES) ---
    let scene, camera, renderer, particles, analyser, dataArray;
    let audioContext, source;
    let setupDone = false;

    function initThree() {
        const container = document.getElementById('scene-container');
        scene = new THREE.Scene();
        
        // Caméra
        camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
        camera.position.z = 100; // Reculer la caméra

        // Renderer
        renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true });
        renderer.setSize(window.innerWidth, window.innerHeight);
        container.appendChild(renderer.domElement);

        // --- CRÉATION DES PARTICULES (Le "Nuage") ---
        const geometry = new THREE.BufferGeometry();
        const count = 2000; // Nombre de particules
        const positions = new Float32Array(count * 3); // x, y, z

        for(let i = 0; i < count * 3; i++) {
            // Position aléatoire dans une sphère
            positions[i] = (Math.random() - 0.5) * 100; 
        }

        geometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));

        // Matériau brillant
        const material = new THREE.PointsMaterial({
            size: 1.5,
            color: 0x00f3ff, // Cyan néon
            transparent: true,
            opacity: 0.8,
            blending: THREE.AdditiveBlending // Donne l'effet "glowing"
        });

        particles = new THREE.Points(geometry, material);
        scene.add(particles);

        animate();
    }

    function initAudioContext() {
        if(setupDone) return;
        
        audioContext = new (window.AudioContext || window.webkitAudioContext)();
        source = audioContext.createMediaElementSource(audio);
        analyser = audioContext.createAnalyser();
        
        source.connect(analyser);
        analyser.connect(audioContext.destination);
        
        analyser.fftSize = 512;
        dataArray = new Uint8Array(analyser.frequencyBinCount);
        setupDone = true;
    }

    function animate() {
        requestAnimationFrame(animate);

        if(analyser) {
            analyser.getByteFrequencyData(dataArray);
            
            // Calcul des Basses (basses fréquences)
            let bass = 0;
            for(let i = 0; i < 50; i++) {
                bass += dataArray[i];
            }
            bass = bass / 50; // Moyenne

            // --- RÉACTIVITÉ ---
            
            // 1. Zoom de la caméra selon le rythme
            const scale = 1 + (bass / 255) * 0.5; 
            particles.scale.set(scale, scale, scale);

            // 2. Rotation des particules (plus vite quand c'est fort)
            particles.rotation.y += 0.002 + (bass * 0.0001);
            particles.rotation.x += 0.001;

            // 3. Changement de couleur (Cyan -> Violet selon intensité)
            // On joue sur le rouge (R)
            particles.material.color.setRGB(
                (bass / 255), // Plus de rouge = plus violet
                0.8 - (bass/500), // Moins de vert
                1 // Toujours bleu
            );
        } else {
            // Animation "Repos" (si pas de musique)
            particles.rotation.y += 0.002;
        }

        renderer.render(scene, camera);
    }

    // Gestion redimensionnement
    window.addEventListener('resize', () => {
        camera.aspect = window.innerWidth / window.innerHeight;
        camera.updateProjectionMatrix();
        renderer.setSize(window.innerWidth, window.innerHeight);
    });

    initThree();

</script>
</body>
</html>
>>>>>>> parent of 1c2316c (update three js again)
