<?php
require_once 'includes/db.php';

try {
    echo "Iniciando migración de base de datos...\n";

    // 1. Agregar columna 'estado_stock' si no existe
    $stmt = $pdo->query("SHOW COLUMNS FROM productos LIKE 'estado_stock'");
    $col = $stmt->fetch();

    if (!$col) {
        echo "Agregando columna 'estado_stock'...\n";
        $pdo->exec("ALTER TABLE productos ADD COLUMN estado_stock ENUM('automatico', 'en_stock', 'bajo_stock', 'agotado') DEFAULT 'automatico' AFTER stock_actual");
        echo "✅ Columna 'estado_stock' agregada correctamente.\n";
    } else {
        echo "ℹ️ La columna 'estado_stock' ya existe.\n";
    }

    // 2. Ampliar columna 'categoria' a VARCHAR(255) para evitar errores por longitud
    echo "Verificando longitud de columna 'categoria'...\n";
    $pdo->exec("ALTER TABLE productos MODIFY COLUMN categoria VARCHAR(255) DEFAULT NULL");
    echo "✅ Columna 'categoria' ampliada a VARCHAR(255).\n";

    echo "\n--- MIGRACIÓN COMPLETADA CON ÉXITO ---\n";

} catch (PDOException $e) {
    echo "❌ Error Crítico: " . $e->getMessage() . "\n";
}
?>