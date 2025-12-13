<?php
ob_start();
?>

<div class="wrapper stack" style="text-align:center; padding: 3rem 0;">
    
    <img src="<?= rtrim(BASE_URL, '/') ?>/assets/images/logo_tempo.png" alt="Tempo Logo" style="width: 150px; margin-bottom: 20px;">

    <h1>🎵 MusicShare</h1>
    <p>Rejoignez la communauté, partagez vos MP3 et découvrez de nouveaux talents.</p>

    <div class="btn-container" style="margin-top:20px; margin-bottom: 50px;">
        <?php if(\Core\Auth::check()): ?>
            <a href="<?= rtrim(BASE_URL, '/') ?>/profile" class="btn primary">Mon Tableau de bord</a>
        <?php else: ?>
            <a href="<?= rtrim(BASE_URL, '/') ?>/signup" class="btn primary">Créer un compte</a>
            <a href="<?= rtrim(BASE_URL, '/') ?>/login" class="btn btn-secondary" style="margin-left:10px;">Se connecter</a>
        <?php endif; ?>
    </div>

    <h2 style="text-align:left; border-bottom:1px solid #ddd; padding-bottom:10px;">Derniers ajouts</h2>
    
    <div class="music-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; text-align: left;">
        <?php if (!empty($musics)): ?>
            <?php foreach ($musics as $music): ?>
                <div class="card" style="padding: 15px; border: 1px solid #eee; border-radius: 8px; background: white; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                    <div style="margin-bottom: 10px;">
                        <span style="font-size: 0.8rem; color: #888; text-transform: uppercase;">
                            <?= htmlspecialchars($music['username']) ?>
                        </span>
                        <h3 style="margin: 5px 0; font-size: 1.2rem;">
                            <a href="<?= rtrim(BASE_URL, '/') ?>/m/<?= $music['id'] ?>" style="color: #2D2828; text-decoration: none;">
                                <?= htmlspecialchars($music['title']) ?>
                            </a>
                        </h3>
                    </div>
                    
                    <audio controls style="width: 100%; height: 30px;">
                        <source src="<?= rtrim(BASE_URL, '/') ?>/uploads/mp3/<?= htmlspecialchars($music['filename']) ?>" type="audio/mpeg">
                        Votre navigateur ne supporte pas l'audio.
                    </audio>

                    <div style="margin-top: 10px; text-align: right;">
                        <a href="<?= rtrim(BASE_URL, '/') ?>/m/<?= $music['id'] ?>" class="btn-light" style="font-size: 0.8rem; padding: 5px 10px; border-radius: 4px; text-decoration:none;">
                            Voir & Commenter
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Aucune musique publique pour le moment. Soyez le premier à poster !</p>
        <?php endif; ?>
    </div>

</div>

<?php
$content = ob_get_clean();
$title = "Bienvenue - MusicShare";
include __DIR__ . '/layout.php';
?>
