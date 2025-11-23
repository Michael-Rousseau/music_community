<?php
namespace Controllers;

class MusicController {
    public function show($id) {
        include __DIR__ . '../../Views/music/musicPage.php';
    }

    public function create() {
        include __DIR__ . '../../Views/music/uploadForm.php';
    }

    public function store() {
        // Placeholder for storing uploaded music
        echo "<p>Music uploaded successfully! Redirect to /m/new/success</p>";
    }

    public function success() {
        echo "<h1>Music uploaded successfully!</h1>";
    }
}
