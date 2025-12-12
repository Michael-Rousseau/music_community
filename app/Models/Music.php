<?php
namespace Models;

use PDO;

class Music {

    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function create($data)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO musics (user_id, title, description, filename, image)
            VALUES (?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $data['user_id'],
            $data['title'],
            $data['description'],
            $data['filename'],
            $data['image']
        ]);
    }

    public function find($id)
    {
        $stmt = $this->pdo->prepare("
            SELECT m.*, u.username 
            FROM musics m 
            JOIN users u ON m.user_id = u.id 
            WHERE m.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getComments($musicId)
    {
        $stmt = $this->pdo->prepare("
            SELECT c.*, u.username 
            FROM comments c 
            JOIN users u ON c.user_id = u.id 
            WHERE c.music_id = ? 
            ORDER BY c.timestamp ASC
        ");
        $stmt->execute([$musicId]);
        return $stmt->fetchAll();
    }

    public function getAvgRating($musicId)
    {
        $stmt = $this->pdo->prepare("SELECT AVG(value) as moy FROM ratings WHERE music_id = ?");
        $stmt->execute([$musicId]);
        $res = $stmt->fetch();
        return $res['moy'] ? round($res['moy'], 1) : 0;
    }

    public function addComment($userId, $musicId, $content, $timestamp = 0)
    {
        $stmt = $this->pdo->prepare("INSERT INTO comments (user_id, music_id, content, timestamp) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$userId, $musicId, $content, $timestamp]);
    }
    
    public function addRating($userId, $musicId, $value)
    {
        $stmt = $this->pdo->prepare("INSERT INTO ratings (user_id, music_id, value) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE value = ?");
        return $stmt->execute([$userId, $musicId, $value, $value]);
    }
}
