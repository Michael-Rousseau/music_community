<?php ob_start(); ?>

<div class="container" style="max-width: 1000px; margin: 0 auto;">
    
    <div class="card" style="text-align:center; margin-bottom: 20px;">
        <div style="width:80px; height:80px; background:#bd00ff; color:white; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:2rem; margin:0 auto 10px; font-weight:bold;">
            <?= strtoupper(substr($userName, 0, 1)) ?>
        </div>
        <h1><?= htmlspecialchars($userName) ?></h1>
        <p class="text-muted"><?= ucfirst($userRole) ?></p>
        <a href="<?= BASE_URL ?>/m/new" class="btn primary">Uploader une musique 🚀</a>
    </div>

    <div class="card">
        <h2>Mes Musiques (<?= count($my_musics) ?>)</h2>
        <?php if (empty($my_musics)): ?>
            <p style="text-align:center; color:#888;">Aucune musique pour le moment.</p>
        <?php else: ?>
            <div class="stack">
            <?php foreach ($my_musics as $music): ?>
                <div style="display:flex; justify-content:space-between; align-items:center; background:#f9f9f9; padding:15px; border-radius:8px; width:100%; border-left: 4px solid #bd00ff;">
                    <div>
                        <h3 style="margin:0; font-size:1.1rem;"><?= htmlspecialchars($music['title']) ?></h3>
                        <span style="font-size:0.85rem; color:#666;">
                            <?= ($music['visibility'] == 'public') ? '🌍 Public' : '🔒 Privé'; ?> 
                            • <?= date('d/m/Y', strtotime($music['created_at'])); ?>
                        </span>
                    </div>
                    <div>
                        <a href="<?= BASE_URL ?>/m/<?= $music['id'] ?>" class="btn-secondary" style="font-size:0.8rem;">▶ Écouter</a>
                        <a href="<?= BASE_URL ?>/m/edit/<?= $music['id'] ?>" style="margin-left:10px; text-decoration:none;">✏️</a>
                        <form action="<?= BASE_URL ?>/m/delete/<?= $music['id'] ?>" method="POST" style="display:inline;" onsubmit="return confirm('Confirmer la suppression ?');">
                            <button type="submit" style="background:none; border:none; color:red; cursor:pointer; font-size:1.2rem;">🗑️</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = "Mon Espace";
include __DIR__ . '/layout.php';
?>
