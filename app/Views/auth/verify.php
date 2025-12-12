<?php
ob_start();
?>
<h1>Vérification du compte</h1>
<p><?php echo htmlspecialchars($message); ?></p>
<a href="<?=  BASE_URL ?>/login" class="btn primary">Se connecter</a>
<?php
$content = ob_get_clean();
$title = "Vérification";
include __DIR__ . '/../layout.php';
?>
