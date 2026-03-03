<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔍 Diagnóstico de Sistema</h2>";

// 1. Verificar archivos críticos
$archivos = ['includes/db.php', 'includes/header.php', '.htaccess'];
foreach ($archivos as $archivo) {
    if (file_exists($archivo)) {
        echo "✅ El archivo <b>$archivo</b> existe.<br>";
    } else {
        echo "❌ ERROR: No se encuentra <b>$archivo</b>.<br>";
    }
}

// 2. Probar Conexión a Base de Datos
echo "<h3>📡 Probando Base de Datos...</h3>";
include 'includes/db.php';

if (isset($pdo)) {
    echo "✅ Variable \$pdo detectada.<br>";
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM categorias");
        $count = $stmt->fetchColumn();
        echo "✅ Conexión exitosa. Categorías encontradas: <b>$count</b><br>";
    } catch (Exception $e) {
        echo "❌ ERROR en consulta: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ ERROR: La conexión (\$pdo) no está definida en db.php.<br>";
}

// 3. Probar Sesiones
echo "<h3>🔑 Probando Sesiones...</h3>";
session_start();
if (session_id()) {
    echo "✅ Las sesiones están activas.<br>";
} else {
    echo "❌ ERROR: Las sesiones no funcionan en este servidor.<br>";
}
?>