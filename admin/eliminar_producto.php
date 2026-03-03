<?php
require_once 'check_auth.php';
require_once '../includes/db.php';

$id = isset($_GET['id']) ? $_GET['id'] : 0;

if ($id > 0) {
    $stmt = $pdo->prepare("DELETE FROM productos WHERE id = ?");
    $stmt->execute([$id]);
}

header("Location: dashboard.php?msg=eliminado");
exit;
?>