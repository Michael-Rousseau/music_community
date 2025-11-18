<?php
session_start();

// CHARGEMENT DES LIBRAIRIES VIA VENDOR (COMPOSER)
require '../vendor/autoload.php'; 
require_once '../config/db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Configuration de l'URL de base pour le lien
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$domainName = $_SERVER['HTTP_HOST'];
$path = dirname($_SERVER['PHP_SELF']);
// Nettoyage si on est à la racine
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
            // Vérification doublon
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? OR username = ?");
            $stmt->execute([$email, $username]);
            
            if ($stmt->fetchColumn() > 0) {
                $message = 'Erreur : Cet email ou ce nom d\'utilisateur est déjà pris.';
            } else {
                // Création user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $token = bin2hex(random_bytes(32));

                $sql = "INSERT INTO users (username, email, password, token) VALUES (?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$username, $email, $hashed_password, $token]);

                // Envoi email
                $verification_link = $server_url . "/verif.php?token=" . $token;
                
                $mail = new PHPMailer(true);
                try {
                    // CONFIGURATION SMTP PROD (LOCALHOST)
                    $mail->isSMTP();
                    $mail->Host       = 'localhost'; 
                    $mail->SMTPAuth   = false;       // Souvent false pour localhost
                    $mail->Port       = 25;          // Port standard Postfix
                    $mail->CharSet    = 'UTF-8';

                    $mail->setFrom('no-reply@michael.rousseau.13h37.io', 'Music Community'); // Mets ton domaine ici
                    $mail->addAddress($email, $username);

                    $mail->isHTML(true);
                    $mail->Subject = 'Activez votre compte Music Community';
                    $mail->Body    = "<h3>Bonjour $username,</h3><p>Validez votre compte en cliquant ici : <a href='$verification_link'>$verification_link</a></p>";
                    $mail->AltBody = "Lien de validation : $verification_link";

                    $mail->send();
                    $message = 'Succès ! Vérifiez vos emails pour activer votre compte.';

                } catch (Exception $e) {
                    // En prod, on log l'erreur mais on affiche un message gentil
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Inscription - Music Community</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; display: flex; justify-content: center; align-items: center; min-height: 90vh; background-color: #f4f4f9; margin: 0; }
        .container { background: #fff; padding: 2rem; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        h2 { text-align: center; color: #333; margin-bottom: 20px; }
        form div { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: 600; color: #555; }
        input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background-color: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: bold; transition: background 0.3s; }
        button:hover { background-color: #218838; }
        .message { padding: 10px; margin-bottom: 20px; border-radius: 5px; text-align: center; font-size: 0.9rem; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .info { margin-top: 20px; text-align: center; font-size: 0.9em; color: #666; }
        .info a { color: #007bff; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Créer un compte</h2>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo (strpos($message, 'Succès') !== false) ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form action="inscription.php" method="POST">
            <div>
                <label for="username">Nom d'utilisateur :</label>
                <input type="text" id="username" name="username" required placeholder="Ex: DJ_Mike">
            </div>
            <div>
                <label for="email">Email :</label>
                <input type="email" id="email" name="email" required placeholder="exemple@gmail.com">
            </div>
            <div>
                <label for="password">Mot de passe :</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div>
                <button type="submit">S'inscrire</button>
            </div>
        </form>
        
        <div class="info">
            <p>Déjà membre ? <a href="connexion.php">Connectez-vous</a></p>
        </div>
    </div>
</body>
</html>
