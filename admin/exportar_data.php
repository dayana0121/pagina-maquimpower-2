<?php
require_once 'check_auth.php';
require_once '../includes/db.php';

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=MaquimPower_Data_" . date('Y-m-d') . ".xls");

// 1. PRODUCTOS
echo "TABLA PRODUCTOS\n";
echo "ID\tSKU\tNOMBRE\tPRECIO\tSTOCK\tCATEGORIA\n";
$rows = $pdo->query("SELECT id, sku, nombre, precio, stock_actual, categoria FROM productos")->fetchAll(PDO::FETCH_ASSOC);
foreach($rows as $row) {
    echo implode("\t", array_values($row)) . "\n";
}

// 2. PEDIDOS
echo "\n\nTABLA PEDIDOS\n";
echo "ID\tUSUARIO_ID\tTOTAL\tESTADO\tFECHA\n";
$rows = $pdo->query("SELECT id, usuario_id, total, estado, created_at FROM pedidos")->fetchAll(PDO::FETCH_ASSOC);
foreach($rows as $row) {
    echo implode("\t", array_values($row)) . "\n";
}

// 3. CLIENTES
echo "\n\nTABLA CLIENTES\n";
echo "ID\tNOMBRE\tEMAIL\tROL\n";
$rows = $pdo->query("SELECT id, nombre, email, rol FROM usuarios")->fetchAll(PDO::FETCH_ASSOC);
foreach($rows as $row) {
    echo implode("\t", array_values($row)) . "\n";
}
exit;
?>