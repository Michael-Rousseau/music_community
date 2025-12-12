<?php
namespace Controllers;

use Models\Music;

class HomeController {
    
    private $pdo;
    private $musicModel;

    // The Router needs to inject PDO here. 
    // *NOTE*: Your Router class creates "new $controller()". 
    // You might need to edit Router.php to pass $pdo, OR use global $pdo.
    // For simplicity, let's assume we pass $pdo in constructor manually or use a simple Dependency Injection.
    // FIX: Let's use `global $pdo` inside the controller methods for now to keep it simple without changing Router logic too much.

    public function index() {
        global $pdo; // Get the connection created in index.php
        $model = new Music($pdo);

        $search = isset($_GET['q']) ? trim($_GET['q']) : '';
        $musics = $model->findAllPublic($search);

        include __DIR__ . '/../Views/home.php';
    }
}
