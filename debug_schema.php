<?php
require_once 'includes/db.php';

try {
    $stmt = $pdo->query("SHOW CREATE TABLE productos");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    print_r($row);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>