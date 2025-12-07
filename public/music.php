<?php
session_start();
require_once '../config/db.php';

if (!isset($_GET['id'])) die("ID manquant");
$music_id = (int)$_GET['id'];

// Récupération des données BDD
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
    <title><?php echo htmlspecialchars($music['title']); ?> - Visualizer</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/simplex-noise/2.4.0/simplex-noise.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;900&family=Rajdhani:wght@300;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #bd00ff; /* Le violet de ton image */
            --secondary: #00f3ff; /* Cyan pour le contraste */
            --bg: #050505;
        }

        body { 
            margin: 0; 
            background: var(--bg); 
            color: white; 
            font-family: 'Rajdhani', sans-serif; 
            overflow: hidden; /* Pas de scroll, tout est dans l'écran */
        }

        /* --- LE VISUALISEUR --- */
        #canvas-container {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100vh;
            z-index: 1;
        }

        /* --- L'INTERFACE (HUD) --- */
        .hud-layer {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            z-index: 10;
            pointer-events: none; /* Laisse passer les clics vers la 3D si besoin */
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 40px;
            box-sizing: border-box;
            background: radial-gradient(circle at center, transparent 0%, rgba(5,5,10,0.8) 100%);
        }

        /* Header */
        .top-bar {
            pointer-events: auto;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .back-btn {
            color: white; text-decoration: none; font-size: 1.2rem;
            display: flex; align-items: center; gap: 10px;
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

        /* Footer / Player Controls */
        .bottom-bar {
            pointer-events: auto;
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            background: rgba(20, 20, 30, 0.4);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 20px;
            padding: 20px 30px;
            display: flex;
            align-items: center;
            gap: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }

        .play-btn {
            background: var(--primary);
            border: none;
            width: 60px; height: 60px;
            border-radius: 50%;
            color: white; font-size: 1.5rem;
            cursor: pointer;
            box-shadow: 0 0 20px var(--primary);
            transition: 0.2s;
            display: flex; align-items: center; justify-content: center;
        }
        .play-btn:hover { transform: scale(1.1); background: #d94dff; }

        .progress-wrapper { flex-grow: 1; position: relative; height: 5px; background: rgba(255,255,255,0.1); cursor: pointer; }
        .progress-fill { height: 100%; background: var(--secondary); width: 0%; box-shadow: 0 0 10px var(--secondary); position: relative; }
        .progress-fill::after { content:''; position: absolute; right: -5px; top: -5px; width: 15px; height: 15px; background: white; border-radius: 50%; box-shadow: 0 0 10px white; }
        
        .time { font-family: monospace; font-size: 1rem; color: #ccc; width: 100px; text-align: right; }

        /* Commentaires (Drawer) */
        .comments-trigger {
            position: absolute; right: 40px; bottom: 120px;
            pointer-events: auto;
            color: rgba(255,255,255,0.5); cursor: pointer;
            transition: 0.3s; text-align: right;
        }
        .comments-trigger:hover { color: white; }

        /* Input caché pour l'audio */
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

        <div class="comments-trigger">
            <i class="fas fa-comment-alt"></i> <?php echo count($comments); ?> Commentaires<br>
            <small>(Feature à venir dans la V2)</small>
        </div>

        <div class="bottom-bar">
            <button id="playBtn" class="play-btn"><i class="fas fa-play"></i></button>
            
            <div class="progress-wrapper" id="progressContainer">
                <div class="progress-fill" id="progressBar"></div>
            </div>
            
            <div class="time">
                <span id="currTime">00:00</span>
            </div>
        </div>
    </div>

    <audio id="audio" src="uploads/mp3/<?php echo htmlspecialchars($music['filename']); ?>" crossorigin="anonymous"></audio>

<script>
    // --- 1. LOGIQUE DU LECTEUR AUDIO (UI) ---
    const audio = document.getElementById('audio');
    const playBtn = document.getElementById('playBtn');
    const icon = playBtn.querySelector('i');
    const progressBar = document.getElementById('progressBar');
    const progressContainer = document.getElementById('progressContainer');
    const currTimeDisplay = document.getElementById('currTime');
    
    let isPlaying = false;

    playBtn.addEventListener('click', togglePlay);

    function togglePlay() {
        if (!context) initAudioContext(); // Démarrer l'audio context au premier clic (règle navigateur)

        if (isPlaying) {
            audio.pause();
            icon.classList.replace('fa-pause', 'fa-play');
            playBtn.style.boxShadow = "0 0 20px var(--primary)";
        } else {
            audio.play();
            icon.classList.replace('fa-play', 'fa-pause');
            playBtn.style.boxShadow = "0 0 40px var(--primary), inset 0 0 10px white";
        }
        isPlaying = !isPlaying;
    }

    audio.addEventListener('timeupdate', () => {
        const percent = (audio.currentTime / audio.duration) * 100;
        progressBar.style.width = `${percent}%`;
        
        let mins = Math.floor(audio.currentTime / 60);
        let secs = Math.floor(audio.currentTime % 60);
        if (secs < 10) secs = '0' + secs;
        currTimeDisplay.innerText = `${mins}:${secs}`;
    });

    progressContainer.addEventListener('click', (e) => {
        const width = progressContainer.clientWidth;
        const clickX = e.offsetX;
        const duration = audio.duration;
        audio.currentTime = (clickX / width) * duration;
    });


    // --- 2. LE VISUALISEUR "BLOB" (Three.js) ---
    let scene, camera, renderer, geometry, mesh;
    let context, analyser, src, dataArray;
    let noise = new SimplexNoise(); // Générateur de bruit organique

    function init3D() {
        const container = document.getElementById('canvas-container');
        scene = new THREE.Scene();
        
        // Caméra
        camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
        camera.position.z = 4; // Zoom initial

        // Rendu
        renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true });
        renderer.setSize(window.innerWidth, window.innerHeight);
        renderer.setPixelRatio(window.devicePixelRatio);
        container.appendChild(renderer.domElement);

        // LUMIÈRES (Pour faire briller le blob)
        const ambientLight = new THREE.AmbientLight(0xffffff, 0.2);
        scene.add(ambientLight);
        
        const pointLight = new THREE.PointLight(0xbd00ff, 1);
        pointLight.position.set(5, 5, 5);
        scene.add(pointLight);

        const pointLight2 = new THREE.PointLight(0x00f3ff, 1); // Cyan light
        pointLight2.position.set(-5, -5, 5);
        scene.add(pointLight2);

        createBlob();
        animate();
    }

    function createBlob() {
        // Icosahedron = Sphère géométrique (le look de ton image)
        // Detail = 4 (Assez de points pour être fluide, pas trop pour ne pas ramer)
        geometry = new THREE.IcosahedronGeometry(1.8, 4);
        
        // Matériau Wireframe violet comme l'image
        const material = new THREE.MeshBasicMaterial({ 
            color: 0xbd00ff, // VIOLET
            wireframe: true,
            transparent: true,
            opacity: 0.8
        });

        mesh = new THREE.Mesh(geometry, material);
        scene.add(mesh);
    }

    function initAudioContext() {
        if (context) return;
        context = new (window.AudioContext || window.webkitAudioContext)();
        analyser = context.createAnalyser();
        src = context.createMediaElementSource(audio);
        src.connect(analyser);
        analyser.connect(context.destination);
        analyser.fftSize = 512;
        dataArray = new Uint8Array(analyser.frequencyBinCount);
    }

    function animate() {
        requestAnimationFrame(animate);

        // Récupérer les données audio
        let bass = 0;
        let mid = 0;
        if (analyser) {
            analyser.getByteFrequencyData(dataArray);
            bass = dataArray[0]; // Les basses fréquences (0-255)
            mid = dataArray[20];
        }

        // Normaliser les basses (0.0 à 1.0)
        const bassNorm = bass / 255;
        const time = performance.now() * 0.001;

        // --- DÉFORMATION DU BLOB ---
        // On accède à la position de chaque point (vertex) de la sphère
        const positionAttribute = geometry.getAttribute('position');
        const vertex = new THREE.Vector3();

        // Pour chaque point...
        for (let i = 0; i < positionAttribute.count; i++) {
            vertex.fromBufferAttribute(positionAttribute, i);
            
            // On le fait bouger "organiquement" avec le Noise
            // Le rayon change selon le temps ET la basse
            vertex.normalize();
            
            // Paramètres de déformation
            const distance = 1.8 + 
                             noise.noise3D(vertex.x + time, vertex.y + time, vertex.z + time) * (0.3 + bassNorm * 0.8); // Plus il y a de basse, plus ça se déforme

            vertex.multiplyScalar(distance);
            
            positionAttribute.setXYZ(i, vertex.x, vertex.y, vertex.z);
        }

        geometry.computeVertexNormals();
        positionAttribute.needsUpdate = true; // IMPORTANT : Dire à Three.js de mettre à jour la forme

        // Rotation lente permanente + accélération sur le beat
        mesh.rotation.y += 0.002 + (bassNorm * 0.01);
        mesh.rotation.z += 0.002;

        // Effet de zoom caméra sur les gros beats
        // camera.position.z = 4 - (bassNorm * 0.5); 

        renderer.render(scene, camera);
    }

    // Resize responsive
    window.addEventListener('resize', () => {
        renderer.setSize(window.innerWidth, window.innerHeight);
        camera.aspect = window.innerWidth / window.innerHeight;
        camera.updateProjectionMatrix();
    });

    init3D();

</script>
</body>
</html>
