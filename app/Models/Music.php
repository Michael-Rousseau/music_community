<?php
namespace Models;

use PDO;

class Music {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // --- Create ---
    public function create($data) {
        $stmt = $this->pdo->prepare("INSERT INTO musics (user_id, title, description, filename, image, visibility) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$data['user_id'], $data['title'], $data['description'], $data['filename'], $data['image'], $data['visibility'] ?? 'public']);
    }

    // --- Read ---
    public function find($id) {
        $stmt = $this->pdo->prepare("SELECT m.*, u.username FROM musics m JOIN users u ON m.user_id = u.id WHERE m.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function findAllPublic($search = '') {
        $sql = "SELECT m.*, u.username, u.avatar FROM musics m JOIN users u ON m.user_id = u.id WHERE m.visibility = 'public'";
        if ($search) $sql .= " AND m.title LIKE :search";
        $sql .= " ORDER BY m.created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        if ($search) $stmt->bindValue(':search', "%$search%");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findAllByUser($userId) {
        $stmt = $this->pdo->prepare("SELECT * FROM musics WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    // --- Update ---
    public function update($id, $title, $description, $visibility) {
        $stmt = $this->pdo->prepare("UPDATE musics SET title = ?, description = ?, visibility = ? WHERE id = ?");
        return $stmt->execute([$title, $description, $visibility, $id]);
    }

    // --- Delete ---
    public function delete($id) {
        // First get filename to delete file
        $music = $this->find($id);
        if ($music) {
            $filePath = __DIR__ . '/../../public/uploads/mp3/' . $music['filename'];
            $imgPath = __DIR__ . '/../../public/uploads/images/' . $music['image'];
            if (file_exists($filePath)) unlink($filePath);
            if ($music['image'] !== 'default.jpg' && file_exists($imgPath)) unlink($imgPath);
        }
        $stmt = $this->pdo->prepare("DELETE FROM musics WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // --- Player Features ---
    public function getComments($musicId) {
        $stmt = $this->pdo->prepare("SELECT c.*, u.username FROM comments c JOIN users u ON c.user_id = u.id WHERE c.music_id = ? ORDER BY c.timestamp ASC");
        $stmt->execute([$musicId]);
        return $stmt->fetchAll();
    }

    public function getAvgRating($musicId) {
        $stmt = $this->pdo->prepare("SELECT AVG(value) as moy FROM ratings WHERE music_id = ?");
        $stmt->execute([$musicId]);
        $res = $stmt->fetch();
        return $res['moy'] ? round($res['moy'], 1) : 0;
    }

    public function addComment($userId, $musicId, $content, $timestamp = 0) {
        $stmt = $this->pdo->prepare("INSERT INTO comments (user_id, music_id, content, timestamp) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$userId, $musicId, $content, $timestamp]);
    }

    public function addRating($userId, $musicId, $value) {
        $stmt = $this->pdo->prepare("INSERT INTO ratings (user_id, music_id, value) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE value = ?");
        return $stmt->execute([$userId, $musicId, $value, $value]);
    }
}
