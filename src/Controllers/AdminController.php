<?php
namespace App\Controllers;

use App\Config\Database;
use App\Models\User;
use App\Models\Music;

class AdminController extends Controller {
    
    public function index() {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            $this->redirect($this->basePath .'/');
        }

        $pdo = Database::getConnection();
        $userModel = new User($pdo);
        $musicModel = new Music($pdo);

        // Handle Actions
        if (isset($_GET['del_user'])) {
            $userId = (int)$_GET['del_user'];
            // Prevent deleting yourself
            if ($userId != $_SESSION['user_id']) {
                $userModel->delete($userId);
            }
            $this->redirect($this->basePath .'/admin');
        }
        
        if (isset($_GET['promote_admin'])) {
            $userId = (int)$_GET['promote_admin'];
            if ($userId != $_SESSION['user_id']) {
                $userModel->updateRole($userId, 'admin');
            }
            $this->redirect($this->basePath .'/admin');
        }
        
        if (isset($_GET['revoke_admin'])) {
            $userId = (int)$_GET['revoke_admin'];
            if ($userId != $_SESSION['user_id']) {
                $userModel->updateRole($userId, 'user');
            }
            $this->redirect($this->basePath .'/admin');
        }
        
        if (isset($_GET['del_music'])) {
            $musicModel->deleteByAdmin((int)$_GET['del_music']);
            $this->redirect($this->basePath .'/admin');
        }

        $this->render('admin/index', [
            'users' => $userModel->getAll(),
            'musics' => $musicModel->getAllForAdmin()
        ]);
    }
}
