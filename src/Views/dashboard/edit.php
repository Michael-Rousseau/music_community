<?php
ob_start();
?>
    <style>

        form { 
            background: var(--bg-card); 
            padding: 30px; 
            border-radius: 20px; 
            width: 100%; 
            max-width: 400px; 
            box-shadow: 0 10px 30px var(--shadow); 
            color: var(--text-main);
        }
        input, textarea, select { 
            width: 100%; 
            padding: 12px; 
            margin: 10px 0 20px; 
            background: var(--bg-input);
            border: 1px solid var(--border-color);
            color: var(--text-main);
            border-radius: 8px;
            box-sizing: border-box;
        }
    </style>
    <form method="POST" class="auth-container">
        <h2 style="margin-top:0; text-align:center;">Modifier la musique</h2>
        
        <label>Titre</label>
        <input type="text" name="title" value="<?= htmlspecialchars($music['title']); ?>" required>
        
        <label>Description</label>
        <textarea name="description" rows="4"><?= htmlspecialchars($music['description']); ?></textarea>
        
        <label>Visibilité</label>
        <select name="visibility">
            <option value="public" <?= ($music['visibility']=='public') ? 'selected' : ''; ?>>Public</option>
            <option value="private" <?= ($music['visibility']=='private') ? 'selected' : ''; ?>>Privé</option>
        </select>
        
        <button type="submit" class="btn btn-primary" style="width:100%;">Sauvegarder</button>
        <p style="text-align:center; margin-top:15px;">
            <a href="<?= $basePath ?>/dashboard" style="color:var(--text-muted);">Annuler</a>
        </p>
    </form>
    
<?php
$content = ob_get_clean();
$title = "Modifier une musique";
include __DIR__ . "/../general/layout.php";
?>
