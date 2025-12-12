<?php
ob_start();
?>
<h1>Inscription</h1>
<?php if(!empty($message)) echo "<p>$message</p>"; ?>
<form method="POST" action="<?= BASE_URL ?>/signup" class="stack">
    <input type="email" name="email" placeholder="Email" required><br>
    <input type="text" name="username" placeholder="Nom d'utilisateur" required><br>
    <input type="password" name="password" placeholder="Mot de passe" required><br>
<button type="submit" class="btn primary">S'inscrire</button>
</form>
<p>Déjà un compte ? <a href="<?= BASE_URL ?>/login">Connectez-vous</a></p>
<?php
$content = ob_get_clean();
$title = "Inscription";
include __DIR__ . '/../layout.php';
?>
