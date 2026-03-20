<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

// Lógica de Búsqueda Flexible
if ($q != '') {
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE (nombre LIKE ? OR sku LIKE ? OR categoria LIKE ?) AND activo = 1");
    $term = "%$q%";
    $stmt->execute([$term, $term, $term]);
    $productos = $stmt->fetchAll();
} else {
    $productos = [];
}

$listaCategorias = $pdo->query("SELECT DISTINCT categoria FROM productos WHERE categoria != '' ORDER BY categoria")->fetchAll(PDO::FETCH_COLUMN);
?>

<!-- BANNER HERO (Altura ajustada para móvil) -->
<div class="position-relative bg-dark overflow-hidden hero-search">
    <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(135deg, #1a1a1a 0%, #333 100%);"></div>
    <div class="container position-relative z-2 h-100 d-flex flex-column justify-content-center text-center text-lg-start">
        <h6 class="text-primary fw-bold text-uppercase ls-2 mb-2 small">RESULTADOS DE BÚSQUEDA</h6>
        <h1 class="display-5 fw-black text-white text-uppercase" style="font-size: clamp(1.5rem, 5vw, 3rem);">"<?php echo htmlspecialchars($q); ?>"</h1>
        <p class="text-white-50 m-0"><?php echo count($productos); ?> coincidencias</p>
    </div>
</div>

<div class="container py-4 py-lg-5">
    <div class="row">
        
        <!-- SIDEBAR (Solo Desktop) -->
        <div class="col-lg-3 d-none d-lg-block">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 sticky-top" style="top: 100px; z-index: 1;">
                <div class="card-header bg-white fw-bold py-3 border-bottom">FILTRAR POR</div>
                <div class="list-group list-group-flush" style="max-height: 400px; overflow-y: auto;">
                    <a href="categoria.php?c=todo" class="list-group-item list-group-item-action fw-bold text-primary">Ver Todo</a>
                    <?php foreach($listaCategorias as $c): ?>
                        <a href="categoria.php?c=<?php echo urlencode($c); ?>" class="list-group-item list-group-item-action text-muted small py-2">
                            <?php echo $c; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- RESULTADOS -->
        <div class="col-lg-9">
            
            <!-- Barra de Herramientas Móvil (Opcional) -->
            <div class="d-lg-none mb-3 d-flex justify-content-between align-items-center">
                <span class="text-muted small"><?php echo count($productos); ?> Productos</span>
                <a href="categoria.php?c=todo" class="btn btn-sm btn-outline-dark rounded-pill">Ver Categorías</a>
            </div>

            <?php if (empty($productos)): ?>
                <div class="text-center py-5 bg-light rounded-4 border border-dashed mx-3 mx-lg-0">
                    <i class="bi bi-search display-1 text-muted opacity-25"></i>
                    <h3 class="mt-3 fw-bold text-dark fs-5">Sin resultados</h3>
                    <p class="text-muted small">Intenta con otra palabra clave.</p>
                    <a href="/" class="btn btn-primary rounded-pill px-4 fw-bold btn-sm">Ir al Inicio</a>
                </div>
            <?php else: ?>

                <!-- GRILLA RESPONSIVE (2 columnas en móvil, 3 en tablet, 3 en PC) -->
                <div class="row row-cols-2 row-cols-md-3 g-2 g-lg-4">
                    <?php foreach ($productos as $p): 
                         $agotado = ($p['stock_actual'] <= 0);
                         $img = !empty($p['imagen_url']) ? $p['imagen_url'] : '/assets/img/no-photo.png';
                         $link = "/producto/" . $p['slug']; 
                    ?>
                    <div class="col">
                        <a href="<?php echo $link; ?>" class="text-decoration-none">
                            <div class="prod-card h-100 bg-white shadow-sm border-0 position-relative overflow-hidden group-hover">
                                <?php if($agotado): ?>
                                    <div class="badge bg-secondary position-absolute top-0 start-0 m-2 z-2" style="font-size:0.6rem;">AGOTADO</div>
                                <?php elseif($p['precio_oferta'] > 0): ?>
                                    <div class="badge bg-danger position-absolute top-0 start-0 m-2 z-2" style="font-size:0.6rem;">OFERTA</div>
                                <?php endif; ?>
                                
                                <!-- Imagen (Altura ajustada móvil) -->
                                <div class="img-wrap p-2 d-flex align-items-center justify-content-center bg-light">
                                    <img src="<?php echo $img; ?>" class="img-fluid" style="max-height: 100%; transition: transform 0.3s;">
                                </div>
                                
                                <div class="d-flex flex-column h-100 p-2 p-lg-3 pt-2">
                                    <!-- Categoría oculta en móvil para ahorrar espacio -->
                                    <small class="text-muted text-uppercase mb-1 d-none d-md-block" style="font-size:0.65rem;"><?php echo substr($p['categoria'],0,20); ?></small>
                                    
                                    <h6 class="p-title text-dark fw-bold text-truncate mb-1" style="font-size: 0.85rem;"><?php echo htmlspecialchars($p['nombre']); ?></h6>
                                    
                                    <div class="mt-auto">
                                        <?php if($p['precio_oferta'] > 0): ?>
                                            <small class="text-decoration-line-through text-muted d-block" style="font-size:0.7rem">S/ <?php echo number_format($p['precio'], 2); ?></small>
                                            <span class="p-price text-danger fw-black mb-0" style="font-size: 1rem;">S/ <?php echo number_format($p['precio_oferta'], 2); ?></span>
                                        <?php else: ?>
                                            <span class="p-price text-dark fw-black mb-0" style="font-size: 1rem;">S/ <?php echo number_format($p['precio'], 2); ?></span>
                                        <?php endif; ?>
                                        
                                        <!-- Botón solo en PC -->
                                        <button class="btn btn-sm btn-dark rounded-circle float-end d-none d-md-block"><i class="bi bi-plus"></i></button>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>

            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>