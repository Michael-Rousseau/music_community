<?php
ob_start();
?>

    <div class="auth-container">
        <h1>Connexion</h1>

        <?php if (!empty($message)): ?>
            <div style="background:#f8d7da; color:#721c24; padding:10px; border-radius:8px; margin-bottom:20px;">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form action="<?= $basePath?>/login" method="POST">
            <div class="form-group">
                <input type="email" name="email" placeholder="Email" required>
            </div>
            <div class="form-group">
                <input type="password" name="password" placeholder="Mot de passe" required>
            </div>
            <button type="submit">Connexion</button>
        </form>

        <p style="margin-top:20px; color:#666;">
            Pas encore de compte ? <a href="<?= $basePath?>/register" style="color:var(--dark); font-weight:bold;">Inscrivez-vous</a>
        </p>
         <p style="margin-top:20px; color:#666;">
            <a href="<?= $basePath?>/forgot-password" style="color:var(--dark); font-weight:bold;">Mot de passe oublié ?</a>
        </p>
    </div>
<?php
$content = ob_get_clean();
$title = "Connexion";
include __DIR__ . "/../general/layout.php";
?>
