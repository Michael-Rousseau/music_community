<?php
// 1. On démarre la session pour pouvoir y accéder
session_start();

// 2. On détruit toutes les variables de session
$_SESSION = array();

// 3. On détruit le cookie de session (si utilisé)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. On détruit la session
session_destroy();

// 5. Redirection vers la page d'accueil
header("Location: index.php");
exit();
?>
