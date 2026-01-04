<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="description" content="Bienvenue sur Tempo, la communauté musicale pour partager et découvrir des fichiers MP3.">
    <title><?= $pageTitle ?? 'Tempo - Communauté Musicale'; ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;900&family=Rajdhani:wght@300;500;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" media="print" onload="this.media='all'">
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"></noscript>

    <link rel="preload" href="/assets/css/tempo.css" as="style">
    <link rel="stylesheet" href="/assets/css/tempo.css">

    <style>
        :root {
            --primary: #F7AAC3; --primary-hover: #e08baa; --dark: #2D2828;
            --text-main: #2D2828; --text-muted: #666666; --bg-body: #ffffff;
            --bg-card: #f9f9f9; --bg-input: #eff1f5; --border-color: #eeeeee;
            --shadow: rgba(0,0,0,0.05);
        }
        body.dark-mode {
            --primary: #F7AAC3; --primary-hover: #ffc2d6; --dark: #ffffff;
            --text-main: #f0f0f0; --text-muted: #aaaaaa; --bg-body: #121212;
            --bg-card: #1e1e1e; --bg-input: #2a2a2a; --border-color: #333333;
            --shadow: rgba(0,0,0,0.3);
        }
        body {
            font-family: 'Rajdhani', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            margin: 0;
            padding: 0;
            padding-bottom: 120px;
            transition: background 0.3s, color 0.3s;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 40px;
            background: var(--bg-card);
            border-bottom: 1px solid var(--border-color);
            position: relative;
            z-index: 100;
        }
        .logo img { height: 50px; width: auto; }

        .theme-toggle {
            background: none;
            border: 2px solid var(--border-color);
            color: var(--text-main);
            padding: 8px 12px;
            border-radius: 50%;
            cursor: pointer;
            margin-right: 15px;
        }
        .btn {
            display: inline-block;
            padding: 10px 24px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            border: none;
            cursor: pointer;
        }
        .btn-primary { background-color: var(--primary); color: #2D2828; }
        .btn-secondary { background: transparent; color: var(--text-main); border: 1px solid var(--border-color); }

        main { position: relative; z-index: 10; }
    </style>

    <?= $head ?? ''; ?>
</head>
<body>

<header>
    <a href="/" class="logo">
        <img src="/assets/images/logo_tempo.png" alt="Tempo Logo" width="150" height="50">
    </a>
    <div style="display:flex; align-items:center;">
        <button id="themeToggle" class="theme-toggle" title="Mode sombre/clair" aria-label="Toggle Dark Mode">
            <i class="fas fa-moon"></i>
        </button>

        <nav>
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                    <a href="/admin" class="btn btn-secondary" style="border-color:gold; color:#b58900; margin-right:5px;">Admin</a>
                <?php endif; ?>
                <a href="/dashboard" class="btn btn-primary">Mon Espace</a>
                <a href="/logout" class="btn btn-secondary">Déconnexion</a>
            <?php else: ?>
                <a href="/login" class="btn btn-primary">Connexion</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main id="main-content">
    <?= $content ?>
</main>

<?php
// include persistent player component
require_once(__DIR__ . '/../components/player.php');
?>

<script>
// theme toggle logic
document.addEventListener("DOMContentLoaded", () => {
  const toggleBtn = document.getElementById("themeToggle");
  const body = document.body;
  const icon = toggleBtn ? toggleBtn.querySelector("i") : null;

  if (localStorage.getItem("theme") === "dark") {
    body.classList.add("dark-mode");
    if (icon) { icon.classList.replace("fa-moon", "fa-sun"); }
  }

  if (toggleBtn) {
    toggleBtn.addEventListener("click", () => {
      body.classList.toggle("dark-mode");
      if (body.classList.contains("dark-mode")) {
        localStorage.setItem("theme", "dark");
        if (icon) { icon.classList.replace("fa-moon", "fa-sun"); }
      } else {
        localStorage.setItem("theme", "light");
        if (icon) { icon.classList.replace("fa-sun", "fa-moon"); }
      }
    });
  }
});
</script>

<script src="/assets/js/tempo.js"></script>
<script src="/assets/js/player-state.js"></script>
<script src="https://unpkg.com/htmx.org@1.9.10"></script>

</body>
</html>
