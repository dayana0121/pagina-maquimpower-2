<?php
// api/import_sheet.php
require_once '../includes/db.php';
header('Content-Type: application/json');

// --- MODO DETECTIVE: GUARDAR LOG ---
$logFile = 'log_importacion.txt';
function logMsg($msg) {
    global $logFile;
    file_put_contents($logFile, date('H:i:s') . " - " . print_r($msg, true) . "\n", FILE_APPEND);
}

// 1. Recibir Datos
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || !is_array($data)) {
    logMsg("ERROR: No llegaron datos JSON válidos.");
    echo json_encode(['status' => 'error', 'msg' => 'JSON invalido']);
    exit;
}

logMsg("RECIBIDOS " . count($data) . " PRODUCTOS DESDE N8N.");

$stats = ['insertados' => 0, 'errores' => 0];

$sql = "INSERT INTO productos (sku, nombre, slug, precio, stock_actual, categoria, descripcion, imagen_url, activo) 
        VALUES (:sku, :nombre, :slug, :precio, :stock, :cat, :desc, :img, 1)
        ON DUPLICATE KEY UPDATE 
        nombre = VALUES(nombre), 
        precio = VALUES(precio),
        stock_actual = VALUES(stock_actual),
        imagen_url = VALUES(imagen_url)";

$stmt = $pdo->prepare($sql);

foreach ($data as $index => $row) {
    try {
        // --- NORMALIZAR CLAVES (Hacer que no importe mayúsculas/minúsculas) ---
        // Convierte todas las llaves a MAYÚSCULAS (ej: 'precio' -> 'PRECIO')
        $row = array_change_key_case($row, CASE_UPPER);

        // Validar datos mínimos
        // Buscamos 'SKU' o 'ID', y 'NOMBRE' o 'PRODUCTO'
        $sku = $row['SKU'] ?? $row['ID'] ?? '';
        $nombre = $row['NOMBRE'] ?? $row['PRODUCTO'] ?? '';
        
        if (empty($sku) || empty($nombre)) {
            logMsg("Fila #$index ignorada: Falta SKU o NOMBRE. Datos: " . json_encode($row));
            continue;
        }

        // Limpiar Precio (Quitar 'S/', comas, espacios)
        $precioRaw = $row['PRECIO'] ?? 0;
        $precio = preg_replace('/[^0-9.]/', '', $precioRaw);
        
        // Slug
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $nombre)));

        // Imagen
        $img = $row['IMAGEN_URL'] ?? $row['IMAGEN'] ?? $row['FOTO'] ?? '/assets/img/no-photo.png';

        $stmt->execute([
            ':sku'    => $sku,
            ':nombre' => $nombre,
            ':slug'   => $slug,
            ':precio' => $precio,
            ':stock'  => $row['STOCK'] ?? 10,
            ':cat'    => $row['CATEGORIA'] ?? 'General',
            ':desc'   => $row['DESCRIPCION'] ?? '',
            ':img'    => $img
        ]);
        
        $stats['insertados']++;

    } catch (Exception $e) {
        $stats['errores']++;
        logMsg("Error SQL en SKU $sku: " . $e->getMessage());
    }
}

logMsg("FIN PROCESO. Insertados: " . $stats['insertados']);
echo json_encode(['status' => 'success', 'stats' => $stats]);
?>