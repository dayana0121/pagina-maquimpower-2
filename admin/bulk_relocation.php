<?php
require_once 'check_auth.php';
require_once '../includes/db.php';

// 1. Obtener Jerarquía de Categorías para los selectores
$stmtCats = $pdo->query("SELECT * FROM categorias ORDER BY padre_id ASC, nombre ASC");
$allCats = $stmtCats->fetchAll();

// 2. Procesar Actualización Masiva
if (isset($_POST['bulk_update'])) {
    foreach ($_POST['categorias'] as $prod_id => $nuevo_nombre_cat) {
        if (!empty($nuevo_nombre_cat)) {
            $stmt = $pdo->prepare("UPDATE productos SET categoria = ? WHERE id = ?");
            $stmt->execute([$nuevo_nombre_cat, $prod_id]);
        }
    }
    $msg = "¡Sincronización masiva completada!";
}

// 3. Obtener todos los productos (los 78)
$productos = $pdo->query("SELECT id, nombre, categoria, sku, imagen_url FROM productos ORDER BY categoria ASC, nombre ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reposicionamiento Total | MaquimAdmin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root { --primary: #FF4500; --dark: #1a1a1a; }
        body { background: #f4f6f9; font-family: 'Inter', sans-serif; }
        .header-bulk { background: var(--dark); color: white; padding: 30px 0; border-bottom: 5px solid var(--primary); }
        .table-custom { background: white; border-radius: 15px; overflow: hidden; shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .img-thumb { width: 50px; height: 50px; object-fit: contain; border-radius: 5px; background: #eee; }
        .sticky-save { position: sticky; bottom: 20px; z-index: 100; display: flex; justify-content: center; }
        .btn-sync { background: var(--primary); color: white; padding: 15px 40px; border-radius: 50px; font-weight: 800; border: none; box-shadow: 0 10px 20px rgba(255, 69, 0, 0.4); transition: 0.3s; }
        .btn-sync:hover { transform: scale(1.05); background: #e03e00; }
    </style>
</head>
<body>

<div class="header-bulk mb-4">
    <div class="container d-flex justify-content-between align-items-center">
        <div>
            <h1 class="fw-black m-0"><i class="bi bi-grid-3x3-gap-fill me-2"></i> REPOSICIONAMIENTO TOTAL</h1>
            <p class="text-white-50 m-0">Organiza tus 78 productos en las nuevas categorías rápidamente</p>
        </div>
        <a href="dashboard.php" class="btn btn-outline-light rounded-pill px-4">Salir</a>
    </div>
</div>

<div class="container pb-5">
    <?php if(isset($msg)): ?>
        <div class="alert alert-success fw-bold shadow-sm mb-4"><i class="bi bi-check-circle-fill me-2"></i> <?php echo $msg; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="table-custom shadow-sm">
            <table class="table table-hover align-middle m-0">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-4">Producto</th>
                        <th>SKU</th>
                        <th>Categoría Actual</th>
                        <th class="pe-4" style="width: 350px;">Nueva Categoría (Jerarquía)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($productos as $p): ?>
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-3">
                                <img src="<?php echo $p['imagen_url']; ?>" class="img-thumb">
                                <span class="fw-bold small"><?php echo $p['nombre']; ?></span>
                            </div>
                        </td>
                        <td class="text-muted small"><?php echo $p['sku']; ?></td>
                        <td>
                            <span class="badge bg-light text-dark border"><?php echo $p['categoria'] ?: 'Sin categoría'; ?></span>
                        </td>
                        <td class="pe-4">
                            <select name="categorias[<?php echo $p['id']; ?>]" class="form-select form-select-sm border-primary">
                                <option value="">Mantener actual...</option>
                                <?php foreach($allCats as $cat): ?>
                                    <option value="<?php echo $cat['nombre']; ?>">
                                        <?php echo ($cat['padre_id'] ? '↳ ' : '📦 ') . $cat['nombre']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="sticky-save">
            <button type="submit" name="bulk_update" class="btn-sync">
                <i class="bi bi-cloud-arrow-up-fill me-2"></i> GUARDAR Y SINCRONIZAR TODO
            </button>
        </div>
    </form>
</div>

</body>
</html>