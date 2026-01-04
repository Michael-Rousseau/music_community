<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription - Tempo</title>
    <link rel="stylesheet" href="<?= $basePath?>/assets/css/tempo.css">
</head>
<body>

    <header>
<a href="<?= $basePath?>/" class="logo">
    <img src="<?= $basePath?>/assets/images/logo_tempo.png" alt="Tempo" width="150" height="50">
</a>
        <a href="<?= $basePath?>/login" class="btn btn-primary">Connexion</a>
    </header>

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

</body>
</html>
