<?php
$stmt = $pdo->query("SELECT * FROM productos WHERE activo = 1 ORDER BY id DESC");
$productos = $stmt->fetchAll();
?>
<h2 class="mb-4 text-center">Catálogo de Productos</h2>
<div class="row row-cols-1 row-cols-md-3 g-4">
    <?php foreach ($productos as $p): ?>
    <div class="col">
        <div class="card h-100 shadow-sm">
            <div class="card-body">
                <h5 class="card-title"><?php echo htmlspecialchars($p['nombre']); ?></h5>
                <p class="card-text text-muted"><?php echo htmlspecialchars($p['descripcion']); ?></p>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <h4 class="text-primary">S/ <?php echo number_format($p['precio'], 2); ?></h4>
                    <button class="btn btn-outline-primary btn-sm">Agregar</button>
                </div>
            </div>
            <div class="card-footer text-muted small">SKU: <?php echo $p['sku']; ?></div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
