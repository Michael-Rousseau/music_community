<?php ob_start(); ?>
    <div class="auth-container" style="text-align:center;">
        <h1><?php echo $success ? 'Succès ✅' : 'Erreur ⚠️'; ?></h1>
        <p><?php echo htmlspecialchars($message); ?></p>
        <a href="<?= $basePath?>/login" class="btn btn-primary">Se connecter</a>
    </div>
<?php
$content = ob_get_clean();
$title = "Vérification de l'email";
include __DIR__ . "/../general/layout.php";
?>