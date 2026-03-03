<?php
require_once '../includes/db.php';
header('Content-Type: application/json');

$accion = $_GET['accion'] ?? '';
$filtro = $_GET['filtro'] ?? '';

if ($accion == 'departamentos') {
    $stmt = $pdo->query("SELECT DISTINCT departamento FROM ubigeo ORDER BY departamento");
    echo json_encode($stmt->fetchAll(PDO::FETCH_COLUMN));
} 
elseif ($accion == 'provincias') {
    $stmt = $pdo->prepare("SELECT DISTINCT provincia FROM ubigeo WHERE departamento = ? ORDER BY provincia");
    $stmt->execute([$filtro]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_COLUMN));
} 
elseif ($accion == 'distritos') {
    $stmt = $pdo->prepare("SELECT id_ubigeo, distrito FROM ubigeo WHERE provincia = ? ORDER BY distrito");
    $stmt->execute([$filtro]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
}
?>