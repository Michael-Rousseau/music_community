<?php
namespace Core;

use Models\User;

class Auth {
    public static function login($user) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
    }

    public static function logout() {
        session_unset();
        session_destroy();
    }

    public static function check() {
        return isset($_SESSION['user_id']);
    }

    public static function user() {
        return self::check() ? $_SESSION : null;
    }

    public static function isAdmin() {
        return self::check() && ($_SESSION['role'] === 'admin');
    }
}
