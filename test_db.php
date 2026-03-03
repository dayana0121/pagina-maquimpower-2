<?php
// test_db.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h2>PRUEBA 1: Conexión y Escritura Directa</h2>";

// 1. Probar ruta de inclusión
$ruta = __DIR__ . '/includes/db.php';
echo "Buscando DB en: $ruta <br>";

if (!file_exists($ruta)) {
    die("<span style='color:red'>[FALLO] No existe el archivo db.php</span>");
}

require_once $ruta;
echo "<span style='color:green'>[OK] Archivo db.php incluido.</span><br>";

// 2. Probar objeto PDO
if (!isset($pdo)) {
    die("<span style='color:red'>[FALLO] La variable \$pdo no está definida. Revisa db.php</span>");
}
echo "<span style='color:green'>[OK] Objeto PDO detectado.</span><br>";

// 3. Intentar Insertar un Usuario Falso
$nombre = "Test User " . rand(1,100);
$email = "test" . rand(1,1000) . "@prueba.com";
$pass = password_hash("123456", PASSWORD_BCRYPT);

$sql = "INSERT INTO usuarios (nombre, email, password, rol, marketing_opt_in) VALUES (?, ?, ?, 'cliente', 1)";

try {
    $stmt = $pdo->prepare($sql);
    if ($stmt->execute([$nombre, $email, $pass])) {
        echo "<h3 style='color:green'>[ÉXITO] SE LOGRÓ INSERTAR EN LA BD.</h3>";
        echo "ID Creado: " . $pdo->lastInsertId() . "<br>";
        echo "Usuario: $nombre / $email";
    } else {
        echo "<h3 style='color:red'>[ERROR SQL] La consulta falló.</h3>";
        print_r($stmt->errorInfo());
    }
} catch (Exception $e) {
    echo "<h3 style='color:red'>[EXCEPCIÓN] Error crítico:</h3>";
    echo $e->getMessage();
}
?>