<?php
ob_start();
?>
    <div class="auth-container">
        <h1>Inscription</h1>

        <?php if (!empty($message)): ?>
            <div style="background:#d4edda; color:#155724; padding:10px; border-radius:8px; margin-bottom:20px;">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form action="<?= $basePath?>/register" method="POST">
            <div class="form-group">
                <input type="email" name="email" placeholder="Email" required>
            </div>
            <div class="form-group">
                <input type="text" name="username" placeholder="Nom d'utilisateur" required>
            </div>
            <div class="form-group">
                <input type="password" name="password" placeholder="Mot de passe" required>
            </div>
            <button type="submit">S'inscrire</button>
        </form>

        <p style="margin-top:20px; color:#666;">
            Déjà un compte ? <a href="<?= $basePath?>/login" style="color:var(--dark); font-weight:bold;">Connectez-vous</a>
        </p>
    </div>

<?php
$content = ob_get_clean();
$title = "Inscription";
include __DIR__ . "/../general/layout.php";
?>