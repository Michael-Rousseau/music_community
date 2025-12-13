<?php
namespace App\Controllers;

use App\Config\Database;
use App\Models\Music;
use App\Models\Comment;

class MusicController extends Controller {
    
    public function show() {
        // 1. Setup
        if (!isset($_GET['id'])) die("ID manquant");
        $musicId = (int)$_GET['id'];
        
        // Start session if not already started (needed for user_id check)
        if (session_status() === PHP_SESSION_NONE) session_start();
        $userId = $_SESSION['user_id'] ?? null;

        $pdo = Database::getConnection();
        $musicModel = new Music($pdo);
        $commentModel = new Comment($pdo);

        // 2. Handle POST Actions (Comments & Ratings)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $userId) {
            // Handle Comment
            if (isset($_POST['comment'])) {
                $content = trim($_POST['comment']);
                $timestamp = (int)($_POST['timestamp'] ?? 0);
                if (!empty($content)) {
                    $commentModel->create($userId, $musicId, $content, $timestamp);
                    $this->redirect("/music?id=$musicId&drawer=open");
                }
            }
            // Handle Rating
            if (isset($_POST['rating'])) {
                $val = (int)$_POST['rating'];
                if ($val >= 1 && $val <= 5) {
                    $musicModel->addRating($userId, $musicId, $val);
                    $this->redirect("/music?id=$musicId");
                }
            }
        }

        // 3. Fetch Data for View
        $music = $musicModel->findById($musicId);
        if (!$music) die("Musique introuvable");

        $avgRating = $musicModel->getAvgRating($musicId);
        $comments = $commentModel->getAllForMusic($musicId);
        $openDrawer = (isset($_GET['drawer']) && $_GET['drawer'] === 'open') ? 'open' : '';

        // 4. Render View
        $this->render('music/show', [
            'music' => $music,
            'comments' => $comments,
            'avgRating' => $avgRating,
            'openDrawer' => $openDrawer,
            'isUserLoggedIn' => $userId ? true : false
        ]);
    }
}
