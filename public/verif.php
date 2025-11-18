<?php
// public/verif.php

// 1. --- IMPORTATION DE LA CONFIGURATION ---
// On utilise le fichier centralisé. Si on change de serveur, on ne touche qu'à db.php.
require_once '../config/db.php';

$message = '';
$message_type = 'error'; // Par défaut, erreur

// 2. --- LOGIQUE DE VÉRIFICATION ---
if (isset($_GET['token']) && !empty($_GET['token'])) {
    
    $token_recu = $_GET['token'];

    try {
        // On tente de mettre à jour l'utilisateur qui possède ce token.
        // L'action : On met le token à NULL (ce qui valide le compte).
        // La condition : Le token doit correspondre à celui reçu.
        $stmt = $pdo->prepare("UPDATE users SET token = NULL WHERE token = ?");
        $stmt->execute([$token_recu]);

        // 3. --- ANALYSE DU RÉSULTAT ---
        // rowCount() nous dit combien de lignes ont été modifiées.
        // Si > 0 : Le token existait, on l'a effacé -> SUCCÈS.
        // Si = 0 : Le token n'existait pas (ou compte déjà validé) -> ÉCHEC.
        
        if ($stmt->rowCount() > 0) {
            $message = 'Félicitations ! Votre compte a été vérifié avec succès.';
            $message_type = 'success';
        } else {
            // Sécurité : On reste vague pour ne pas donner d'infos aux pirates
            $message = 'Ce lien de validation est invalide ou a déjà été utilisé.';
            $message_type = 'error';
        }

    } catch (PDOException $e) {
        // PROD : On n'affiche JAMAIS l'erreur SQL brute à l'utilisateur
        error_log("Erreur DB verif.php : " . $e->getMessage()); // On loggue l'erreur côté serveur
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
