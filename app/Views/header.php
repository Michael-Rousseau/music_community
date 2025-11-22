<header class="site-header">
    <div class="header-container">
        <a href="/" class="logo">MyMusic</a>

        <nav>
            <?php if(\Core\Auth::check()): ?>
                <a href="/profile">Profil</a>
                <a href="/logout">Déconnexion</a>
            <?php else: ?>
                <a href="/login" class="login-button">Connexion</a>
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
        height: 60px;
        background: #111;
        color: #fff;
        display: flex;
        align-items: center;
        z-index: 1000;
        border-bottom: 1px solid #333;
    }

    .header-container {
        max-width: 1100px;
        margin: 0 auto;
        width: 100%;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0 20px;
    }

    .logo {
        color: #fff;
        text-decoration: none;
        font-size: 20px;
        font-weight: bold;
    }

    nav a {
        color: #fff;
        text-decoration: none;
        margin-left: 20px;
        font-size: 16px;
    }

    .login-button {
        padding: 6px 12px;
        background: #fff;
        color: #111 !important;
        border-radius: 4px;
    }

    body {
        padding-top: 60px; /* ensures content is not behind the header */
    }
</style>
