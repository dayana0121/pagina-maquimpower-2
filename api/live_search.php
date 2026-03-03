<?php
// 1. Silenciar errores visuales para no romper el JSON
error_reporting(0); 
ini_set('display_errors', 0);

// 2. Iniciar buffer de salida (captura cualquier texto basura)
ob_start();

header('Content-Type: application/json');

try {
    // Verificar ruta
    if (!file_exists('../includes/db.php')) {
        throw new Exception("No se encuentra db.php");
    }
    require_once '../includes/db.php';

    // Capturar búsqueda
    $q = isset($_GET['q']) ? trim($_GET['q']) : '';

    // Si es muy corto, devolver array vacío
    if (strlen($q) < 2) {
        ob_clean(); // Limpiar basura
        echo json_encode([]);
        exit;
    }

    // Consulta SQL segura
    $stmt = $pdo->prepare("
        SELECT id, nombre, slug, precio, imagen_url 
        FROM productos 
        WHERE (nombre LIKE ? OR categoria LIKE ? OR sku LIKE ?) AND activo = 1 
        ORDER BY stock_actual DESC 
        LIMIT 6
    ");

    $term = "%$q%";
    $stmt->execute([$term, $term, $term]);
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Limpiar cualquier HTML previo (warnings, espacios)
    ob_clean();
    
    // Enviar JSON puro
    echo json_encode($resultados);

} catch (Exception $e) {
    // En caso de error fatal, enviar JSON vacío o log
    ob_clean();
    echo json_encode([]); 
}
?>