<!DOCTYPE html>
<html>
<head><title>Vérification</title><link rel="stylesheet" href="<?= $basePath?>/assets/css/tempo.css"></head>
<body>
    <div class="auth-container" style="text-align:center;">
        <h1><?php echo $success ? 'Succès ✅' : 'Erreur ⚠️'; ?></h1>
        <p><?php echo htmlspecialchars($message); ?></p>
        <a href="<?= $basePath?>/login" class="btn btn-primary">Se connecter</a>
    </div>
</body>
</html>
