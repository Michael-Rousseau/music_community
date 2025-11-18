<?php
session_start();
require_once '../config/db.php';

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $message = 'Champs requis.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(); 

            if ($user && password_verify($password, $user['password'])) {
                if ($user['token'] === NULL) {
                    // Connexion OK
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['username'];
                    $_SESSION['user_role'] = $user['role'];
                    
                    // Redirection vers la page d'accueil membre
                    header("Location: dashboard.php");
                    exit;
                } else {
                    $message = 'Compte non validé. Vérifiez vos emails.';
                }
            } else {
                $message = 'Identifiants incorrects.';
            }
        } catch (PDOException $e) {
            error_log("Login DB Error: " . $e->getMessage());
            $message = 'Erreur technique.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <style>
        body { font-family: system-ui, sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; background-color: #f4f4f9; margin: 0; }
        .container { background: #fff; padding: 2rem; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        input { width: 100%; padding: 10px; margin-top: 5px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; }
        .message { padding: 10px; margin-bottom: 20px; text-align: center; border-radius: 5px; background: #f8d7da; color: #721c24; }
        .info { text-align: center; margin-top: 15px; font-size: 0.9em; }
    </style>
</head>
<body>
    <div class="container">
        <h2 style="text-align:center;">Connexion</h2>
        <?php if (!empty($message)): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <form method="POST">
            <label>Email :</label>
            <input type="email" name="email" required>
            <label>Mot de passe :</label>
            <input type="password" name="password" required>
            <button type="submit">Se connecter</button>
        </form>
        <div class="info"><a href="inscription.php">Créer un compte</a></div>
    </div>
</body>
</html>
