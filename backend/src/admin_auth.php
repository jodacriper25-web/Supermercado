<?php
require_once __DIR__ . '/db.php';

/* =========================
   VERIFICAR SI ES ADMIN
========================= */
function isAdmin(): bool {
    return isset($_SESSION['user'])
        && isset($_SESSION['user']['role'])
        && $_SESSION['user']['role'] === 'admin';
}

/* =========================
   PROTEGER RUTAS ADMIN
========================= */
function requireAdmin(): void {
    if (!isAdmin()) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error' => 'Acceso denegado (solo administrador)'
        ]);
        exit;
    }
}

/* =========================
   LOGIN ADMIN
========================= */
function adminLogin(string $email, string $password): bool {
    global $pdo;

    $stmt = $pdo->prepare(
        "SELECT id, name, email, password, role
         FROM users
         WHERE email = ? AND role = 'admin'
         LIMIT 1"
    );
    $stmt->execute([$email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin) return false;
    if (!password_verify($password, $admin['password'])) return false;

    // USAMOS LA MISMA SESIÓN QUE EL USUARIO NORMAL
    $_SESSION['user'] = [
        'id'    => $admin['id'],
        'name'  => $admin['name'],
        'email' => $admin['email'],
        'role'  => $admin['role'] // === admin
    ];

    return true;
}
