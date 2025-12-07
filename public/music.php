<?php
session_start();
require_once '../config/db.php';

if (!isset($_GET['id'])) die("ID manquant");
$music_id = (int)$_GET['id'];

// --- 1. POST : AJOUTER COMMENTAIRE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment']) && isset($_SESSION['user_id'])) {
    $content = trim($_POST['comment']);
    if (!empty($content)) {
        $stmt = $pdo->prepare("INSERT INTO comments (user_id, music_id, content) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $music_id, $content]);
        header("Location: music.php?id=" . $music_id . "&drawer=open"); 
        exit();
    }
}

// --- 2. POST : NOTER (RATING) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rating']) && isset($_SESSION['user_id'])) {
    $val = (int)$_POST['rating'];
    if ($val >= 1 && $val <= 5) {
        $stmt = $pdo->prepare("INSERT INTO ratings (user_id, music_id, value) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE value = ?");
        $stmt->execute([$_SESSION['user_id'], $music_id, $val, $val]);
        header("Location: music.php?id=" . $music_id); 
        exit();
    }
}

// --- 3. RECUPERATION DONNEES ---
$stmt = $pdo->prepare("SELECT m.*, u.username FROM musics m JOIN users u ON m.user_id = u.id WHERE m.id = ?");
$stmt->execute([$music_id]);
$music = $stmt->fetch();
if (!$music) die("Musique introuvable");

$stmtAvg = $pdo->prepare("SELECT AVG(value) as moy FROM ratings WHERE music_id = ?");
$stmtAvg->execute([$music_id]);
$avgRating = round($stmtAvg->fetch()['moy'], 1);

$stmtC = $pdo->prepare("SELECT c.*, u.username FROM comments c JOIN users u ON c.user_id = u.id WHERE c.music_id = ? ORDER BY c.created_at DESC");
$stmtC->execute([$music_id]);
$comments = $stmtC->fetchAll();

