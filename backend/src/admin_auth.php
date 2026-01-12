<?php
session_start();
require_once __DIR__ . '/db.php';

function isAdmin(){
    return isset($_SESSION['user']) && $_SESSION['user']['role']==='admin';
}

function requireAdmin(){
    if (!isAdmin()){ http_response_code(403); echo json_encode(['error'=>'Forbidden']); exit; }
}

function adminLogin($email, $password){ global $pdo; $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1'); $stmt->execute([$email]); $user = $stmt->fetch(); if ($user && password_verify($password, $user['password']) && $user['role']==='admin'){ $_SESSION['user'] = ['id'=>$user['id'],'name'=>$user['name'],'email'=>$user['email'],'role'=>$user['role']]; return true; } return false; }
?>