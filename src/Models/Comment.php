<?php
namespace App\Models;

use PDO;

class Comment {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getAllForMusic($musicId) {
        $stmt = $this->pdo->prepare("SELECT c.*, u.username, u.avatar FROM comments c JOIN users u ON c.user_id = u.id WHERE c.music_id = ? ORDER BY c.timestamp ASC");
        $stmt->execute([$musicId]);
        return $stmt->fetchAll();
    }

    public function create($userId, $musicId, $content, $timestamp) {
        $stmt = $this->pdo->prepare("INSERT INTO comments (user_id, music_id, content, timestamp) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$userId, $musicId, $content, $timestamp]);
    }
}