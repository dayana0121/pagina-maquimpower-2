<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

// Buscar en BD
if($q != '') {
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE (nombre LIKE ? OR sku LIKE ?) AND activo = 1");
    $stmt->execute(["%$q%", "%$q%"]);
    $resultados = $stmt->fetchAll();
} else {
    $resultados = [];
}
?>

<div class="container py-5">
    <h2 class="fw-bold mb-4">Resultados para: "<?php echo htmlspecialchars($q); ?>"</h2>

    <?php if(empty($resultados)): ?>
        <div class="alert alert-warning">No encontramos productos con ese nombre o código.</div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-4 g-4">
            <?php foreach ($resultados as $p): 
                 $agotado = ($p['stock_actual'] <= 0);
                 $img = !empty($p['imagen_url']) ? $p['imagen_url'] : '/assets/img/no-photo.png';
                 $link = "/producto/" . $p['slug']; 
            ?>
            <div class="col">
                 <!-- Reutiliza aquí tu tarjeta de producto del index -->
                 <div class="prod-card">
                     <div class="img-wrap"><img src="<?php echo $img; ?>"></div>
                     <h5 class="p-title"><a href="<?php echo $link; ?>" class="text-decoration-none text-dark"><?php echo $p['nombre']; ?></a></h5>
                     <span class="p-price">S/ <?php echo number_format($p['precio'], 2); ?></span>
                     <a href="<?php echo $link; ?>" class="btn-add text-center text-decoration-none d-block">Ver Detalle</a>
                 </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>