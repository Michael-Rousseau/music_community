CREATE DATABASE IF NOT EXISTS music_community CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE music_community;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL, 
    token VARCHAR(255) NULL, 
    role ENUM('user', 'admin') DEFAULT 'user',
    avatar VARCHAR(255) DEFAULT 'default_avatar.png',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE musics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    filename VARCHAR(255) NOT NULL, 
    image VARCHAR(255) DEFAULT 'default_image.png',
    number_listening INT DEFAULT NULL,
    visibility ENUM('public', 'private') DEFAULT 'public',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    music_id INT NOT NULL,
    content TEXT NOT NULL,
    timestamp INT DEFAULT 0,
    visibility ENUM('visible', 'hidden') DEFAULT 'visible',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    answer_to INT DEFAULT NULL, 
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (music_id) REFERENCES musics(id) ON DELETE CASCADE,
    FOREIGN KEY (answer_to) REFERENCES comments(id) ON DELETE CASCADE
);

CREATE TABLE ratings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    music_id INT NOT NULL,
    value INT NOT NULL CHECK (value >= 1 AND value <= 5),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (music_id) REFERENCES musics(id) ON DELETE CASCADE,
    UNIQUE KEY unique_rating (user_id, music_id)
);