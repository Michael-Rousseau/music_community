<?php
ob_start();
?>
    <div class="hero">
        <h1>Accueil</h1>
        <form class="search-bar" action="<?= $basePath ?>/" method="GET" style="max-width: 400px; margin: 0 auto;">
            <input type="text" name="q" placeholder="Rechercher un titre..." aria-label="Rechercher" value="<?= htmlspecialchars($search ?? ''); ?>">
        </form>
    </div>

    <div class="grid">
        <?php if(isset($musics)): foreach ($musics as $m): ?>
            <div class="card">
                <div class="card-img">
                    <?php if(!empty($m['image']) && $m['image'] !== 'default_image.png'): ?>
                        <img src="<?= $basePath ?>/uploads/images/<?= htmlspecialchars($m['image']) ?>" 
                            alt="Cover de <?= htmlspecialchars($m['title']); ?>"
                            loading="lazy"
                            width="250" height="180"
                            style="width:100%; height:100%; object-fit:cover;">
                    <?php else: ?>
                        💿
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <h2 class="card-title"><?= htmlspecialchars($m['title']); ?></h2>
                    
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
                    <a href="<?= $basePath ?>/music?id=<?= $m['id']; ?>" class="btn btn-primary" style="display:block; text-align:center; margin-top:auto;" aria-label="Écouter <?= htmlspecialchars($m['title']); ?>">Écouter ▶</a>
                </div>
            </div>
        <?php endforeach; endif; ?>
    </div>

<?php
$content = ob_get_clean();
$title = "Accueil";
include __DIR__ . "/../general/layout.php";
?>
