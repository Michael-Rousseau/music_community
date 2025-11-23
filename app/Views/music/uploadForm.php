<?php ob_start(); ?>

<h1>Uploader une musique</h1>

<?php if (!empty($error)): ?>
    <p class="error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" class="stack">

    <label for="title">Titre</label>
    <input type="text" name="title" id="title" required>

    <label for="description">Description</label>
    <textarea name="description" id="description" required></textarea>

    <label for="image">Image de couverture</label>
    <input type="file" name="image" id="image" accept=".jpg,.jpeg,.png,.webp" required>
    
    <label for="mp3">Fichier MP3</label>
    <input type="file" name="mp3" id="mp3" accept=".mp3" required>


    <button type="submit" class="primary">Uploader</button>
</form>
<!-- 
<?php
// echo "upload_max_filesize = " . ini_get('upload_max_filesize') . "<br>";
// echo "post_max_size = " . ini_get('post_max_size') . "<br>";

?> 
-->

<?php
$content = ob_get_clean();
$title = "Uploader une musique";
include __DIR__ . './../layout.php';
?>
