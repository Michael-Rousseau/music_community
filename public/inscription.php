<?php
session_start();

require '../vendor/autoload.php'; 
require_once '../config/db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$domainName = $_SERVER['HTTP_HOST'];
$path = dirname($_SERVER['PHP_SELF']);
if($path === '/' || $path === '\\') $path = '';
$server_url = $protocol . $domainName . $path; 

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($username) || empty($email) || empty($password)) {
        $message = 'Erreur : Tous les champs sont requis.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Erreur : Format d\'email invalide.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? OR username = ?");
            $stmt->execute([$email, $username]);
            
            if ($stmt->fetchColumn() > 0) {
                $message = 'Erreur : Cet email ou ce nom d\'utilisateur est déjà pris.';
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $token = bin2hex(random_bytes(32));

                $sql = "INSERT INTO users (username, email, password, token) VALUES (?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$username, $email, $hashed_password, $token]);

                $verification_link = $server_url . "/verif.php?token=" . $token;
                
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host       = 'localhost'; 
                    $mail->SMTPAuth   = false;       
                    $mail->Port       = 25;         
                    $mail->CharSet    = 'UTF-8';

                    $mail->setFrom('no-reply@michael.rousseau.13h37.io', 'Music Community'); 
                    $mail->addAddress($email, $username);

                    $mail->isHTML(true);
                    $mail->Subject = 'Activez votre compte Music Community';
                    $mail->Body    = "<h3>Bonjour $username,</h3><p>Validez votre compte en cliquant ici : <a href='$verification_link'>$verification_link</a></p>";
                    $mail->AltBody = "Lien de validation : $verification_link";

                    $mail->send();
                    $message = 'Succès ! Vérifiez vos emails pour activer votre compte.';

                } catch (Exception $e) {
                    error_log("Mail Error: " . $mail->ErrorInfo);
                    $message = "Compte créé, mais impossible d'envoyer l'email. Contactez l'admin.";
                }
            }
        } catch (PDOException $e) {
            error_log("DB Error: " . $e->getMessage());
            $message = 'Une erreur technique est survenue.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription - Tempo</title>
    <link rel="stylesheet" href="assets/css/tempo.css">
</head>
<body>

    <header>
        <a href="index.php" class="logo">
            <img src="assets/images/logo_tempo.png" alt="Tempo">
        </a>
        <a href="connexion.php" class="btn btn-primary">Connexion</a>
    </header>

    <div class="auth-container">
        <h1>Inscription</h1>

        <?php if (!empty($message)): ?>
            <div style="background:#d4edda; color:#155724; padding:10px; border-radius:8px; margin-bottom:20px;">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form action="inscription.php" method="POST">
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
            Déjà un compte ? <a href="connexion.php" style="color:var(--dark); font-weight:bold;">Connectez-vous</a>
        </p>
    </div>

</body>
</html>
