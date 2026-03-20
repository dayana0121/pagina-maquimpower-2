<?php
// admin/check_auth.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. ¿Está logueado?
if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit;
}

// 2. ¿Es Admin? (Seguridad RBAC)
if ($_SESSION['user_role'] !== 'admin') {
    // Si es cliente y trata de entrar, lo mandamos al home
    header("Location: /index.php");
    exit;
}
?>