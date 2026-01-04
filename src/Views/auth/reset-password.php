<?php
ob_start();
?>

    <div class="auth-container">
        <h1>Réinitialiser le mot de passe</h1>

        
        <?php if ($success): ?>
            <?php if (!empty($message)): ?>
                <div style="background:#d7f8de; color:#07410d; padding:10px; border-radius:8px; margin-bottom:20px;">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            <a href="<?= $basePath?>/login" class="btn btn-primary">Se connecter</a>
        <?php else: ?>
            <?php if (!empty($message)): ?>
                <div style="background:#f8d7da; color:#721c24; padding:10px; border-radius:8px; margin-bottom:20px;">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form action="<?= $basePath?>/reset-password?token=<?= $token ?>" method="POST">
                <div class="form-group">
                    <input type="password" name="password" placeholder="Nouveau mot de passe" required>
                </div>
                <button type="submit">Réinitialiser</button>
            </form>
        <?php endif; ?>

    </div>

<?php
$content = ob_get_clean();
$title = "Réinitialiser le mot de passe";
include __DIR__ . "/../general/layout.php";
?>