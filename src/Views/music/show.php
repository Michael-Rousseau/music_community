<?php ob_start(); ?>

    <div id="canvas-container"></div>

    <div class="hud-layer">
        
        <div class="top-bar" style="display:flex; justify-content:space-between; align-items:center;">
             <div>
                 <a href="<?= $basePath ?>/" class="btn btn-secondary" style="border-radius:50px; padding:8px 20px; background:var(--bg-card); color:var(--text-main);">
                    <i class="fas fa-arrow-left"></i> Retour
                 </a>
             </div>
             <div style="text-align:right;">
                <button id="themeToggle" class="theme-toggle themeToggle" title="Mode sombre/clair" aria-label="Toggle Dark Mode">
                    <i class="fas fa-moon"></i>
                </button>
             </div>
        </div>

        <div class="song-info-container" style="position: absolute; top: 20%; right: 5%; text-align: right;">
            <h1 class="song-title"><?= htmlspecialchars($music['title']); ?></h1>
            <div style="display:flex; align-items:center; justify-content:flex-end; gap:10px; margin-top:10px;">
                <?php if (!empty($music['avatar']) && $music['avatar'] !== 'default_avatar.png'): ?>
                    <img src="/uploads/avatars/<?= htmlspecialchars($music['avatar']); ?>" 
                         alt="<?= htmlspecialchars($music['username']); ?>"
                         width="40" height="40"
                         style="width:40px; height:40px; border-radius:50%; object-fit:cover; border:3px solid var(--primary);">
                <?php else: ?>
                    <div style="width:40px; height:40px; border-radius:50%; background:var(--primary); color:#2D2828; display:flex; align-items:center; justify-content:center; font-size:1.2rem; font-weight:bold; border:3px solid var(--primary);">
                        <?= strtoupper(substr($music['username'], 0, 1)); ?>
                    </div>
                <?php endif; ?>
                <div style="color:var(--primary); letter-spacing:2px; font-weight:bold; font-size:1.2rem;">
                    <?= strtoupper(htmlspecialchars($music['username'])); ?>
                </div>
            </div>
            
            <div class="rating-stars" id="starContainer" style="margin-top:10px;">
                <?php for($i=1; $i<=5; $i++): ?>
                    <span class="star <?= ($i <= round($avgRating)) ? 'filled' : ''; ?>" onclick="submitRating(<?= $i; ?>)">★</span>
                <?php endfor; ?>
            </div>
        </div>

        <button class="btn btn-secondary comments-trigger" onclick="toggleDrawer(true)" style="position:absolute; bottom:120px; right:40px; background:var(--bg-card); color:var(--text-main);">
            <i class="fas fa-comment-alt"></i> Commentaires (<?= count($comments); ?>)
        </button>

        <div class="bottom-bar">
            <button id="playBtn" class="play-btn" aria-label="Play Music"><i class="fas fa-play"></i></button>
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
    
    <div class="comments-drawer <?= $openDrawer; ?>" id="drawer">
        <div class="drawer-header" style="padding:20px; border-bottom:1px solid var(--border-color); display:flex; justify-content:space-between;">
            <span style="font-weight:800; color:var(--text-main);">TIMELINE</span>
            <i class="fas fa-times" style="cursor:pointer; color:var(--text-main);" onclick="toggleDrawer(false)"></i>
        </div>
        
        <div class="comments-list" id="commentsList" style="flex:1; overflow-y:auto; padding:20px;">
            <?php foreach($comments as $c): ?>
                <div class="comment-item" onclick="jumpTo(<?= $c['timestamp']; ?>)" style="cursor:pointer;">
                    <div style="display:flex; align-items:center; gap:8px; margin-bottom:5px;">
                        <span class="comment-time">[<?= gmdate("i:s", $c['timestamp']); ?>]</span>
                        <div style="display:flex; align-items:center; gap:8px;">
                            <?php if (!empty($c['avatar']) && $c['avatar'] !== 'default_avatar.png'): ?>
                                <img src="/uploads/avatars/<?= htmlspecialchars($c['avatar']); ?>" 
                                    alt="<?= htmlspecialchars($c['username']); ?>"
                                    width="28" height="28"
                                    loading="lazy"
                                    style="width:28px; height:28px; border-radius:50%; object-fit:cover; border:2px solid var(--primary);">
                            <?php else: ?>
                                <div style="width:28px; height:28px; border-radius:50%; background:var(--primary); color:#2D2828; display:flex; align-items:center; justify-content:center; font-size:0.75rem; font-weight:bold;">
                                    <?= strtoupper(substr($c['username'], 0, 1)); ?>
                                </div>
                            <?php endif; ?>
                            <span style="font-weight:bold; color:var(--text-main);"><?= htmlspecialchars($c['username']); ?></span>
                        </div>
                    </div>
                    <div style="color:var(--text-muted); margin-top:3px; margin-left:8px;"><?= htmlspecialchars($c['content']); ?></div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="drawer-footer" style="padding:20px; border-top:1px solid var(--border-color);">
            <?php if($isUserLoggedIn): ?>
            <form method="POST" style="display:flex; gap:10px;">
                <input type="hidden" name="timestamp" id="timestampInput" value="0">
                <input type="text" name="comment" placeholder="Un avis ?" required autocomplete="off" style="margin:0;">
                <button type="submit" class="btn btn-primary" style="padding:10px 15px;">OK</button>
            </form>
            <?php else: ?>
                <a href="<?= $basePath ?>/login" style="color:var(--primary); font-weight:bold;">Connectez-vous pour commenter</a>
            <?php endif; ?>
        </div>
    </div>

    <form id="ratingForm" method="POST" style="display:none;">
        <input type="hidden" name="rating" id="ratingInput">
    </form>
    
    <audio id="audio" src="<?= $basePath ?>/music/stream?id=<?= $music['id']; ?>" crossorigin="anonymous"></audio>

    <script>
        const commentsData = <?= json_encode($comments); ?>;
    </script>

    <script type="module" src="<?= $basePath ?>/assets/player.js"></script>

<?php
$content = ob_get_clean();
$title = htmlspecialchars($music['title']);
include __DIR__ . "/../general/layout.php";
?>
