<?php
session_start();
require_once '../config/db.php';

if (!isset($_GET['id'])) die("ID manquant");
$music_id = (int)$_GET['id'];

// Récupération des données (identique à avant)
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
            --neon-blue: #00f3ff;
            --neon-purple: #bc13fe;
            --bg-dark: #050510;
            --glass: rgba(255, 255, 255, 0.05);
        }

        body { 
            margin: 0; 
            background: var(--bg-dark); 
            color: white; 
            font-family: 'Rajdhani', sans-serif; 
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
        }

        .play-btn {
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
