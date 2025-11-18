<?php
// --- 1. INCLUSIONS PHPMailer ---
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Assurez-vous que ce chemin est correct
require '../vendor/autoload.php';

// --- 2. CONFIGURATION ---
$db_host = 'localhost';
$db_nom = 'users';
$db_user = 'root';
$db_pass = 'Collector10';
$charset = 'utf8mb4';

// !! IMPORTANT: URL de votre serveur
$server_url = 'https://michael.rousseau.13h37.io'; 

// --- 3. INITIALISATION ---
$message = ''; 

// --- 4. TRAITEMENT DU FORMULAIRE ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $mail_utilisateur = trim($_POST['mail']); // Renommé pour éviter conflit avec $mail (PHPMailer)
    $mdp_clair = $_POST['mdp']; 

    // --- 5. VALIDATION ---
    if (empty($nom) || empty($prenom) || empty($mail_utilisateur) || empty($mdp_clair)) {
        $message = 'Erreur : Tous les champs sont requis.';
    } elseif (!filter_var($mail_utilisateur, FILTER_VALIDATE_EMAIL)) {
        $message = 'Erreur : Format d\'email invalide.';
    } else {
        
        // --- 6. PRÉPARATION (Hachage et Token) ---
        $hashed_password = password_hash($mdp_clair, PASSWORD_DEFAULT);
        $token = bin2hex(random_bytes(32)); // Crée un token sécurisé

        // --- 7. CONNEXION BDD ---
        $dsn = "mysql:host=$db_host;dbname=$db_nom;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, $db_user, $db_pass, $options);

            // --- 8. VÉRIFIER DOUBLON EMAIL ---
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE mail = ?");
            $stmt->execute([$mail_utilisateur]);
            
            if ($stmt->fetchColumn() > 0) {
                $message = 'Erreur : Cette adresse email est déjà enregistrée.';
            } else {
                
                // --- 9. INSÉRER L'UTILISATEUR ET LE TOKEN ---
                // On inclut la colonne 'token' dans l'insertion
                $sql = "INSERT INTO users (nom, prenom, mail, mdp, token) VALUES (?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nom, $prenom, $mail_utilisateur, $hashed_password, $token]);

                // --- 10. ENVOI DE L'EMAIL DE VÉRIFICATION ---
                $verification_link = $server_url . "/verif.php?token=" . $token;
                
                $mail = new PHPMailer(true); 

                try {
                    // Configuration serveur (identique à votre contact.php)
                    $mail->isSMTP();
                    $mail->Host       = 'localhost';
                    $mail->SMTPAuth   = false;
                    $mail->Port       = 25;
                    $mail->CharSet    = 'UTF-8';

                    // Expéditeur
                    $mail->setFrom('no-reply@votre-site.com', 'Support Inscription');
                    // Destinataire
                    $mail->addAddress($mail_utilisateur, $prenom . ' ' . $nom);

                    // Contenu
                    $mail->isHTML(true);
                    $mail->Subject = 'Activez votre compte';
                    $mail->Body    = "<h3>Bonjour $prenom,</h3>" .
                                   "<p>Cliquez sur ce lien pour activer votre compte :</p>" .
                                   "<a href='$verification_link'>$verification_link</a>";
                    $mail->AltBody = "Bonjour $prenom, copiez ce lien pour activer votre compte : $verification_link";

                    $mail->send();
                    
                    $message = 'Inscription réussie ! Veuillez consulter votre email pour vérifier votre compte.';

                } catch (Exception $e) {
                    $message = "Inscription réussie, mais l'email de vérification n'a pas pu être envoyé. Erreur : {$mail->ErrorInfo}";
                }
            }
        } catch (\PDOException $e) {
            $message = 'Erreur de base de données : ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Inscription Utilisateur</title>
    <style>
        /* (Votre CSS) */
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; display: grid; place-items: center; min-height: 90vh; background-color: #f4f4f4; }
        .container { background: #fff; border: 1px solid #ccc; padding: 25px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        h2 { text-align: center; margin-top: 0; }
        form div { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="email"], input[type="password"] { width: 300px; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        .message { padding: 10px; margin-bottom: 15px; border-radius: 4px; text-align: center; }
        .success { background-color: #d4edda; color: #155724; }
        .error { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Créer votre compte</h2>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo (strpos($message, 'Erreur') !== false) ? 'error' : 'success'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form action="inscription.php" method="POST">
            <div>
                <label for="nom">Nom :</label>
                <input type="text" id="nom" name="nom" required>
            </div>
            <div>
                <label for="prenom">Prénom :</label>
                <input type="text" id="prenom" name="prenom" required>
            </div>
            <div>
                <label for="mail">Email :</Flabel>
                <input type="email" id="mail" name="mail" required>
            </div>
            <div>
                <label for="mdp">Mot de passe :</label>
                <input type="password" id="mdp" name="mdp" required>
            </div>
            <div>
                <button type="submit">S'inscrire</button>
            </div>
        </form>
    </div>
</body>
</html>
