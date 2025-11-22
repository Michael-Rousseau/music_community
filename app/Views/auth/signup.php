<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Inscription</title>
</head>
<body>
<h2>Inscription</h2>
<?php if(!empty($message)) echo "<p>$message</p>"; ?>
<form method="POST" action="/signup">
    <input type="email" name="email" placeholder="Email" required><br>
    <input type="text" name="username" placeholder="Nom d'utilisateur" required><br>
    <input type="password" name="password" placeholder="Mot de passe" required><br>
    <button type="submit">S'inscrire</button>
</form>
<p>Déjà un compte ? <a href="/login">Connectez-vous</a></p>
</body>
</html>
