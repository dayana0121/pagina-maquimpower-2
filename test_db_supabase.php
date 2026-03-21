<?php
require 'includes/db.php';
echo "✅ Conexión Supabase OK<br>";
$stmt = $pdo->query("SELECT COUNT(*) as total FROM productos");
$row = $stmt->fetch();
echo "Productos en DB: " . $row['total'];
?>

