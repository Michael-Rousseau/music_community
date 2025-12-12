<?php ob_start(); ?>

<div style="max-width:500px; margin:0 auto;">
    <h1>Modifier la musique</h1>
    <div class="card">
        <form method="POST" action="<?= BASE_URL ?>/m/edit/<?= $music['id'] ?>" class="stack">
            
            <label>Titre</label>
            <input type="text" name="title" value="<?= htmlspecialchars($music['title']) ?>" required>
            
            <label>Description</label>
            <textarea name="description" rows="4"><?= htmlspecialchars($music['description']) ?></textarea>
            
            <label>Visibilité</label>
            <select name="visibility">
                <option value="public" <?= $music['visibility']=='public'?'selected':'' ?>>Public</option>
                <option value="private" <?= $music['visibility']=='private'?'selected':'' ?>>Privé</option>
            </select>
            
            <button type="submit" class="primary">Sauvegarder</button>
            <a href="<?= BASE_URL ?>/profile" style="display:block; text-align:center; margin-top:10px;">Annuler</a>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = "Modifier";
include __DIR__ . '/../layout.php';
?>
