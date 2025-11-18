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
    <title>Inscription</title>
    <style>
        body { font-family: system-ui, sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; background-color: #f4f4f9; margin: 0; }
        .container { background: #fff; padding: 2rem; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        input { width: 100%; padding: 10px; margin-top: 5px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background-color: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; }
        .message { padding: 10px; margin-bottom: 20px; text-align: center; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; } .error { background: #f8d7da; color: #721c24; }
        .info { text-align: center; margin-top: 15px; font-size: 0.9em; }
    </style>
</head>
