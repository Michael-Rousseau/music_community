<?php
// 1. --- DÉMARRER LA SESSION ---
session_start();

// 2. --- INCLUSION DE LA CONNEXION BDD ---
// On remonte d'un dossier (..) pour aller chercher config/db.php
require_once '../config/db.php';

// 3. --- INITIALISATION ---
$message = '';

// 4. --- TRAITEMENT DU FORMULAIRE ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // On vérifie 'email' car j'ai corrigé le name dans le HTML plus bas
    if (empty($_POST['email']) || empty($_POST['password'])) {
        $message = 'Erreur : Email et mot de passe requis.';
    } else {
        
        $email_posted = trim($_POST['email']);
        $password_posted = $_POST['password'];

        try {
            // 5. RECHERCHER L'UTILISATEUR
            // Correction : Table 'users' (pas useres)
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email_posted]);
            $user = $stmt->fetch(); 

            // 6. VÉRIFICATIONS
            if ($user) {
                
                // Vérification du mot de passe (La colonne BDD s'appelle 'password')
                if (password_verify($password_posted, $user['password'])) {

                    // Vérification du Token (Si NULL, c'est que le compte est validé)
                    if ($user['token'] === NULL) {
                        
                        // --- SUCCÈS ---
                        session_regenerate_id(true);
                        
                        // On stocke les infos disponibles dans ta nouvelle table
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_name'] = $user['username']; // Ta table a 'username', pas 'nom'/'prenom'
                        $_SESSION['user_role'] = $user['role'];
                        
                        // Redirection
                        header("Location: index.php"); // Ou dashboard.php
                        exit;

                    } else {
                        $message = 'Erreur : Votre compte n\'est pas encore validé. Vérifiez vos emails.';
                    }
                } else {
                    $message = 'Erreur : Email ou mot de passe incorrect.';
                }
            } else {
                $message = 'Erreur : Email ou mot de passe incorrect.';
            }

        } catch (PDOException $e) {
            // En production, évite d'afficher l'erreur brute SQL
            $message = 'Erreur technique lors de la connexion.';
            /* // error_log($e->getMessage()); // Pour toi dans les logs serveur */
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Connexion - Music Community</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; display: flex; justify-content: center; align-items: center; min-height: 90vh; background-color: #f4f4f9; margin: 0; }
        .container { background: #fff; padding: 2rem; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        h2 { text-align: center; color: #333; margin-bottom: 20px; }
        form div { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: 600; color: #555; }
        input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: bold; transition: background 0.3s; }
        button:hover { background-color: #0056b3; }
        .message { padding: 10px; margin-bottom: 20px; border-radius: 5px; text-align: center; font-size: 0.9rem; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .info { margin-top: 20px; text-align: center; font-size: 0.9em; color: #666; }
        .info a { color: #007bff; text-decoration: none; }
        .info a:hover { text-decoration: underline; }
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
                <label for="email">Email :</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div>
                <label for="password">Mot de passe :</label>
                <input type="password" id="password" name="password" required>
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
