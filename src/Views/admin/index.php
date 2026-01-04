<?php ob_start() ?>
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
            display: grid;
            grid-template-columns: 1fr;
            gap: 40px;
        }
        .admin-panel {
            background: var(--bg-card);
            border-radius: 20px;
            box-shadow: 0 10px 30px var(--shadow);
            padding: 25px;
            width: 100%;
        }
        .stat-card {
            background: var(--primary);
            color: #2D2828;
            padding: 20px;
            border-radius: 20px;
            text-align: center;
            font-weight: 800;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            min-width: 200px;
        }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { text-align: left; padding: 15px; color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase; border-bottom: 2px solid var(--border-color); }
        td { padding: 15px; border-bottom: 1px solid var(--border-color); color: var(--text-main); vertical-align: middle; }
        .user-avatar {
            width: 30px; height: 30px; 
            background: var(--bg-input); 
            border-radius: 50%; 
            display: inline-flex; 
            align-items: center; 
            justify-content: center;
            font-size: 0.8rem;
            margin-right: 10px;
            font-weight: bold;
        }
        .badge-admin {
            background: #FFD700;
            color: #5a4a00;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: bold;
        }
    </style>


    <div class="hero" style="padding-bottom: 20px;">
        <h1>Panneau d'Administration</h1>
        <p>Gérez les utilisateurs et le contenu de la plateforme.</p>
        
        <div style="display: flex; gap: 20px; justify-content: center; margin-top: 20px; flex-wrap: wrap;">
            <div class="stat-card">
                <i class="fas fa-users"></i> <?= count($users); ?> Utilisateurs
            </div>
            <div class="stat-card" style="background: var(--bg-card); color: var(--text-main); border: 1px solid var(--border-color);">
                <i class="fas fa-music"></i> <?= count($musics); ?> Musiques
            </div>
        </div>
    </div>

    <div class="admin-container">
        
        <div class="admin-panel">
            <h2 style="margin: 0; display:flex; align-items:center; gap:10px;">
                <i class="fas fa-user-shield" style="color:var(--primary);"></i> Utilisateurs
            </h2>
            
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Utilisateur</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th style="text-align:center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($users as $u): ?>
                        <tr>
                            <td>
                                <div style="display:flex; align-items:center;">
                                    <div class="user-avatar">
                                        <?= strtoupper(substr($u['username'], 0, 1)); ?>
                                    </div>
                                    <strong><?= htmlspecialchars($u['username']); ?></strong>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($u['email']); ?></td>
                            <td>
                                <?php if($u['role'] === 'admin'): ?>
                                    <span class="badge-admin">ADMIN</span>
                                <?php else: ?>
                                    <span style="color:var(--text-muted); font-size:0.9rem;">Membre</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align:center;">
                                <?php if($u['id'] != $_SESSION['user_id']):
                                    <?php if($u['role'] === 'admin'): ?>
                                        <a href="/admin?revoke_admin=<?= $u['id']; ?>" class="btn btn-secondary btn-sm" style="color:#ff9800; margin-right:5px;" onclick="return confirm('Retirer les droits admin à <?= htmlspecialchars($u['username']); ?> ?');" title="Révoquer admin">
                                            <i class="fas fa-user-minus"></i> Révoquer
                                        </a>
                                    <?php else: ?>
                                        <a href="/admin?promote_admin=<?= $u['id']; ?>" class="btn btn-secondary btn-sm" style="color:#28a745; margin-right:5px;" onclick="return confirm('Promouvoir <?= htmlspecialchars($u['username']); ?> en admin ?');" title="Promouvoir admin">
                                            <i class="fas fa-user-shield"></i> Promouvoir
                                        </a>
                                        <a href="/admin?del_user=<?= $u['id']; ?>" class="btn btn-danger btn-sm" style="color:#dc3545;" onclick="return confirm('Bannir cet utilisateur ?');">
                                            <i class="fas fa-ban"></i> Bannir
                                        </a>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span style="color:var(--text-muted); font-size:0.85rem; font-style:italic;">Vous</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="admin-panel">
            <h2 style="margin: 0; display:flex; align-items:center; gap:10px;">
                <i class="fas fa-compact-disc" style="color:var(--primary);"></i> Musiques
            </h2>

            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Titre</th>
                            <th>Auteur</th>
                            <th>Date</th>
                            <th style="text-align:center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($musics as $m): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($m['title']); ?></strong>
                            </td>
                            <td><?= htmlspecialchars($m['username']); ?></td>
                            <td style="color:var(--text-muted); font-size:0.85rem;">
                                <?= date('d/m/Y', strtotime($m['created_at'])); ?>
                            </td>
                            <td style="text-align:center;">
                                <a href="/music?id=<?= $m['id']; ?>" target="_blank" class="btn btn-secondary btn-sm" style="margin-right:5px;">
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                                <a href="/admin?del_music=<?= $m['id']; ?>" class="btn btn-danger btn-sm" style="color:#dc3545;" onclick="return confirm('Supprimer définitivement cette musique ?');">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
<?php
$content = ob_get_clean();
$title = "Admin";
include __DIR__ . "/../general/layout.php";
?>
