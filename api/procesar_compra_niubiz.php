<?php
require_once '../includes/db.php';
// Aquí deberías incluir la lógica de guardar pedido que hicimos antes

$transactionToken = $_POST['transactionToken'] ?? null;

if ($transactionToken) {
    // AQUI OCURRE LA MAGIA DE VALIDACIÓN FINAL CON NIUBIZ
    // (Niubiz requiere una llamada final de Autorización usando este token)
    
    // Si la respuesta es exitosa (codigo 000):
    // 1. Guardar Pedido en BD con estado 'pagado'.
    // 2. Redirigir a success.php
    
    echo "Pago recibido. Token: " . $transactionToken;
    // TODO: Implementar la llamada de autorización final (Paso 4 del flujo Niubiz)
} else {
    // Pago fallido o cancelado
    header("Location: /checkout.php?error=pago_cancelado");
}
?>