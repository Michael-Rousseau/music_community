<?php
// --- 1. CONFIGURATION ---
$db_host = 'localhost';
$db_nom = 'users';
$db_user = 'root';
$db_pass = 'Collector10';
$charset = 'utf8mb4';

$message = '';
$message_type = 'error'; // Par défaut, on considère que c'est une erreur

// --- 2. VÉRIFICATION DU TOKEN ---
// On vérifie si un token est présent dans l'URL (via la méthode GET)
if (isset($_GET['token']) && !empty($_GET['token'])) {
    
    $token_recu = $_GET['token'];

    // --- 3. CONNEXION BDD (PDO) ---
    $dsn = "mysql:host=$db_host;dbname=$db_nom;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $pdo = new PDO($dsn, $db_user, $db_pass, $options);

        // --- 4. RECHERCHER LE TOKEN ET VÉRIFIER L'UTILISATEUR ---
        // On cherche un utilisateur avec ce token
        // Si on le trouve, on met son token à NULL pour le valider
        $sql = "UPDATE users SET token = NULL WHERE token = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$token_recu]);

        if ($stmt->rowCount() > 0) {
            // SUCCÈS : 1 ligne a été modifiée, le token était bon
            $message = 'Vérification réussie ! Votre compte est maintenant actif. Vous pouvez vous connecter.';
            $message_type = 'success';
        } else {
            // ÉCHEC : 0 ligne modifiée, le token est invalide ou a déjà été utilisé
            $message = 'Échec de la vérification. Ce lien est invalide ou a déjà expiré.';
            $message_type = 'error';
        }

    } catch (\PDOException $e) {
        $message = 'Erreur de base de données : ' . $e->getMessage();
        $message_type = 'error';
    }

} else {
    // Pas de token dans l'URL
    $message = 'Échec de la vérification. Aucun token fourni.';
    $message_type = 'error';
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification de compte</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; display: grid; place-items: center; min-height: 90vh; background-color: #f4f4f4; }
        .message { padding: 25px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); font-size: 1.2em; text-align: center; max-width: 500px; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        a { color: #007bff; text-decoration: none; font-weight: bold; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    
    <div class="message <?php echo $message_type; ?>">
        <?php echo htmlspecialchars($message); ?>
        
        <?php if ($message_type === 'success'): ?>
            <p style="margin-top: 20px;">
                <a href="connexion.php">Cliquez ici pour vous connecter</a>
            </p>
        <?php endif; ?>
    </div>

</body>
</html>
