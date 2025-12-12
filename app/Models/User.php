<?php
namespace Models;

use PDO;

class User {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function findById($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }


    public function findByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function deleteByEmail($email)
    {
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE email = ?");
        $stmt->execute([$email]);
    }


    public function findByToken($token) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE token = ?");
        $stmt->execute([$token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($email, $username, $password) {
        $token = bin2hex(random_bytes(32));
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("INSERT INTO users (email, username, password, token) VALUES (?, ?, ?, ?)");
        $stmt->execute([$email, $username, $hashed, $token]);
        return $token;
    }

    public function verify($token) {
        $stmt = $this->pdo->prepare("UPDATE users SET token = NULL WHERE token = ?");
        return $stmt->execute([$token]);
    }

    public function validatePassword($user, $password) {
        return password_verify($password, $user['password']);
    }
}
