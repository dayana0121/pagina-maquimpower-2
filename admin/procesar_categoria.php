<?php
require_once 'check_auth.php';
require_once '../includes/db.php';

// ELIMINAR
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    // Opcional: Verificar si tiene hijos antes de borrar
    $stmt = $pdo->prepare("DELETE FROM categorias WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: categorias_manager.php?status=deleted");
    exit;
}

// EDITAR O CREAR
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $padre_id = !empty($_POST['padre_id']) ? $_POST['padre_id'] : NULL;
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $nombre)));

    if (!empty($_POST['id'])) {
        // UPDATE
        $sql = "UPDATE categorias SET nombre = ?, padre_id = ?, slug = ? WHERE id = ?";
        $pdo->prepare($sql)->execute([$nombre, $padre_id, $slug, $_POST['id']]);
    } else {
        // INSERT
        $sql = "INSERT INTO categorias (nombre, padre_id, slug) VALUES (?, ?, ?)";
        $pdo->prepare($sql)->execute([$nombre, $padre_id, $slug]);
    }
    header("Location: categorias_manager.php?status=success");
    exit;
}