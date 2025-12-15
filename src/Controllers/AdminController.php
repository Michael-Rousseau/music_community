<?php
namespace App\Controllers;

use App\Config\Database;
use App\Models\User;
use App\Models\Music;

class AdminController extends Controller {
    
    public function index() {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            $this->redirect('/');
        }

        $pdo = Database::getConnection();
        $userModel = new User($pdo);
        $musicModel = new Music($pdo);

        // Handle Actions
        if (isset($_GET['del_user'])) {
            $userModel->delete((int)$_GET['del_user']);
            $this->redirect('/admin');
        }
        if (isset($_GET['del_music'])) {
            $musicModel->deleteByAdmin((int)$_GET['del_music']);
            $this->redirect('/admin');
        }

        $this->render('admin/index', [
            'users' => $userModel->getAll(),
            'musics' => $musicModel->getAllForAdmin()
        ]);
    }
}
