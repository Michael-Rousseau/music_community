<?php
ob_start();
?>


<h1>Music Page #<?= $id ?></h1>

<?php
$content = ob_get_clean();
$title = "Musique";
include __DIR__ . './../layout.php';
?>
