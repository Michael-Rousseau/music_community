<?php
ob_start();
?>
<h1>Mon profil</h1>
<p>Contenu du profil…</p>

<?php
$content = ob_get_clean();
$title = "Profil";
include __DIR__ . '/layout.php';
?>
