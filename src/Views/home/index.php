<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <meta name="description" content="Tempo - Communauté musicale de partage de MP3."> <title>Tempo - Accueil</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" media="print" onload="this.media='all'">

    <link rel="stylesheet" href="/assets/css/tempo.css">
</head>
<body>

<header>
    <a href="/" class="logo"><img src="/assets/images/logo_tempo.png" alt="Tempo"></a>
    <div style="display:flex; align-items:center;">
        <button id="themeToggle" class="theme-toggle" title="Changer de thème"><i class="fas fa-moon"></i></button>

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
<script src="/assets/js/tempo.js"></script>

    <div class="hero">
        <h1>Accueil</h1>
        <form class="search-bar" action="/" method="GET" style="max-width: 400px; margin: 0 auto;">
            <input type="text" name="q" placeholder="Rechercher un titre..." value="<?= htmlspecialchars($search); ?>">
        </form>
    </div>

    <div class="grid">
        <?php foreach ($musics as $m): ?>
            <div class="card">
<div class="card-img">
    <?php if(!empty($m['image']) && $m['image'] !== 'default_image.png'): ?>
         <img src="/uploads/images/<?= htmlspecialchars($m['image']) ?>" 
              alt="Cover of <?= htmlspecialchars($m['title']); ?>"
              loading="lazy"
              width="250" height="180"
              style="width:100%; height:100%; object-fit:cover;">
    <?php else: ?>
        💿
    <?php endif; ?>
</div>
                <div class="card-body">
                    <h3 class="card-title"><?= htmlspecialchars($m['title']); ?></h3>
                    <div class="card-user" style="display:flex; align-items:center; gap:8px;">
                        <?php if (!empty($m['avatar']) && $m['avatar'] !== 'default_avatar.png'): ?>
                            <img src="/uploads/avatars/<?= htmlspecialchars($m['avatar']); ?>" 
                                 style="width:24px; height:24px; border-radius:50%; object-fit:cover; border:2px solid var(--primary);">
                        <?php else: ?>
                            <div style="width:24px; height:24px; border-radius:50%; background:var(--primary); color:#2D2828; display:flex; align-items:center; justify-content:center; font-size:0.7rem; font-weight:bold;">
                                <?= strtoupper(substr($m['username'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                        <span>par <?= htmlspecialchars($m['username']); ?></span>
                    </div>
                    <a href="/music?id=<?= $m['id']; ?>" class="btn btn-primary" style="display:block; text-align:center;">Écouter ▶</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

</body>
</html>
