<?php
require_once 'includes/db.php';

try {
    echo "Checking database columns...\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM productos LIKE 'estado_stock'");
    $col = $stmt->fetch();

    if (!$col) {
        echo "Adding 'estado_stock' column...\n";
        // Adding the column. We use ENUM but also handle potential future cases by allowed default.
        // It should be safe to run on existing table.
        $pdo->exec("ALTER TABLE productos ADD COLUMN estado_stock ENUM('automatico', 'en_stock', 'bajo_stock', 'agotado') DEFAULT 'automatico' AFTER stock_actual");
        echo "Column 'estado_stock' added successfully.\n";
    } else {
        echo "Column 'estado_stock' already exists.\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>