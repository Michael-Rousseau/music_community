<?php
ob_start();
?>
<h1>Uploader une musique</h1>
<form method='post' enctype='multipart/form-data' class="stack">
    <input type='text' name='title' placeholder='Title'><br>
    <textarea name='description' placeholder='Description'></textarea><br>
    <input type='file' name='mp3'><br>
    <button type='submit' class="primary">Upload</button>
</form>

<?php
$content = ob_get_clean();
$title = "Profil";
include __DIR__ . './../layout.php';
?>
