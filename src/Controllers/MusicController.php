<?php
namespace App\Controllers;

use App\Config\Database;
use App\Models\Music;
use App\Models\Comment;

class MusicController extends Controller {

    // music stream
    public function stream() {
        // Get Music ID
        if (!isset($_GET['id'])) {
            header("HTTP/1.1 400 Bad Request"); 
            exit;
        }
        $id = (int)$_GET['id'];

        // Fetch filename from DB
        $pdo = Database::getConnection();
        $musicModel = new Music($pdo);
        $music = $musicModel->findById($id);

        if (!$music) {
            header("HTTP/1.1 404 Not Found");
            exit;
        }

        // Define Path
        $filePath = __DIR__ . '/../../public/uploads/mp3/' . $music['filename'];

        if (!file_exists($filePath)) {
            header("HTTP/1.1 404 Not Found");
            exit;
        }

        // handle range / streaming
        $fileSize = filesize($filePath);
        $start = 0;
        $end = $fileSize - 1;

        // check if browser requested a partial range (seeking)
        if (isset($_SERVER['HTTP_RANGE'])) {
            $c_start = $start;
            $c_end = $end;

            list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
            if (strpos($range, ',') !== false) {
                header('HTTP/1.1 416 Requested Range Not Satisfiable');
                header("Content-Range: bytes $start-$end/$fileSize");
                exit;
            }
            
            if ($range == '-') {
                $c_start = $fileSize - substr($range, 1);
            } else {
                $range = explode('-', $range);
                $c_start = $range[0];
                $c_end = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $c_end;
            }

            $c_end = ($c_end > $end) ? $end : $c_end;
            
            if ($c_start > $c_end || $c_start > $fileSize - 1 || $c_end >= $fileSize) {
                header('HTTP/1.1 416 Requested Range Not Satisfiable');
                header("Content-Range: bytes $start-$end/$fileSize");
                exit;
            }
            $start = $c_start;
            $end = $c_end;
            $length = $end - $start + 1;
            
            fseek($fp = fopen($filePath, 'rb'), $start);
            header('HTTP/1.1 206 Partial Content');
            header("Content-Range: bytes $start-$end/$fileSize");
        } else {
            // full content
            $length = $fileSize;
            $fp = fopen($filePath, 'rb');
        }

        // Send Headers
        header("Content-Type: audio/mpeg");
        header("Content-Length: " . $length);
        header("Content-Disposition: inline; filename=\"" . $music['title'] . ".mp3\"");
        header("Accept-Ranges: bytes");

        // Output Data in 8KB chunks
        $buffer = 8192;
        while (!feof($fp) && ($p = ftell($fp)) <= $end) {
            if ($p + $buffer > $end) {
                $buffer = $end - $p + 1;
            }
            echo fread($fp, $buffer);
            flush();
        }
        fclose($fp);
        exit;
    }
    
    public function show() {
        if (!isset($_GET['id'])) die("ID manquant");
        $musicId = (int)$_GET['id'];
        
        // start session if not already started 
        if (session_status() === PHP_SESSION_NONE) session_start();
        $userId = $_SESSION['user_id'] ?? null;

        $pdo = Database::getConnection();
        $musicModel = new Music($pdo);
        $commentModel = new Comment($pdo);

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $userId) {
            // handle comment
            if (isset($_POST['comment'])) {
                $content = trim($_POST['comment']);
                $timestamp = (int)($_POST['timestamp'] ?? 0);
                if (!empty($content)) {
                    $commentModel->create($userId, $musicId, $content, $timestamp);
                    $this->redirect("/music?id=$musicId&drawer=open");
                }
            }
            // handle rating
            if (isset($_POST['rating'])) {
                $val = (int)$_POST['rating'];
                if ($val >= 1 && $val <= 5) {
                    $musicModel->addRating($userId, $musicId, $val);
                    $this->redirect("/music?id=$musicId");
                }
            }
        }

        // fetch data for view
        $music = $musicModel->findById($musicId);
        if (!$music) die("Musique introuvable");

        $avgRating = $musicModel->getAvgRating($musicId);
        $comments = $commentModel->getAllForMusic($musicId);
        $openDrawer = (isset($_GET['drawer']) && $_GET['drawer'] === 'open') ? 'open' : '';

        // render view
        $this->render('music/show', [
            'music' => $music,
            'comments' => $comments,
            'avgRating' => $avgRating,
            'openDrawer' => $openDrawer,
            'isUserLoggedIn' => $userId ? true : false
        ]);
    }
}
