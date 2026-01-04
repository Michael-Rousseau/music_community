<div class="auth-container" style="text-align:center;">
    <h1><?php echo $success ? 'Succès ✅' : 'Erreur ⚠️'; ?></h1>
    <p><?php echo htmlspecialchars($message); ?></p>
    <a href="/login" class="btn btn-primary">Se connecter</a>
</div>
