<?php

use Core\Auth;

function requireLogin() {
    if (!Auth::check()) {
        header("Location: /login");
        exit;
    }
}
