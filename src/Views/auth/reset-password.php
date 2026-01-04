<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Réinitialiser - Tempo</title>
    <link rel="stylesheet" href="<?= $basePath?>/assets/css/tempo.css">
</head>
<body>

<header>
<a href="<?= $basePath?>/" class="logo">
    <img src="<?= $basePath?>/assets/images/logo_tempo.png" alt="Tempo" width="150" height="50">
</a>
    <div style="display:flex; align-items:center;">
        <button id="themeToggle" class="theme-toggle"><i class="fas fa-moon"></i></button>
        <a href="/login" class="btn btn-primary">Connexion</a>
    </div>
</header>
<script src="/assets/js/tempo.js"></script>

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

</body>
</html>
