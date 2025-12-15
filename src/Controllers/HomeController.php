<?php
namespace App\Controllers;

use App\Config\Database;
use App\Models\Music;

class HomeController extends Controller {
    public function index() {
        $pdo = Database::getConnection();
        $musicModel = new Music($pdo);
        
        $search = isset($_GET['q']) ? trim($_GET['q']) : '';
        $musics = $musicModel->findAllPublic($search);

        $this->render('home/index', [
            'musics' => $musics,
            'search' => $search
        ]);
    }
}
