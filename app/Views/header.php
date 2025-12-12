<header class="site-header">
    <div class="header-container wrapper">
        
        <a href="<?= BASE_URL ?>/" class="logo">
            <div>
                <img src="<?= BASE_URL ?>/assets/images/logo_tempo.png" alt="Logo Tempo">
            </div>
        </a>

        <nav>
            <?php if(\Core\Auth::check()): ?>
                <a href="<?= BASE_URL ?>/m/new">Uploader une musique</a>
                <a href="<?= BASE_URL ?>/profile">Profil</a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/login" class="btn primary">Connexion</a>
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
    .logo img {
        height: calc(var(--header-height) - 2 * 10px); 
        width: auto;                 
        max-width: none;              
        object-fit: contain;
        display: block;
    }


    nav {
        display: flex;
        gap: 20px;
    }

</style>