$openDrawer = (isset($_GET['drawer']) && $_GET['drawer'] === 'open') ? 'open' : '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($music['title']); ?></title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/simplex-noise/2.4.0/simplex-noise.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;900&family=Rajdhani:wght@300;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root { --primary: #bd00ff; --secondary: #00f3ff; --bg: #050505; }
        body { margin: 0; background: var(--bg); color: white; font-family: 'Rajdhani', sans-serif; overflow: hidden; }

        /* 3D */
        #canvas-container { position: fixed; top: 0; left: 0; width: 100%; height: 100vh; z-index: 1; }

        /* HUD */
        .hud-layer {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 10;
            pointer-events: none; display: flex; flex-direction: column; justify-content: space-between;
            padding: 40px; box-sizing: border-box;
            background: radial-gradient(circle at center, transparent 0%, rgba(5,5,10,0.8) 100%);
        }

        /* Top Bar */
        .top-bar { pointer-events: auto; display: flex; justify-content: space-between; align-items: flex-start; }
        .back-btn { color: white; text-decoration: none; font-size: 1.2rem; display: flex; align-items: center; gap: 10px; opacity: 0.7; transition: 0.3s; }
        .back-btn:hover { opacity: 1; color: var(--secondary); text-shadow: 0 0 10px var(--secondary); }

        .song-info { text-align: right; }
        .song-title { font-family: 'Orbitron'; font-size: 3rem; margin: 0; line-height: 1; text-transform: uppercase; text-shadow: 0 0 20px rgba(189, 0, 255, 0.6); }
        .artist-name { font-size: 1.5rem; color: var(--secondary); margin-top: 5px; letter-spacing: 4px; }
        
        /* Stars Rating */
        .rating-stars { margin-top: 10px; font-size: 1.5rem; cursor: pointer; display: inline-block; }
        .star { color: #444; transition: 0.2s; }
        .star.filled { color: #ffd700; text-shadow: 0 0 10px #ffd700; }
        .star:hover { transform: scale(1.2); }

        /* Buttons */
        .comments-trigger {
            position: absolute; right: 40px; bottom: 120px; pointer-events: auto;
            color: rgba(255,255,255,0.7); cursor: pointer; transition: 0.3s; text-align: right;
            border: 1px solid rgba(255,255,255,0.2); padding: 10px 20px; border-radius: 30px; background: rgba(0,0,0,0.3);
        }
        .comments-trigger:hover { color: white; border-color: var(--secondary); background: rgba(0, 243, 255, 0.1); }

        /* Player */
        .bottom-bar {
            pointer-events: auto; width: 100%; max-width: 800px; margin: 0 auto;
            background: rgba(20, 20, 30, 0.4); backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.1); border-radius: 20px; padding: 20px 30px;
            display: flex; align-items: center; gap: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }
        .play-btn { background: var(--primary); border: none; width: 60px; height: 60px; border-radius: 50%; color: white; font-size: 1.5rem; cursor: pointer; box-shadow: 0 0 20px var(--primary); display: flex; align-items: center; justify-content: center; }
        .play-btn:hover { transform: scale(1.1); background: #d94dff; }
        .progress-wrapper { flex-grow: 1; position: relative; height: 5px; background: rgba(255,255,255,0.1); cursor: pointer; }
        .progress-fill { height: 100%; background: var(--secondary); width: 0%; box-shadow: 0 0 10px var(--secondary); position: relative; }
        .time { font-family: monospace; font-size: 1rem; color: #ccc; width: 60px; text-align: right; }

        /* Drawer */
        .drawer-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 90; background: rgba(0,0,0,0.5); opacity: 0; pointer-events: none; transition: 0.3s; }
        .drawer-overlay.visible { opacity: 1; pointer-events: auto; }
        
        .comments-drawer {
            position: fixed; top: 0; right: -450px; width: 400px; height: 100%; z-index: 100;
            background: rgba(10, 10, 20, 0.85); backdrop-filter: blur(15px); border-left: 1px solid var(--secondary);
            transition: right 0.4s cubic-bezier(0.16, 1, 0.3, 1); display: flex; flex-direction: column;
        }
        .comments-drawer.open { right: 0; }
        
        .drawer-header { padding: 20px; border-bottom: 1px solid rgba(255,255,255,0.1); display: flex; justify-content: space-between; align-items: center; font-family: 'Orbitron'; }
        .comments-list { flex-grow: 1; overflow-y: auto; padding: 20px; }
        .comment-item { margin-bottom: 20px; border-left: 2px solid var(--primary); padding-left: 10px; }
        .comment-user { color: var(--secondary); font-weight: bold; }
        .comment-date { font-size: 0.7rem; color: #666; margin-left: 5px; }
        .comment-content { margin-top: 5px; line-height: 1.4; color: #ddd; }
        
        .drawer-footer { padding: 20px; border-top: 1px solid rgba(255,255,255,0.1); }
        .comment-input { width: 100%; background: rgba(0,0,0,0.3); border: 1px solid #444; color: white; padding: 10px; border-radius: 5px; margin-bottom: 10px; font-family: 'Rajdhani'; }
        .send-btn { width: 100%; background: var(--primary); color: white; border: none; padding: 10px; border-radius: 5px; cursor: pointer; font-family: 'Orbitron'; }

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
                
                <div class="rating-stars" id="starContainer">
                    <?php for($i=1; $i<=5; $i++): ?>
                        <span class="star <?php echo ($i <= round($avgRating)) ? 'filled' : ''; ?>" 
                              onclick="submitRating(<?php echo $i; ?>)">★</span>
                    <?php endfor; ?>
                </div>
            </div>
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
            <span>DATA_LOGS</span>
            <i class="fas fa-times" style="cursor:pointer;" onclick="toggleDrawer(false)"></i>
        </div>
        <div class="comments-list">
            <?php foreach($comments as $c): ?>
                <div class="comment-item">
                    <div>
                        <span class="comment-user"><?php echo htmlspecialchars($c['username']); ?></span>
                        <span class="comment-date"><?php echo date('d/m H:i', strtotime($c['created_at'])); ?></span>
                    </div>
                    <div class="comment-content"><?php echo htmlspecialchars($c['content']); ?></div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="drawer-footer">
            <?php if(isset($_SESSION['user_id'])): ?>
            <form method="POST">
                <input type="text" name="comment" class="comment-input" placeholder="Message..." required>
                <button type="submit" class="send-btn">ENVOYER</button>
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
    // --- UI LOGIC ---
    function toggleDrawer(show) {
        const d = document.getElementById('drawer');
        const o = document.getElementById('overlay');
        if(show) { d.classList.add('open'); o.classList.add('visible'); }
        else { d.classList.remove('open'); o.classList.remove('visible'); }
    }

    function submitRating(val) {
        <?php if(!isset($_SESSION['user_id'])): ?>
            window.location.href = 'connexion.php';
        <?php else: ?>
            document.getElementById('ratingInput').value = val;
            document.getElementById('ratingForm').submit();
        <?php endif; ?>
    }

    // --- AUDIO ---
    const audio = document.getElementById('audio');
    const playBtn = document.getElementById('playBtn');
    const icon = playBtn.querySelector('i');
    const bar = document.getElementById('progressBar');
    const barCont = document.getElementById('progressContainer');
    let isPlaying = false;

    playBtn.addEventListener('click', () => {
        if(!context) initAudioContext();
        if(isPlaying) { audio.pause(); icon.classList.replace('fa-pause', 'fa-play'); }
        else { audio.play(); icon.classList.replace('fa-play', 'fa-pause'); }
        isPlaying = !isPlaying;
    });

    audio.addEventListener('timeupdate', () => {
        bar.style.width = (audio.currentTime/audio.duration)*100 + '%';
        let m = Math.floor(audio.currentTime/60), s = Math.floor(audio.currentTime%60);
        document.getElementById('currTime').innerText = `${m}:${s<10?'0'+s:s}`;
    });

    barCont.addEventListener('click', (e) => {
        audio.currentTime = (e.offsetX / barCont.clientWidth) * audio.duration;
    });

    // --- 3D VISUALIZER ---
    let scene, camera, renderer, geometry, mesh, context, analyser, dataArray;
    let noise = new SimplexNoise();

    function init3D() {
        scene = new THREE.Scene();
        camera = new THREE.PerspectiveCamera(75, window.innerWidth/window.innerHeight, 0.1, 1000);
        camera.position.z = 4;
        
        renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true });
        renderer.setSize(window.innerWidth, window.innerHeight);
        document.getElementById('canvas-container').appendChild(renderer.domElement);

        const l1 = new THREE.PointLight(0xbd00ff, 1); l1.position.set(5,5,5); scene.add(l1);
        const l2 = new THREE.PointLight(0x00f3ff, 1); l2.position.set(-5,-5,5); scene.add(l2);
        
        geometry = new THREE.IcosahedronGeometry(1.8, 4);
        const mat = new THREE.MeshBasicMaterial({ color: 0xbd00ff, wireframe: true, transparent: true, opacity: 0.8 });
        mesh = new THREE.Mesh(geometry, mat);
        scene.add(mesh);
        
        animate();
    }

    function initAudioContext() {
        if(context) return;
        context = new (window.AudioContext||window.webkitAudioContext)();
        analyser = context.createAnalyser();
        const src = context.createMediaElementSource(audio);
        src.connect(analyser);
        analyser.connect(context.destination);
        analyser.fftSize = 512;
        dataArray = new Uint8Array(analyser.frequencyBinCount);
    }

    function animate() {
        requestAnimationFrame(animate);
        let bass = 0;
        if(analyser) {
            analyser.getByteFrequencyData(dataArray);
            bass = dataArray[0];
        }
        
        const pos = geometry.getAttribute('position');
        const vec = new THREE.Vector3();
        const time = performance.now() * 0.001;
        const bassNorm = bass/255;

        for(let i=0; i<pos.count; i++) {
            vec.fromBufferAttribute(pos, i);
            vec.normalize();
            const dist = 1.8 + noise.noise3D(vec.x+time, vec.y+time, vec.z+time) * (0.3 + bassNorm*0.8);
            vec.multiplyScalar(dist);
            pos.setXYZ(i, vec.x, vec.y, vec.z);
        }
        geometry.computeVertexNormals();
        pos.needsUpdate = true;
        
        mesh.rotation.y += 0.002 + bassNorm*0.01;
        mesh.rotation.z += 0.002;
        renderer.render(scene, camera);
    }
    
    window.addEventListener('resize', () => {
        renderer.setSize(window.innerWidth, window.innerHeight);
        camera.aspect = window.innerWidth/window.innerHeight;
        camera.updateProjectionMatrix();
    });
    
    init3D();
</script>
</body>
</html>
