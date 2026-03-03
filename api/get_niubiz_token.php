<?php
require_once '../includes/NiubizAPI.php';
header('Content-Type: application/json');

// Recibir monto total del JS
$input = json_decode(file_get_contents('php://input'), true);
$monto = $input['total'] ?? 0;

if ($monto <= 0) {
    echo json_encode(['error' => 'Monto inválido']);
    exit;
}

try {
    $niubiz = new NiubizAPI();
    
    // 1. Token de Seguridad
    $token = $niubiz->getSecurityToken();
    
    // 2. Token de Sesión
    $sessionKey = $niubiz->createSession($monto, $token);
    
    echo json_encode([
        'sessionKey' => $sessionKey,
        'merchantId' => $niubiz->getMerchantId()
    ]);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}