<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mot de Passe oublié - Tempo</title>
    <link rel="stylesheet" href="<?= $basePath?>/assets/css/tempo.css">
</head>
<body>

<header>
<a href="<?= $basePath?>/" class="logo">
    <img src="<?= $basePath?>/assets/images/logo_tempo.png" alt="Tempo" width="150" height="50">
</a>
    <div style="display:flex; align-items:center;">
        <button id="themeToggle" class="theme-toggle"><i class="fas fa-moon"></i></button>
        <a href="<?= $basePath ?>/login" class="btn btn-primary">Connexion</a>
    </div>
</header>
<script src="/assets/js/tempo.js"></script>

    <div class="auth-container">
        <h1>Mot de passe oublié!</h1>

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

</body>
</html>
