<?php
require_once __DIR__ . '/db.php';

function registerUser($name, $email, $password) {
    global $pdo;
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
    try {
        $stmt->execute([$name, $email, $hash]);
        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        return false;
    }
}

function loginUser($email, $password) {
    global $pdo;
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password'])) {
        // Normalmente iniciar sesión, crear token, etc.
        return $user;
    }
    return false;
}
