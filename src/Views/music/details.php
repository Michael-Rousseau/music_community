<style>
    .music-page-container {
        max-width: 900px;
        margin: 40px auto;
        padding: 0 20px 60px;
    }

    .song-header {
        background: var(--bg-card);
        border-radius: 20px;
        padding: 40px;
        margin-bottom: 30px;
        box-shadow: 0 10px 30px var(--shadow);
        text-align: center;
    }

    .song-header h1 {
        font-family: 'Orbitron', sans-serif;
        text-transform: uppercase;
        letter-spacing: 2px;
        margin: 0 0 20px 0;
        color: var(--text-main);
    }

    .artist-info {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 15px;
        margin-bottom: 30px;
    }

    .artist-info img, .artist-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        border: 3px solid var(--primary);
        object-fit: cover;
    }

    .artist-avatar {
        background: var(--primary);
        color: #2D2828;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        font-weight: bold;
    }

    .artist-name {
        color: var(--primary);
        font-weight: bold;
        font-size: 1.2rem;
        letter-spacing: 1px;
        text-transform: uppercase;
    }

    .player-buttons {
        display: flex;
        gap: 15px;
        justify-content: center;
        flex-wrap: wrap;
        margin-bottom: 20px;
    }

    .rating-section {
        padding: 20px;
        text-align: center;
    }

    .rating-stars {
        display: inline-flex;
        gap: 5px;
        font-size: 2rem;
        cursor: pointer;
    }

    .star {
        color: var(--border-color);
        transition: color 0.2s;
    }

    .star.filled {
        color: #FFD700;
    }

    .star:hover {
        color: #FFD700;
    }

    .comments-section {
        background: var(--bg-card);
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 10px 30px var(--shadow);
    }

    .comments-section h2 {
        font-family: 'Orbitron', sans-serif;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin: 0 0 25px 0;
        color: var(--text-main);
        font-size: 1.3rem;
    }

    .comment-item {
        padding: 20px;
        border-bottom: 1px solid var(--border-color);
        transition: background 0.2s;
    }

    .comment-item:last-child {
        border-bottom: none;
    }

    .comment-item:hover {
        background: var(--bg-input);
        border-radius: 12px;
    }

    .comment-time {
        color: var(--primary);
        font-weight: bold;
        font-family: monospace;
        font-size: 0.9rem;
    }

    .comment-form {
        margin-top: 30px;
        padding-top: 30px;
        border-top: 2px solid var(--border-color);
    }

    .comment-form input {
        width: 100%;
        padding: 12px 15px;
        background: var(--bg-input);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        color: var(--text-main);
        font-family: 'Rajdhani', sans-serif;
        box-sizing: border-box;
    }
</style>

<div class="music-page-container">
    <div class="song-header">
        <h1><?= htmlspecialchars($music['title']); ?></h1>

        <div class="artist-info">
            <?php if (!empty($music['avatar']) && $music['avatar'] !== 'default_avatar.png'): ?>
                <img src="/uploads/avatars/<?= htmlspecialchars($music['avatar']); ?>"
                     alt="<?= htmlspecialchars($music['username']); ?>">
            <?php else: ?>
                <div class="artist-avatar">
                    <?= strtoupper(substr($music['username'], 0, 1)); ?>
                </div>
            <?php endif; ?>
            <div class="artist-name">
                <?= htmlspecialchars($music['username']); ?>
            </div>
        </div>

        <div class="player-buttons">
            <button
                onclick="loadSong(<?= $music['id']; ?>, '<?= addslashes($music['title']); ?>', '<?= addslashes($music['username']); ?>')"
                class="btn btn-primary"
                style="font-size: 1.1rem; padding: 15px 40px;">
                <i class="fas fa-play"></i> Load in Player
            </button>

            <a href="/music?id=<?= $music['id']; ?>"
               class="btn btn-secondary"
               style="font-size: 1.1rem; padding: 15px 40px;">
                <i class="fas fa-expand"></i> Open Visualization
            </a>
        </div>

        <div class="rating-section">
            <div class="rating-stars" id="starContainer">
                <?php for($i=1; $i<=5; $i++): ?>
                    <span class="star <?= ($i <= round($avgRating)) ? 'filled' : ''; ?>" onclick="submitRating(<?= $i; ?>)">★</span>
                <?php endfor; ?>
            </div>
            <div style="color: var(--text-muted); margin-top: 10px;">
                <?= number_format($avgRating, 1); ?> / 5
            </div>
        </div>
    </div>

    <div class="comments-section">
        <h2>Comments (<?= count($comments); ?>)</h2>

        <?php if (count($comments) > 0): ?>
            <?php foreach($comments as $c): ?>
                <div class="comment-item">
                    <div style="display:flex; align-items:center; gap:10px; margin-bottom:8px;">
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
                    <div style="color:var(--text-muted); margin-left:8px; line-height:1.6;">
                        <?= htmlspecialchars($c['content']); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align:center; color:var(--text-muted); padding:40px 0;">
                No comments yet. Be the first to comment!
            </p>
        <?php endif; ?>

        <?php if($isUserLoggedIn): ?>
        <div class="comment-form">
            <form method="POST" style="display:flex; gap:10px; align-items:center;">
                <input type="hidden" name="timestamp" id="timestampInput" value="0">
                <input type="text" name="comment" placeholder="Add a comment..." required autocomplete="off">
                <button type="submit" class="btn btn-primary" style="padding:12px 25px; flex-shrink:0;">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </form>
            <p style="color:var(--text-muted); font-size:0.85rem; margin-top:8px;">
                Tip: Comments are timestamped at the current playback position
            </p>
        </div>
        <?php else: ?>
        <div style="text-align:center; padding:20px; margin-top:20px; background:var(--bg-input); border-radius:12px;">
            <a href="/login" style="color:var(--primary); font-weight:bold;">Login to comment</a>
        </div>
        <?php endif; ?>
    </div>
</div>

<form id="ratingForm" method="POST" style="display:none;">
    <input type="hidden" name="rating" id="ratingInput">
</form>

<script>
// rating submission
function submitRating(rating) {
    document.getElementById('ratingInput').value = rating;
    document.getElementById('ratingForm').submit();
}

// update timestamp for comment based on current player time
document.addEventListener('DOMContentLoaded', () => {
    const audio = document.getElementById('audio');
    const timestampInput = document.getElementById('timestampInput');

    if (audio && timestampInput) {
        // update timestamp input when form is about to submit
        const commentForm = timestampInput.closest('form');
        if (commentForm) {
            commentForm.addEventListener('submit', () => {
                timestampInput.value = Math.floor(audio.currentTime || 0);
            });
        }
    }
});
</script>
