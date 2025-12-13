<?php
session_start();
require_once '../config/db.php';

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // ... (Your existing login logic remains the same) ...
    if (empty($_POST['email']) || empty($_POST['password'])) {
        $message = 'Erreur : Email et mot de passe requis.';
    } else {
        $email_posted = trim($_POST['email']);
        $password_posted = $_POST['password'];

        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email_posted]);
            $user = $stmt->fetch(); 

            if ($user && password_verify($password_posted, $user['password'])) {
                if ($user['token'] === NULL) {
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['username']; 
                    $_SESSION['user_role'] = $user['role'];
                    header("Location: index.php"); 
                    exit;
                } else {
                    $message = 'Erreur : Compte non validé.';
                }
            } else {
                $message = 'Erreur : Identifiants incorrects.';
            }
        } catch (PDOException $e) {
            $message = 'Erreur technique.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion - Tempo</title>
    <link rel="stylesheet" href="assets/css/tempo.css">
</head>
<body>

<header>
    <a href="index.php" class="logo"><img src="assets/images/logo_tempo.png" alt="Tempo"></a>
    <div style="display:flex; align-items:center;">
        <button id="themeToggle" class="theme-toggle"><i class="fas fa-moon"></i></button>
        <a href="connexion.php" class="btn btn-primary">Connexion</a>
    </div>
</header>
<script src="assets/js/tempo.js"></script>

    <div class="auth-container">
        <h1>Connexion</h1>

        <?php if (!empty($message)): ?>
            <div style="background:#f8d7da; color:#721c24; padding:10px; border-radius:8px; margin-bottom:20px;">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form action="connexion.php" method="POST">
            <div class="form-group">
                <input type="email" name="email" placeholder="Email" required>
            </div>
            <div class="form-group">
                <input type="password" name="password" placeholder="Mot de passe" required>
            </div>
            <button type="submit">Connexion</button>
        </form>

        <p style="margin-top:20px; color:#666;">
            Pas encore de compte ? <a href="inscription.php" style="color:var(--dark); font-weight:bold;">Inscrivez-vous</a>
        </p>
    </div>

</body>
</html>
