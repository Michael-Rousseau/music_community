<?php ob_start(); ?>

<h1>Musique uploadée avec succès 🎵</h1>
<p>Votre fichier a été enregistré.</p>

<a href="<?= BASE_URL ?>/m/new" class="primary">Uploader une autre musique</a>

<?php
$content = ob_get_clean();
$title = "Success";
include __DIR__ . './../layout.php';
?>
