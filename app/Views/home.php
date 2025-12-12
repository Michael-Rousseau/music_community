<?php ob_start(); ?>

<div style="text-align:center; padding:40px 20px; background:white; margin-bottom:20px; border-radius:10px;">
    <h1>Découvrez les talents de demain 🎵</h1>
    <form method="GET" style="max-width:400px; margin:0 auto; display:flex; gap:10px;">
        <input type="text" name="q" placeholder="Rechercher un titre..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
        <button type="submit" class="primary">🔍</button>
    </form>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px;">
    <?php foreach ($musics as $m): ?>
        <div class="card" style="padding:0; overflow:hidden;">
            <div style="height:150px; background:#eee; display:flex; align-items:center; justify-content:center; overflow:hidden;">
                <?php $img = $m['image'] ? $m['image'] : 'default.jpg'; ?>
                <img src="<?= BASE_URL ?>/uploads/images/<?= htmlspecialchars($img) ?>" style="width:100%; height:100%; object-fit:cover;">
            </div>
            <div style="padding:15px;">
                <h3 style="margin:0 0 10px; font-size:1.1rem;"><?= htmlspecialchars($m['title']) ?></h3>
                <div style="font-size:0.9rem; color:#666; margin-bottom:15px;">
                    👤 <?= htmlspecialchars($m['username']) ?>
                </div>
                <a href="<?= BASE_URL ?>/m/<?= $m['id'] ?>" class="btn primary" style="width:100%; text-align:center;">Écouter ▶</a>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php
$content = ob_get_clean();
$title = "Accueil";
include __DIR__ . '/layout.php';
?>
