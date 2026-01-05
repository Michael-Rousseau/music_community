<?php
ob_start();
?>
    <div class="hero">
        <h1>404</h1>
        <a class="btn btn-primary">Retour à l'accueil</a>
    </div>


<?php
$content = ob_get_clean();
$title = "404";
include __DIR__ . "/../general/layout.php";
?>
