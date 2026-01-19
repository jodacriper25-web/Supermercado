<?php
session_start();
require_once __DIR__ . '/db.php';

function isAdmin(){
    return isset($_SESSION['user']) && $_SESSION['user']['role']==='admin';
}

function requireAdmin(){
    if (!isAdmin()){ http_response_code(403); echo json_encode(['error'=>'Forbidden']); exit; }
}

function adminLogin($email, $password){
    global $pdo;

    $stmt = $pdo->prepare(
      "SELECT * FROM users WHERE email = ? AND role = 'admin' LIMIT 1"
    );
    $stmt->execute([$email]);
    $admin = $stmt->fetch();

    if (!$admin) return false;

    if (!password_verify($password, $admin['password'])) return false;

    $_SESSION['admin'] = [
        'id' => $admin['id'],
        'name' => $admin['name'],
        'email' => $admin['email']
    ];

    return true;
}
?>