<?php
namespace App\Controllers;

use App\Config\Database;
use App\Models\Music;

class HomeController extends Controller {
    public function index() {
        $pdo = Database::getConnection();
        $musicModel = new Music($pdo);
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

        // paginattion
        $perPage = 12; 
        $offset = ($page - 1) * $perPage;

        $search = isset($_GET['q']) ? trim($_GET['q']) : '';
        $musics = $musicModel->findAllPublic($search, $perPage, $offset);
        $totalMusics = $musicModel->countPublic($search);

        $totalPages = ceil($totalMusics / $perPage);

        $this->render('home/index', [
            'musics' => $musics,
            'search' => $search,
            'currentPage' => $page,
            'totalPages' => $totalPages
        ]);
    }
}
