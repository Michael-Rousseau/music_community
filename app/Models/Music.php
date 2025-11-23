<?php
namespace Models;

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
        $stmt = $this->pdo->prepare("SELECT * FROM musics WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}
