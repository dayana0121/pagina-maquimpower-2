<?php

function get_env_custom($key, $default = null)
{
    static $config = null;

    if ($config === null) {
        $config = [];
        $path = dirname(__DIR__) . '/.env';

        if (file_exists($path)) {
            $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) {
                    continue;
                }

                if (strpos($line, '=') !== false) {
                    [$name, $value] = explode('=', $line, 2);
                    $config[trim($name)] = trim($value);
                }
            }
        }
    }

    return $config[$key] ?? $default;
}

$host = get_env_custom('DB_HOST', 'localhost');
$db = get_env_custom('DB_NAME', 'maquimpower_local');
$user = get_env_custom('DB_USER', 'root');
$pass = get_env_custom('DB_PASS', '');

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error de conexion a la base de datos: " . $e->getMessage());
}

?>
