<header>
    <a href="<?= $basePath ?>/" class="logo">
        <img src="<?= $basePath ?>/assets/images/logo_tempo.png" alt="Tempo Logo" width="150" height="50">
    </a>
    <div style="display:flex; align-items:center;">
        <button id="themeToggle" class="theme-toggle themeToggle" title="Mode sombre/clair" aria-label="Toggle Dark Mode">
            <i class="fas fa-moon"></i>
        </button>

        <nav>
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                    <a href="<?= $basePath ?>/admin" class="btn btn-secondary" style="border-color:gold; color:#b58900; margin-right:5px;">Admin</a>
                <?php endif; ?>
                <a href="<?= $basePath ?>/dashboard" class="btn btn-primary">Mon Espace</a>
                <a href="<?= $basePath ?>/logout" class="btn btn-secondary">Déconnexion</a>
            <?php else: ?>
                <a href="<?= $basePath ?>/login" class="btn btn-primary">Connexion</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<style>
    .site-header {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        height: var(--header-height);
        background: var(--background-light);
        display: flex;
        align-items: center;
        z-index: 1000;
        border-bottom: 1px solid #eee;
    }

    .header-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .logo {
        max-width: 100%;
        max-height: 100%;
    }

    .logo div {
        max-width: 100%;
        max-height: var(--header-height);
        display: flex;
        align-items: center;
        justify-content: center;

    }

    nav {
        display: flex;
        gap: 20px;
    }
</style>