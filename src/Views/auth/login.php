<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion - Tempo</title>
    <link rel="stylesheet" href="/assets/css/tempo.css">
</head>
<body>

<header>
<a href="/" class="logo">
    <img src="/assets/images/logo_tempo.png" alt="Tempo" width="150" height="50">
</a>
    <div style="display:flex; align-items:center;">
        <button id="themeToggle" class="theme-toggle"><i class="fas fa-moon"></i></button>
        <a href="/login" class="btn btn-primary">Connexion</a>
    </div>
</header>
<script src="/assets/js/tempo.js"></script>

    <div class="auth-container">
        <h1>Connexion</h1>

        <?php if (!empty($message)): ?>
            <div style="background:#f8d7da; color:#721c24; padding:10px; border-radius:8px; margin-bottom:20px;">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form action="/login" method="POST">
            <div class="form-group">
                <input type="email" name="email" placeholder="Email" required>
            </div>
            <div class="form-group">
                <input type="password" name="password" placeholder="Mot de passe" required>
            </div>
            <button type="submit">Connexion</button>
        </form>

        <p style="margin-top:20px; color:#666;">
            Pas encore de compte ? <a href="/register" style="color:var(--dark); font-weight:bold;">Inscrivez-vous</a>
        </p>
    </div>

</body>
</html>
