<?php
require_once 'includes/db.php';
// Configura tu usuario aquí
$email = "Admin@maquimpower.com";
$pass = "Maquim2026"; // TU CONTRASEÑA MAESTRA

$hash = password_hash($pass, PASSWORD_BCRYPT);

$sql = "INSERT INTO usuarios (nombre, email, password, rol) VALUES ('Admin Principal', ?, ?, 'admin')";
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email, $hash]);
    echo "Usuario Admin creado con éxito. <br>Email: $email <br>Pass: $pass <br>BORRA ESTE ARCHIVO AHORA.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>