<?php
session_start();
echo "<h1>ESTADO DE SESIÓN</h1>";
echo "ID de Sesión: " . session_id() . "<br>";
echo "<h3>Datos guardados:</h3>";
echo "<pre>";
var_dump($_SESSION);
echo "</pre>";

if(empty($_SESSION)) {
    echo "<h2 style='color:red'>LA SESIÓN ESTÁ VACÍA (Error de Servidor)</h2>";
    echo "<p>Solución: Ejecutar 'chmod 777 /tmp' en el contenedor.</p>";
} else {
    echo "<h2 style='color:green'>¡HAY DATOS! (Error de Código)</h2>";
    echo "<p>Solución: Revisa el 'if' en header.php</p>";
}
?>