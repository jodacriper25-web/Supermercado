<?php
session_start();

function generate_csrf() {
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function validate_csrf($token) {
    return isset($_SESSION['_csrf']) && hash_equals($_SESSION['_csrf'], $token);
}
