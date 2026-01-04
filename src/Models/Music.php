<?php
namespace App\Models;

use PDO;

class Music {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    // for home page
    public function findAllPublic($search = '', $limit = 20, $offset = 0) {
        $sql = "SELECT m.*, u.username, u.avatar FROM musics m 
            JOIN users u ON m.user_id = u.id 
            WHERE m.visibility = 'public'";
        if ($search) {
            $sql .= " AND m.title LIKE :search";
        }
        $sql .= " ORDER BY m.created_at DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        if ($search) $stmt->bindValue(':search', "%$search%");

        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function countPublic($search = '') {
        $sql = "SELECT COUNT(*) FROM musics m WHERE m.visibility = 'public'";
        if ($search) {
            $sql .= " AND m.title LIKE :search";
        }
        $stmt = $this->pdo->prepare($sql);
        if ($search) $stmt->bindValue(':search', "%$search%");
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    // for single music page
    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT m.*, u.username, u.avatar FROM musics m JOIN users u ON m.user_id = u.id WHERE m.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // For Dashboard
    public function findAllByUser($userId) {
        $stmt = $this->pdo->prepare("SELECT * FROM musics WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function create($userId, $title, $description, $filename, $visibility) {
        $sql = "INSERT INTO musics (user_id, title, description, filename, visibility) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$userId, $title, $description, $filename, $visibility]);
    }

    public function update($id, $userId, $title, $description, $visibility) {
        $sql = "UPDATE musics SET title=?, description=?, visibility=? WHERE id=? AND user_id=?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$title, $description, $visibility, $id, $userId]);
    }

    public function delete($id, $userId) {
        // First get filename to delete from disk
        $stmt = $this->pdo->prepare("SELECT filename FROM musics WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $userId]);
        $filename = $stmt->fetchColumn();

        if ($filename) {
            $path = __DIR__ . '/../../public/uploads/mp3/' . $filename;
            if (file_exists($path)) unlink($path);
        }

        $stmt = $this->pdo->prepare("DELETE FROM musics WHERE id = ? AND user_id = ?");
        return $stmt->execute([$id, $userId]);
    }

    // For Admin
    public function getAllForAdmin() {
        return $this->pdo->query("SELECT m.*, u.username FROM musics m JOIN users u ON m.user_id = u.id ORDER BY m.created_at DESC")->fetchAll();
    }

    public function deleteByAdmin($id) {
        $stmt = $this->pdo->prepare("SELECT filename FROM musics WHERE id = ?");
        $stmt->execute([$id]);
        $filename = $stmt->fetchColumn();

        if ($filename) {
            $path = __DIR__ . '/../../public/uploads/mp3/' . $filename;
            if (file_exists($path)) unlink($path);
        }

        $stmt = $this->pdo->prepare("DELETE FROM musics WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // ratings
    public function getAvgRating($musicId) {
        $stmt = $this->pdo->prepare("SELECT AVG(value) as moy FROM ratings WHERE music_id = ?");
        $stmt->execute([$musicId]);
        $res = $stmt->fetch();
        return $res ? round($res['moy'], 1) : 0;
    }

    public function addRating($userId, $musicId, $value) {
        $stmt = $this->pdo->prepare("INSERT INTO ratings (user_id, music_id, value) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE value = ?");
        return $stmt->execute([$userId, $musicId, $value, $value]);
    }
}
