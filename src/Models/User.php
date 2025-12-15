<?php
namespace App\Models;

use PDO;

class User {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function findByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function create($username, $email, $password, $token) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("INSERT INTO users (username, email, password, token) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$username, $email, $hash, $token]);
    }

    public function exists($email, $username) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? OR username = ?");
        $stmt->execute([$email, $username]);
        return $stmt->fetchColumn() > 0;
    }

    public function verifyToken($token) {
        $stmt = $this->pdo->prepare("UPDATE users SET token = NULL WHERE token = ?");
        $stmt->execute([$token]);
        return $stmt->rowCount() > 0;
    }

    // Admin methods
    public function getAll() {
        return $this->pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
