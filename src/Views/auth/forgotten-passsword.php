<?php
ob_start();
?>

    <div class="auth-container">
        <h1>Mot de passe oublié</h1>

        <?php if (!empty($message)): ?>
            <div style="background:#f8d7da; color:#721c24; padding:10px; border-radius:8px; margin-bottom:20px;">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form action="<?= $basePath?>/forgot-password" method="POST">
            <div class="form-group">
                <input type="email" name="email" placeholder="Email" required>
            </div>
            <button type="submit">Envoyer un mail</button>
        </form>

    </div>

<?php
$content = ob_get_clean();
$title = "Mot de passe oublié";
include __DIR__ . "/../general/layout.php";
?>
