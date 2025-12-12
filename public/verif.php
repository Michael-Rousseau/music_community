<?php

require_once '../config/db.php';

$message = '';
$message_type = 'error'; 

if (isset($_GET['token']) && !empty($_GET['token'])) {
    
    $token_recu = $_GET['token'];

    try {
        $stmt = $pdo->prepare("UPDATE users SET token = NULL WHERE token = ?");
        $stmt->execute([$token_recu]);

        // if rowcount > 0 : token existed, we delete it -> success.
        // else:token didn't exist (or already validated account) -> fail.
        
        if ($stmt->rowCount() > 0) {
            $message = 'Félicitations ! Votre compte a été vérifié avec succès.';
            $message_type = 'success';
            header("Location: " . BASE_URL . "login");
            exit;
        } else {
            $message = 'Ce lien de validation est invalide ou a déjà été utilisé.';
            $message_type = 'error';
        }

    } catch (PDOException $e) {
        error_log("Erreur DB verif.php : " . $e->getMessage()); 
        $message = 'Une erreur technique est survenue. Veuillez contacter le support.';
        $message_type = 'error';
    }

} else {
    $message = 'Lien incomplet. Vérifiez l\'URL dans votre email.';
    $message_type = 'error';
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validation du compte</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #f4f4f9;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .card {
            background: white;
            padding: 2.5rem;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            text-align: center;
            max-width: 450px;
            width: 90%;
        }
        h1 {
            margin-top: 0;
            color: #333;
            font-size: 1.5rem;
        }
        .status-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            display: block;
        }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        
        p { color: #666; line-height: 1.5; margin-bottom: 1.5rem; }
        
        .btn {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            transition: background-color 0.2s;
        }
        .btn:hover { background-color: #0056b3; }
    </style>
</head>
<body>

    <div class="card">
        <?php if ($message_type === 'success'): ?>
            <span class="status-icon success">✅</span>
            <h1>Compte Activé</h1>
            <p><?php echo htmlspecialchars($message); ?></p>
            <a href="connexion.php" class="btn">Se connecter</a>
        <?php else: ?>
            <span class="status-icon error">⚠️</span>
            <h1>Erreur de validation</h1>
            <p><?php echo htmlspecialchars($message); ?></p>
            <a href="index.php" class="btn" style="background-color:#6c757d;">Retour à l'accueil</a>
        <?php endif; ?>
    </div>

</body>
</html>
