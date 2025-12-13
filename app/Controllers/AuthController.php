<?php

namespace Controllers;

use Core\Auth;
use Models\User;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class AuthController
{
    private $pdo;
    private $userModel;
    // FIX: Generate dynamic server URL based on current request
    private $server_url; 

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->userModel = new User($pdo);

        // Detect protocol and domain dynamically
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
        $this->server_url = $protocol . $_SERVER['HTTP_HOST'] . BASE_URL;
    }

    public function showLogin()
    {
        include __DIR__ . '/../Views/auth/login.php';
    }

    public function login()
    {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $message = '';

        if (!$email || !$password) {
            $message = 'Email et mot de passe requis';
            include __DIR__ . '/../Views/auth/login.php';
            return;
        }

        $user = $this->userModel->findByEmail($email);

        if ($user) {
            if ($this->userModel->validatePassword($user, $password)) {
                if ($user['token'] === null) {
                    Auth::login($user);
                    // FIX: Ensure correct redirection
                    header("Location: " . rtrim(BASE_URL, '/') . "/profile");
                    exit;
                } else {
                    $message = "Votre compte n'est pas encore vérifié. Vérifiez votre email.";
                }
            } else {
                $message = "Email ou mot de passe incorrect";
            }
        } else {
            $message = "Email ou mot de passe incorrect";
        }

        include __DIR__ . '/../Views/auth/login.php';
    }

    public function showSignup()
    {
        include __DIR__ . '/../Views/auth/signup.php';
    }

    public function signup()
    {
        $email = $_POST['email'] ?? '';
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $message = '';

        if (!$email || !$username || !$password) {
            $message = 'Tous les champs sont requis';
            include __DIR__ . '/../Views/auth/signup.php';
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = 'Email invalide';
            include __DIR__ . '/../Views/auth/signup.php';
            return;
        }

        $existing = $this->userModel->findByEmail($email);
        if ($existing) {
            if ($existing['token'] === null) {
                $message = 'Email déjà enregistré';
                include __DIR__ . '/../Views/auth/signup.php';
                return;
            } else {
                $this->userModel->deleteByEmail($email);
            }
        }

        $token = $this->userModel->create($email, $username, $password);

        // FIX: Removed the "echo $token" that was breaking the layout

        $mail = new PHPMailer(true);
        // Clean URL construction
        $verification_link = rtrim($this->server_url, '/') . "/verify?token=$token";

        try {
            $mail->isSMTP();
            $mail->Host = 'localhost';
            $mail->SMTPAuth = false;
            $mail->Port = 1025; // Often 1025 for local dev (Mailhog), or 25
            $mail->setFrom('no-reply@music-community.local', 'Music Community');
            $mail->addAddress($email, $username);
            $mail->isHTML(true);
            $mail->Subject = 'Vérifiez votre compte';
            $mail->Body = "Cliquez sur ce lien pour vérifier votre compte : <a href='$verification_link'>$verification_link</a>";
            $mail->AltBody = "Copiez ce lien pour vérifier votre compte : $verification_link";
            $mail->send();
            $message = 'Inscription réussie ! Vérifiez votre email pour activer votre compte.';
        } catch (Exception $e) {
            $message = "Compte créé, mais erreur d'email : " . $mail->ErrorInfo;
        }

        include __DIR__ . '/../Views/auth/signup.php';
    }

    public function verify()
    {
        $token = $_GET['token'] ?? '';
        $message = '';
        if ($token && $this->userModel->verify($token)) {
            $message = 'Votre compte a été vérifié ! Vous pouvez maintenant vous connecter.';
        } else {
            $message = 'Token invalide ou expiré.';
        }

        include __DIR__ . '/../Views/auth/verify.php';
    }

    public function logout()
    {
        Auth::logout();
        header("Location: " . rtrim(BASE_URL, '/') . "/");
        exit;
    }
}
