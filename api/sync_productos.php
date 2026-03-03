<?php
// /api/sync_productos.php (VERSIÓN FINAL)
header('Content-Type: application/json');
require_once '../includes/db.php';

// 1. Validar Token
$headers = getallheaders();
$auth_token = isset($headers['Authorization']) ? $headers['Authorization'] : '';
if (strpos($auth_token, 'Bearer ') === 0) $auth_token = substr($auth_token, 7);

if ($auth_token !== API_SECRET_KEY) {
    http_response_code(403);
    echo json_encode(['error' => 'Acceso denegado']);
    exit;
}

// 2. Recibir Datos
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(['error' => 'Sin datos']);
    exit;
}

// 3. Guardar en BD
$stats = ['ok' => 0, 'error' => 0];
$sql = "INSERT INTO productos (sku, nombre, slug, descripcion, precio, stock_actual, imagen_url, categoria, activo) 
        VALUES (:sku, :nombre, :slug, :desc, :prec, :stk, :img, :cat, 1)
        ON DUPLICATE KEY UPDATE 
        nombre=VALUES(nombre), precio=VALUES(precio), stock_actual=VALUES(stock_actual), imagen_url=VALUES(imagen_url)";

$stmt = $pdo->prepare($sql);

foreach ($data as $prod) {
    try {
        // Parche por si n8n envía envuelto en "productos"
        if (!isset($prod['sku']) && isset($data['productos'])) {
             // Si llegamos aquí por error de flujo, lo ignoramos o manejamos lógica extra
             continue; 
        }

        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $prod['nombre'])));
        $stmt->execute([
            ':sku' => $prod['sku'],
            ':nombre' => $prod['nombre'],
            ':slug' => $slug,
            ':desc' => $prod['descripcion'] ?? '',
            ':prec' => $prod['precio'],
            ':stk' => $prod['stock'],
            ':img' => $prod['imagen_url'] ?? '',
            ':cat' => $prod['categoria'] ?? 'General'
        ]);
        $stats['ok']++;
    } catch (Exception $e) {
        $stats['error']++;
    }
}
echo json_encode(['status' => 'success', 'stats' => $stats]);
?>