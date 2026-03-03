<?php
session_start();
echo "<h1>Prueba de Acceso</h1>";
echo "ID de Sesion actual: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'Ninguna');
?>