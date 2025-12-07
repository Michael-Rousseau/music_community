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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <style>
        body { font-family: 'Segoe UI', sans-serif; margin: 0; background: #1a1a2e; color: white; }
        
        .container { max-width: 900px; margin: 0 auto; padding: 20px; position: relative; z-index: 2; }
        
        .header { text-align: center; margin-bottom: 20px; }
        
        #visualizer-canvas {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1; 
            opacity: 0.6; 
        }

        .player-card {
            background: rgba(255, 255, 255, 0.1); 
            backdrop-filter: blur(10px);
            padding: 20px;
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            margin-bottom: 20px;
        }

        audio { width: 100%; margin: 20px 0; outline: none; }
        
        .stars { color: #ffc107; font-size: 1.5rem; }
        
        .comment-box { margin-top: 30px; background: rgba(0,0,0,0.5); padding: 20px; border-radius: 10px; }
        .comment { background: rgba(255,255,255,0.1); padding: 10px; margin-bottom: 10px; border-radius: 5px; }
        
        .btn { background: #007bff; color: white; border: none; padding: 10px; cursor: pointer; border-radius: 5px; }
        .btn-back { display: inline-block; color: white; text-decoration: none; margin-bottom: 15px; font-weight: bold; }
        
        input[type="text"] { width: 70%; padding: 10px; border-radius: 5px; border: none; }
    </style>
</head>
<body>

<div id="visualizer-canvas"></div>

<div class="container">
    <a href="index.php" class="btn-back">← Retour à l'accueil</a>
    
    <div class="player-card">
        <div class="header">
            <h1 style="margin:0;"><?php echo htmlspecialchars($music['title']); ?></h1>
            <p style="color:#ccc;">Par <strong><?php echo htmlspecialchars($music['username']); ?></strong></p>
            
            <div class="rating">
                <span class="stars">★ <?php echo $avgRating ?: '-'; ?>/5</span>
                <?php if(isset($_SESSION['user_id'])): ?>
                <form method="POST" style="display:inline-block;">
                    <select name="rating" onchange="this.form.submit()" style="padding:5px; border-radius:5px;">
                        <option value="">Noter</option>
                        <option value="5">5 ★</option>
                        <option value="4">4 ★</option>
                        <option value="3">3 ★</option>
                    </select>
                </form>
                <?php endif; ?>
            </div>
        </div>

        <audio id="audio-player" controls crossorigin="anonymous">
            <source src="uploads/mp3/<?php echo htmlspecialchars($music['filename']); ?>" type="audio/mpeg">
            Votre navigateur ne supporte pas l'audio.
        </audio>

        <p><?php echo nl2br(htmlspecialchars($music['description'])); ?></p>
    </div>

    <div class="comment-box">
        <h3>Commentaires (<?php echo count($comments); ?>)</h3>
        
        <?php if(isset($_SESSION['user_id'])): ?>
        <form method="POST" style="margin-bottom: 20px; display:flex; gap:10px;">
            <input type="text" name="comment" placeholder="Écrire un commentaire..." required>
            <button type="submit" class="btn">Envoyer</button>
        </form>
        <?php else: ?>
            <p><a href="connexion.php" style="color:#007bff">Connectez-vous</a> pour commenter.</p>
        <?php endif; ?>

        <?php foreach($comments as $c): ?>
            <div class="comment">
                <strong><?php echo htmlspecialchars($c['username']); ?></strong> 
                <small style="color:#aaa; float:right;"><?php echo date('d/m H:i', strtotime($c['created_at'])); ?></small>
                <p style="margin:5px 0 0;"><?php echo htmlspecialchars($c['content']); ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
    let scene, camera, renderer, sphere, analyser, dataArray;
    let isAudioContextSetup = false;

    function initThreeJS() {
        const container = document.getElementById('visualizer-canvas');
        
        scene = new THREE.Scene();
        camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
        camera.position.z = 5;

        renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true }); 
        renderer.setSize(window.innerWidth, window.innerHeight);
        container.appendChild(renderer.domElement);

        const geometry = new THREE.IcosahedronGeometry(2, 2);
        const material = new THREE.MeshLambertMaterial({ 
            color: 0x007bff, 
            wireframe: true 
        });
        sphere = new THREE.Mesh(geometry, material);
        scene.add(sphere);

        const ambientLight = new THREE.AmbientLight(0xffffff, 0.5);
        scene.add(ambientLight);
        const pointLight = new THREE.PointLight(0xffffff, 1);
        pointLight.position.set(10, 10, 10);
        scene.add(pointLight);

        window.addEventListener('resize', () => {
            renderer.setSize(window.innerWidth, window.innerHeight);
            camera.aspect = window.innerWidth / window.innerHeight;
            camera.updateProjectionMatrix();
        });

        animate();
    }

    function setupAudioContext() {
        if (isAudioContextSetup) return;

        const audio = document.getElementById('audio-player');
        
        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        const source = audioCtx.createMediaElementSource(audio);
        
        analyser = audioCtx.createAnalyser();
        analyser.fftSize = 256; 
        
        source.connect(analyser);
        analyser.connect(audioCtx.destination); 
        
        dataArray = new Uint8Array(analyser.frequencyBinCount);
        isAudioContextSetup = true;

        if (audioCtx.state === 'suspended') {
            audioCtx.resume();
        }
    }

    function animate() {
        requestAnimationFrame(animate);

        sphere.rotation.x += 0.005;
        sphere.rotation.y += 0.005;

        if (analyser) {
            analyser.getByteFrequencyData(dataArray);

            let lowerHalfArray = dataArray.slice(0, (dataArray.length/2) - 1);
            let lowerMax = lowerHalfArray.reduce((a, b) => Math.max(a, b), 0);
            
            let scale = 1 + (lowerMax / 255) * 0.8;
            
            sphere.scale.set(scale, scale, scale);
            
            sphere.material.color.setHSL(0.6 + (lowerMax/255)*0.2, 0.8, 0.5);
        }

        renderer.render(scene, camera);
    }

    initThreeJS();

    document.getElementById('audio-player').addEventListener('play', () => {
        setupAudioContext();
    });

</script>
</body>
</html>
