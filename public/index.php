<?php
// Démarrage de la session (nécessaire partout en PHP)
session_start();

// PETITE ASTUCE PRO :
// Si l'utilisateur est déjà connecté, inutile de lui montrer cette page,
// on l'envoie direct vers la liste des musiques.
if (isset($_SESSION['user_id'])) {
    header("Location: ../dashboard.php"); // Ou dashboard.php
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenue - MusicShare</title>
    <style>
        /* Un peu de style pour ne pas avoir une page blanche triste */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 400px;
            width: 100%;
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
        }
        p {
            color: #666;
            margin-bottom: 30px;
        }
        .btn-container {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .btn {
            display: block;
            padding: 12px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: background 0.3s;
        }
        .btn-primary {
            background-color: #007bff; /* Bleu */
            color: white;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .btn-secondary {
            background-color: #6c757d; /* Gris */
            color: white;
        }
        .btn-secondary:hover {
            background-color: #545b62;
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>🎵 MusicShare</h1>
        <p>Rejoignez la communauté, partagez vos MP3 et découvrez de nouveaux talents.</p>
        
        <div class="btn-container">
            <a href="inscription.php" class="btn btn-primary">Créer un compte</a>
            
            <a href="connexion.php" class="btn btn-secondary">Se connecter</a>
        </div>
    </div>

</body>
</html>
