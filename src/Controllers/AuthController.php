<?php
namespace App\Controllers;

use App\Config\Database;
use App\Models\User;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class AuthController extends Controller {
    
    public function login() {
        $message = '';
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $pdo = Database::getConnection();
            $userModel = new User($pdo);
            
            $email = trim($_POST['email']);
            $password = $_POST['password'];
            $user = $userModel->findByEmail($email);

            if ($user && password_verify($password, $user['password'])) {
                if ($user['token'] === NULL) {
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['username'];
                    $_SESSION['user_role'] = $user['role'];
                    $this->redirect($this->basePath . '/');
                } else {
                    $message = 'Compte non validé.';
                }
            } else {
                $message = 'Identifiants incorrects.';
            }
        }
        $this->render('auth/login', ['message' => $message]);
    }

    public function register() {
        $message = '';
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $pdo = Database::getConnection();
            $userModel = new User($pdo);
            
            $username = trim($_POST['username']);
            $email = trim($_POST['email']);
            $password = $_POST['password'];

            if ($userModel->exists($email, $username)) {
                $message = 'Email ou nom d\'utilisateur déjà pris.';
            } else {
                $token = bin2hex(random_bytes(32));
                if ($userModel->create($username, $email, $password, $token)) {
                    $this->sendVerificationEmail($email, $username, $token);
                    $message = 'Succès ! Vérifiez vos emails.';
                } else {
                    $message = 'Erreur technique.';
                }
            }
        }
        $this->render('auth/register', ['message' => $message]);
    }

    public function verify() {
        $message = 'Lien invalide.';
        $success = false;
        
        if (isset($_GET['token'])) {
            $pdo = Database::getConnection();
            $userModel = new User($pdo);
            if ($userModel->verifyToken($_GET['token'])) {
                $message = 'Compte validé ! Vous pouvez vous connecter.';
                $success = true;
            } else {
                $message = 'Ce lien est invalide ou a déjà été utilisé.';
            }
        }
        $this->render('auth/verify', ['message' => $message, 'success' => $success]);
    }

    public function logout() {
        session_destroy();
        $uri = $this->basePath . "/";
        $this->redirect($uri);
    }

    public function forgottenPassword() {
        $message = '';
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $pdo = Database::getConnection();
            $userModel = new User($pdo);
            
            $email = trim($_POST['email']);
            $user = $userModel->findByEmail($email);

            if($user) {
                $resetToken = bin2hex(random_bytes(32));
                $userModel->updateToken($user['id'], $resetToken);
                $this->sendResetPasswordEmail($email, $user['username'], $resetToken);
            }

            $message = 'Un email de réinitialisation a été envoyé si l\'email existe.';
     
        }
        $this->render('auth/forgotten-passsword', ['message' => $message]);
    }

    public function resetPassword() {
        $message = '';
        $success = false;
        if (isset($_GET['token'])) {
            $token = $_GET['token'];
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                $pdo = Database::getConnection();
                $userModel = new User($pdo);
                
                $newPassword = $_POST['password'];

                if($userModel->updatePasswordByToken($token, $newPassword)){
                    $message = 'Mot de passe réinitialisé avec succès.';
                    $success = true;
                } else {
                    $message = 'Token invalide.';
                };
            }
            $this->render('auth/reset-password', ['message' => $message, 'token' => $token, 'success' => $success]);
        } else {
            $this->redirect($this->basePath . '/forgot-password');
        }
    }

    private function sendResetPasswordEmail($email, $username, $token) {
        $link = "http://" . $_SERVER['HTTP_HOST'] . $this->basePath . "/reset-password?token=" . $token;
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'localhost';
            $mail->SMTPAuth = false;
            $mail->Port = 25;
            $mail->SMTPAutoTLS = false; 
            $mail->SMTPSecure = false;
            $mail->CharSet = 'UTF-8';
            $mail->setFrom('no-reply@' . $_SERVER['HTTP_HOST'], 'Tempo');
            $mail->addAddress($email, $username);
            $mail->isHTML(true);
            $mail->Subject = 'Réinitialisez votre mot de passe Tempo';
            $mail->Body = "Bonjour ! <a href='$link'>Cliquez ici</a> pour réinitialiser votre mot de passe.";
            $mail->SMTPDebug = 2;
            $mail->Debugoutput = 'error_log';
            $mail->send();
        } catch (Exception $e) {
            die("STOP - Erreur Mailer : " . $mail->ErrorInfo);
        }
    }

    private function sendVerificationEmail($email, $username, $token) {
        $link = "http://" . $_SERVER['HTTP_HOST'] . $this->basePath . "/verify?token=" . $token;
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'localhost';
            $mail->SMTPAuth = false;
            $mail->Port = 25;
            $mail->SMTPAutoTLS = false; 
            $mail->SMTPSecure = false;
            $mail->CharSet = 'UTF-8';
            $mail->setFrom('no-reply@' . $_SERVER['HTTP_HOST'], 'Tempo');
            $mail->addAddress($email, $username);
            $mail->isHTML(true);
            $mail->Subject = 'Activez votre compte Tempo';
            $mail->Body = "Bienvenue ! <a href='$link'>Cliquez ici</a> pour activer votre compte.";
            $mail->SMTPDebug = 2;
            $mail->Debugoutput = 'error_log';
            $mail->send();
        } catch (Exception $e) {
            die("STOP - Erreur Mailer : " . $mail->ErrorInfo);
        }
    }

}
