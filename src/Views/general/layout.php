<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <meta name="description" content="Bienvenue sur Tempo, la communauté musicale pour partager et découvrir des fichiers MP3. Écoutez, notez et commentez.">
    <title><?= isset($title)? "$title - Tempo" : "Accueil - Tempo" ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/tempo.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;900&family=Rajdhani:wght@300;500;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" media="print" onload="this.media='all'">
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"></noscript>

        <script type="importmap">
    {
      "imports": {
        "three": "https://unpkg.com/three@0.158.0/build/three.module.js",
        "three/addons/": "https://unpkg.com/three@0.158.0/examples/jsm/"
      }
    }
    </script>
    

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
        body { font-family: 'Rajdhani', sans-serif; background-color: var(--bg-body); color: var(--text-main); margin: 0; padding: 0; transition: background 0.3s, color 0.3s; }
        
        header { display: flex; justify-content: space-between; align-items: center; padding: 15px 40px; background: var(--bg-card); border-bottom: 1px solid var(--border-color); }
        .logo img { height: 50px; width: auto; }
        
        .theme-toggle { background: none; border: 2px solid var(--border-color); color: var(--text-main); padding: 8px 12px; border-radius: 50%; cursor: pointer; margin-right: 15px; }
        .btn { display: inline-block; padding: 10px 24px; border-radius: 50px; text-decoration: none; font-weight: 600; font-size: 0.9rem; border: none; cursor: pointer; }
        .btn-primary { background-color: var(--primary); color: #2D2828; }
        .btn-secondary { background: transparent; color: var(--text-main); border: 1px solid var(--border-color); }
        
        .hero { text-align: center; padding: 60px 20px; }
        h1 { font-family: 'Orbitron', sans-serif; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 20px; }
        
        .search-bar input { width: 100%; padding: 15px; background: var(--bg-input); color: var(--text-main); border: 1px solid var(--border-color); border-radius: 50px; font-family: 'Rajdhani', sans-serif; font-size: 1.1rem; box-sizing: border-box; }
        
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 30px; max-width: 1200px; margin: 0 auto; padding: 0 20px 60px; }
        .card { background: var(--bg-card); border-radius: 20px; overflow: hidden; box-shadow: 0 10px 30px var(--shadow); width: 100%; max-width: 350px; margin: 0 auto; display: flex; flex-direction: column; }
        .card-img { height: 180px; background: var(--bg-input); display: flex; align-items: center; justify-content: center; font-size: 3rem; color: var(--text-muted); overflow:hidden; }
        .card-body { padding: 20px; flex-grow: 1; display:flex; flex-direction:column; }
        .card-title { font-weight: 800; margin: 0 0 5px 0; font-size: 1.2rem; color: var(--text-main); }
        .card-user { color: var(--text-muted); font-size: 0.9rem; margin-bottom: 15px; }
    </style>
</head>
<body>

    <?php include __DIR__ . '/header.php'; ?>

    <main class="wrapper">
        <?= $content ?? '' ?>
    </main>


<script>
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

</body>
</html>
