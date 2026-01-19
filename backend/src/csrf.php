<?php
// NO session_start aquí

function generate_csrf(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function validate_csrf(string $token): bool {
    return isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
}
