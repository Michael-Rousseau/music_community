<?php
namespace Controllers;

class HomeController {
    public function index() {

        // For now, just a placeholder
        echo "<h1>Welcome to the Music Community!</h1>";
        echo "<p>Here will be the 10 latest musics.</p>";

        include __DIR__ . '../../Views/home.php';
    }
}
