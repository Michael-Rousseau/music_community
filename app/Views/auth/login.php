<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
</head>
<body>
<h2>Connexion</h2>
<?php if(!empty($message)) echo "<p>$message</p>"; ?>
<form method="POST" action="/login">
    <input type="email" name="email" placeholder="Email" required><br>
    <input type="password" name="password" placeholder="Mot de passe" required><br>
    <button type="submit">Connexion</button>
</form>
<p>Pas encore de compte ? <a href="/signup">Inscrivez-vous</a></p>
</body>
</html>
