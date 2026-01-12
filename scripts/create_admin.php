<?php
require_once __DIR__ . '/../backend/src/db.php';
if (php_sapi_name() !== 'cli') { echo "Run via CLI\n"; exit; }
$email = $argv[1] ?? null; $name = $argv[2] ?? 'Admin'; $pass = $argv[3] ?? null;
if (!$email || !$pass) { echo "Usage: php create_admin.php email name password\n"; exit; }
$hash = password_hash($pass, PASSWORD_BCRYPT);
$stmt = $pdo->prepare('INSERT INTO users (name,email,password,role) VALUES (?,?,?,"admin")');
try{ $stmt->execute([$name,$email,$hash]); echo "Admin creado: $email\n"; } catch (PDOException $e){ echo "Error: " . $e->getMessage() . "\n"; }
