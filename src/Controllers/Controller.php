<?php
namespace App\Controllers;

class Controller {
    public $basePath = "";

    protected function render($view, $data = []) {
        extract($data); // Converts array keys to variables ($music, $comments, etc.)
        
        $basePath = $this->basePath;
        $viewFile = __DIR__ . "/../Views/$view.php";
        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            die("View '$view' not found!");
        }
    }
    
    protected function redirect($url) {
        header("Location: $url");
        exit();
    }
}
