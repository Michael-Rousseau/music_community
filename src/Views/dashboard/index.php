<style>
    .dashboard-container { max-width: 1200px; margin: 40px auto; padding: 0 20px; display: grid; grid-template-columns: 1fr 2fr; gap: 30px; }
    @media (max-width: 768px) { .dashboard-container { grid-template-columns: 1fr; } }
    .avatar-circle { width: 100px; height: 100px; background-color: var(--primary); color: var(--dark); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; font-weight: 800; margin: 0 auto 15px; }
    .music-item { background: var(--bg-card); border-radius: 12px; padding: 20px; margin-bottom: 20px; box-shadow: 0 4px 15px var(--shadow); display: flex; justify-content: space-between; align-items: center; border-left: 5px solid var(--primary); transition: transform 0.2s, background 0.3s; }
    .music-item:hover { transform: translateX(5px); }
    .music-info h3 { margin: 0 0 5px 0; font-size: 1.2rem; font-family: 'Montserrat', sans-serif; font-weight: 700; color: var(--text-main); }
    .music-meta { font-size: 0.85rem; color: var(--text-muted); margin-bottom: 10px; display: block; }
    .actions { display: flex; gap: 10px; }
    .btn-icon { background: none; border: none; cursor: pointer; font-size: 1.2rem; transition: 0.2s; color: var(--text-muted); }
    .btn-icon:hover { transform: scale(1.2); color: var(--primary); }
    .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: bold; text-align: center; }
    .alert.success { background: #d4edda; color: #155724; }
    .alert.error { background: #f8d7da; color: #721c24; }
</style>

<div class="dashboard-container">

    <div class="left-column">

        <div class="card" style="text-align:center; margin-bottom: 30px; width: 100%; max-width: none;">
            <div class="card-body">
                <?php if (!empty($user_avatar) && $user_avatar !== 'default_avatar.png'): ?>
                    <img src="/uploads/avatars/<?= htmlspecialchars($user_avatar); ?>"
                         style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; margin: 0 auto 15px; display: block; border: 3px solid var(--primary);">
                <?php else: ?>
                    <div class="avatar-circle">
                        <?= strtoupper(substr($_SESSION['user_name'], 0, 1)); ?>
                    </div>
                <?php endif; ?>
                <h2 style="font-size: 1.5rem; margin-bottom:5px; color:var(--text-main);">
                    <?= htmlspecialchars($_SESSION['user_name']); ?>
                </h2>
                <p style="color:var(--text-muted); margin:0 0 15px 0;">
                    <?= isset($_SESSION['user_role']) ? ucfirst($_SESSION['user_role']) : 'Membre'; ?>
                </p>

                <!-- Avatar Upload Form -->
                <form method="POST" enctype="multipart/form-data" style="margin-top: 15px;">
                    <input type="hidden" name="action" value="upload_avatar">
                    <input type="file" name="avatar_file" accept="image/jpeg,image/png,image/jpg,image/gif"
                           id="avatarInput" style="display:none;" onchange="this.form.submit()">
                    <label for="avatarInput" class="btn btn-secondary" style="cursor:pointer; font-size:0.85rem; padding:8px 16px;">
                        <i class="fas fa-camera"></i> Changer la photo
                    </label>
                </form>
            </div>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert <?= $message_type; ?>" style="margin-bottom: 20px;">
                <?= htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="card" style="width: 100%; max-width: none;">
            <div class="card-body">
                <h2 style="font-size: 1.3rem; margin-bottom:20px; color:var(--text-main);">Ajouter une musique</h2>

                <form action="/dashboard" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="upload">

                    <label>Titre</label>
                    <input type="text" name="title" required placeholder="Ex: Mon super remix">

                    <label>Fichier MP3</label>
                    <input type="file" name="music_file" accept=".mp3,audio/mpeg" required>

                    <label>Description</label>
                    <textarea name="description" rows="3" placeholder="Racontez l'histoire..."></textarea>

                    <label>Visibilité</label>
                    <select name="visibility">
                        <option value="public">Public (Tout le monde)</option>
                        <option value="private">Privé (Moi seul)</option>
                    </select>

                    <button type="submit" class="btn btn-primary" style="width:100%; border-radius:50px;">Uploader 🚀</button>
                </form>
            </div>
        </div>
    </div>

    <div class="right-column">
        <h2 style="margin-bottom: 20px; color:var(--text-main);">Mes Musiques (<?= count($my_musics); ?>)</h2>

        <?php if (count($my_musics) > 0): ?>
            <div class="music-list">
                <?php foreach ($my_musics as $music): ?>
                    <div class="music-item">
                        <div class="music-info" style="flex: 1;">
                            <h3><?= htmlspecialchars($music['title']); ?></h3>
                            <span class="music-meta">
                                <?= ($music['visibility'] == 'public') ? '🌍 Public' : '🔒 Privé'; ?>
                                • Ajouté le <?= date('d/m/Y', strtotime($music['created_at'])); ?>
                            </span>
                                <audio controls style="margin-top:10px; height: 30px; width: 100%; max-width: 300px;">
                                    <source src="/music/stream?id=<?= $music['id']; ?>" type="audio/mpeg">
                                        Votre navigateur ne supporte pas l'audio.
                                </audio>
                        </div>

                        <div class="actions">
                            <a href="/dashboard/edit?id=<?= $music['id']; ?>" class="btn-icon" title="Modifier">
                                <i class="fas fa-pen"></i>
                            </a>
                            <a href="/dashboard/delete?id=<?= $music['id']; ?>" class="btn-icon" style="color:#dc3545;" title="Supprimer" onclick="return confirm('Êtes-vous sûr ?');">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="card" style="text-align:center; padding: 40px; width: 100%; max-width: none;">
                <p style="color:var(--text-muted); font-size:1.2rem;">Vous n'avez pas encore posté de musique.</p>
            </div>
        <?php endif; ?>
    </div>

</div>
