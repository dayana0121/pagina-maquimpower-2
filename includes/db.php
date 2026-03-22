<?php
// Datos oficiales de tu panel de Hostinger
$host = "localhost";
$db = "u264219614_maquim"; // Nombre exacto de tu imagen
$user = "u264219614_root";   // Usuario exacto de tu imagen
$pass = "km8W|06x0%b%"; // La que elegiste al crearla en Hostinger

/**
 * Helper para cargar variables de entorno desde .env
 */
function get_env_custom($key, $default = null)
{
    static $config = null;
    if ($config === null) {
        $path = dirname(__DIR__) . '/.env';
        if (file_exists($path)) {
            $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0)
                    continue;
                if (strpos($line, '=') !== false) {
                    list($name, $value) = explode('=', $line, 2);
                    $config[trim($name)] = trim($value);
                }
            }
        } else {
            $config = [];
        }
    }
    return $config[$key] ?? $default;
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error de conexión en Hostinger: " . $e->getMessage());
}
?>