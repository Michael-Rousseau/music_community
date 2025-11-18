<?php
// 1. --- DÉMARRER LA SESSION ---
// Doit être la toute première chose sur la page
session_start();

// 2. --- CONFIGURATION ---
$db_host = 'localhost';
$db_nom = 'users';
$db_user = 'root';
$db_pass = 'Collector10';
$charset = 'utf8mb4';

// 3. --- INITIALISATION ---
$message = '';

// 4. --- TRAITEMENT DU FORMULAIRE DE CONNEXION ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Vérifier si les champs sont remplis
    if (empty($_POST['mail']) || empty($_POST['mdp'])) {
        $message = 'Erreur : Email et mot de passe requis.';
    } else {
        
        $mail_utilisateur = trim($_POST['mail']);
        $mdp_clair = $_POST['mdp'];

        // --- 5. CONNEXION BDD (PDO) ---
        $dsn = "mysql:host=$db_host;dbname=$db_nom;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, $db_user, $db_pass, $options);

            // --- 6. RECHERCHER L'UTILISATEUR PAR EMAIL ---
            $stmt = $pdo->prepare("SELECT * FROM users WHERE mail = ?");
            $stmt->execute([$mail_utilisateur]);
            $user = $stmt->fetch(); // Récupère l'utilisateur ou 'false'

            // --- 7. VÉRIFICATIONS (Utilisateur, Mot de passe, Token) ---

            // Étape 1: L'utilisateur existe-t-il ?
            if ($user) {
                
                // Étape 2: Le mot de passe est-il correct ?
                if (password_verify($mdp_clair, $user['mdp'])) {

                    // Étape 3: Le compte est-il VÉRIFIÉ ? (token est NULL)
                    if ($user['token'] == NULL) {
                        
                        // --- CONNEXION RÉUSSIE ---
                        session_regenerate_id(true); // Sécurité anti-fixation
                        
                        // Stocker les infos de l'utilisateur dans la session
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_nom'] = $user['nom'];
                        $_SESSION['user_prenom'] = $user['prenom'];
                        
                        // Rediriger vers une page membre (ex: dashboard.php)
                        header("Location: dashboard.php"); // !! Changez 'dashboard.php' si besoin
                        exit; // Toujours 'exit' après une redirection

                    } else {
                        // Mot de passe correct, MAIS token n'est pas NULL
                        $message = 'Erreur : Votre compte n\'est pas encore vérifié. Veuillez consulter l\'email que nous vous avons envoyé.';
                    }
                } else {
                    // Mot de passe incorrect
                    $message = 'Erreur : Email ou mot de passe incorrect.';
                }
            } else {
                // Utilisateur non trouvé
                $message = 'Erreur : Email ou mot de passe incorrect.';
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
    <title>Connexion</title>
    <style>
        /* (Même style que votre page d'inscription pour la cohérence) */
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; display: grid; place-items: center; min-height: 90vh; background-color: #f4f4f4; }
        .container { background: #fff; border: 1px solid #ccc; padding: 25px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        h2 { text-align: center; margin-top: 0; }
        form div { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="email"], input[type="password"] { width: 300px; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        button:hover { background-color: #0056b3; }
        .message { padding: 10px; margin-bottom: 15px; border-radius: 4px; text-align: center; }
        .success { background-color: #d4edda; color: #155724; }
        .error { background-color: #f8d7da; color: #721c24; }
        .info { padding: 10px; text-align: center; font-size: 0.9em; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Se connecter</h2>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo (strpos($message, 'Erreur') !== false) ? 'error' : 'success'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form action="connexion.php" method="POST">
            <div>
                <label for="mail">Email :</label>
                <input type="email" id="mail" name="mail" required>
            </div>
            <div>
                <label for="mdp">Mot de passe :</label>
                <input type="password" id="mdp" name="mdp" required>
            </div>
            <div>
                <button type="submit">Connexion</button>
            </div>
        </form>

        <div class="info">
            <p>Pas encore de compte ? <a href="inscription.php">Inscrivez-vous</a></p>
        </div>
    </div>
</body>
</html>
