<?php
ob_start();
?>
<h1>Connexion</h1>
<?php if(!empty($message)) echo "<p>$message</p>"; ?>
<div>
    <form method="POST" action="<?= BASE_URL ?>/login" class="stack">
        <input type="email" name="email" placeholder="Email" required><br>
        <input type="password" name="password" placeholder="Mot de passe" required><br>
        <button type="submit" class="btn primary">Connexion</button>
    </form>
</div>
<p>Pas encore de compte ? <a href="<?= BASE_URL ?>/signup">Inscrivez-vous</a></p>

<?php
$content = ob_get_clean();
$title = "Connexion";
include __DIR__ . '/../layout.php';
?>
