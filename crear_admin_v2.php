<?php
require_once 'includes/db.php';

$email = "admin@maquimpower.com";
$pass = "Maquim2026"; 

// 1. Borrar admin anterior si existe (para evitar duplicados)
$pdo->prepare("DELETE FROM usuarios WHERE email = ?")->execute([$email]);

// 2. Crear nuevo
$hash = password_hash($pass, PASSWORD_BCRYPT);
$sql = "INSERT INTO usuarios (nombre, email, password, rol) VALUES ('Super Admin', ?, ?, 'admin')";

if($pdo->prepare($sql)->execute([$email, $hash])) {
    echo "<h1>ADMIN CREADO</h1>";
    echo "Email: $email <br> Pass: $pass";
} else {
    echo "Error al crear.";
}
?>