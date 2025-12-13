<?php
ob_start(); 
?>

<div class="wrapper stack" style="text-align:center; padding: 4rem 0;">
    <h1>🎵 MusicShare</h1>
    <p>Rejoignez la communauté, partagez vos MP3 et découvrez de nouveaux talents.</p>

    <div class="btn-container" style="margin-top:20px;">
        <?php if(\Core\Auth::check()): ?>
            <a href="<?= rtrim(BASE_URL, '/') ?>/profile" class="btn primary">Mon Tableau de bord</a>
        <?php else: ?>
            <a href="<?= rtrim(BASE_URL, '/') ?>/signup" class="btn primary">Créer un compte</a>
            <a href="<?= rtrim(BASE_URL, '/') ?>/login" class="btn btn-secondary" style="margin-left:10px;">Se connecter</a>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = "Bienvenue - MusicShare";
include __DIR__ . '/layout.php';
?>
