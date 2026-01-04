<style>
    .hero { text-align: center; padding: 60px 20px; }
    h1 { font-family: 'Orbitron', sans-serif; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 20px; }

    .search-bar input { width: 100%; padding: 15px; background: var(--bg-input); color: var(--text-main); border: 1px solid var(--border-color); border-radius: 50px; font-family: 'Rajdhani', sans-serif; font-size: 1.1rem; box-sizing: border-box; }

    .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 30px; max-width: 1200px; margin: 0 auto; padding: 0 20px 60px; }
    .card { background: var(--bg-card); border-radius: 20px; overflow: hidden; box-shadow: 0 10px 30px var(--shadow); width: 100%; max-width: 350px; margin: 0 auto; display: flex; flex-direction: column; }
    .card-img { height: 180px; background: var(--bg-input); display: flex; align-items: center; justify-content: center; font-size: 3rem; color: var(--text-muted); overflow:hidden; }
    .card-body { padding: 20px; flex-grow: 1; display:flex; flex-direction:column; }
    .card-title { font-weight: 800; margin: 0 0 5px 0; font-size: 1.2rem; color: var(--text-main); }
    .card-user { color: var(--text-muted); font-size: 0.9rem; margin-bottom: 15px; }
    .card-title-link { color: inherit; text-decoration: none; }
    .card-title-link:hover { color: var(--primary); }
</style>

<div class="hero">
    <h1>Accueil</h1>
    <form class="search-bar" action="/" method="GET" style="max-width: 400px; margin: 0 auto;">
        <input type="text" name="q" placeholder="Rechercher un titre..." aria-label="Rechercher" value="<?= htmlspecialchars($search ?? ''); ?>">
    </form>
</div>

<div class="grid">
    <?php if(isset($musics)): foreach ($musics as $m): ?>
        <div class="card">
            <a href="/music?id=<?= $m['id']; ?>"
               onclick="loadSong(<?= $m['id']; ?>, '<?= addslashes($m['title']); ?>', '<?= addslashes($m['username']); ?>');"
               class="card-img" style="text-decoration: none;">
                <?php if(!empty($m['image']) && $m['image'] !== 'default_image.png'): ?>
                    <img src="/uploads/images/<?= htmlspecialchars($m['image']) ?>"
                        alt="Cover de <?= htmlspecialchars($m['title']); ?>"
                        loading="lazy"
                        width="250" height="180"
                        style="width:100%; height:100%; object-fit:cover;">
                <?php else: ?>
                    💿
                <?php endif; ?>
            </a>
            <div class="card-body">
                <h2 class="card-title">
                    <a href="/music?id=<?= $m['id']; ?>"
                       onclick="loadSong(<?= $m['id']; ?>, '<?= addslashes($m['title']); ?>', '<?= addslashes($m['username']); ?>');"
                       class="card-title-link">
                        <?= htmlspecialchars($m['title']); ?>
                    </a>
                </h2>

                <div class="card-user" style="display:flex; align-items:center; gap:8px;">
                    <div style="width:24px; height:24px; border-radius:50%; background:var(--primary); overflow:hidden;">
                        <?php if (!empty($m['avatar']) && $m['avatar'] !== 'default_avatar.png'): ?>
                            <img src="/uploads/avatars/<?= htmlspecialchars($m['avatar']); ?>"
                                alt="<?= htmlspecialchars($m['username']); ?>"
                                width="24" height="24"
                                style="width:100%; height:100%; object-fit:cover;">
                        <?php else: ?>
                            <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; font-weight:bold; color:#222;">
                                <?= strtoupper(substr($m['username'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <span><?= htmlspecialchars($m['username']); ?></span>
                </div>

                <button
                    onclick="loadSong(<?= $m['id']; ?>, '<?= addslashes($m['title']); ?>', '<?= addslashes($m['username']); ?>')"
                    class="btn btn-primary"
                    style="display:block; text-align:center; margin-top:auto; width:100%;"
                    aria-label="Écouter <?= htmlspecialchars($m['title']); ?>">
                    Écouter ▶
                </button>
            </div>
        </div>
    <?php endforeach; endif; ?>
</div>
