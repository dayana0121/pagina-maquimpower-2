<?php
require_once '../includes/db.php';
require_once '../includes/mailer.php';

session_start();
header('Content-Type: application/json');

// 1. Recibir datos JSON del Frontend
$input = json_decode(file_get_contents('php://input'), true);
$carrito = $input['carrito'] ?? [];
$totalCalc = $input['total_calculado'] ?? 0;
$datosEnvio = $input['datos_envio'] ?? null;
$metodoPago = $input['metodo_pago'] ?? 'tarjeta';

if (empty($carrito) || !isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Sesión expirada o carrito vacío']);
    exit;
}

try {
    $pdo->beginTransaction();

    // 2. Restar stock y validar
    foreach ($carrito as $item) {
        $stmt = $pdo->prepare("UPDATE productos SET stock_actual = stock_actual - ? WHERE id = ? AND stock_actual >= ?");
        $stmt->execute([$item['cantidad'], $item['id'], $item['cantidad']]);

        if ($stmt->rowCount() == 0) {
            throw new Exception("Stock insuficiente para: " . $item['nombre']);
        }
    }

    // 3. INSERTAR PEDIDO
    $detalleJson = json_encode($carrito);
    $direccionJson = json_encode($datosEnvio);

    // Si es transferencia -> pendiente
    // Si es tarjeta -> pagado (aquí se asume que Niubiz ya validó o se validará después)
    $estadoInicial = ($metodoPago === 'transferencia') ? 'pendiente' : 'pagado';

    $stmtInsert = $pdo->prepare("INSERT INTO pedidos (usuario_id, total, estado, detalle_json, direccion_json, metodo_pago, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    $stmtInsert->execute([
        $_SESSION['user_id'],
        $totalCalc,
        $estadoInicial,
        $detalleJson,
        $direccionJson,
        $metodoPago
    ]);

    $pedidoId = $pdo->lastInsertId();

    // 4. Actualizar teléfono del usuario si viene en el checkout
    if (isset($datosEnvio['celular'])) {
        $stmtUser = $pdo->prepare("UPDATE usuarios SET telefono = ? WHERE id = ?");
        $stmtUser->execute([$datosEnvio['celular'], $_SESSION['user_id']]);
    }

    $pdo->commit();

    // 5. Enviar Correo (Fuera de la transacción para no bloquear)
    // Asumiendo que tienes una función enviarCorreoPedido en mailer.php
    if (function_exists('enviarCorreoPedido')) {
        $stmtU = $pdo->prepare("SELECT email, nombre FROM usuarios WHERE id = ?");
        $stmtU->execute([$_SESSION['user_id']]);
        $user = $stmtU->fetch();
        enviarCorreoPedido($user['email'], $user['nombre'], $pedidoId, $totalCalc, $carrito);
    }

    echo json_encode(['status' => 'success', 'order_id' => $pedidoId]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}