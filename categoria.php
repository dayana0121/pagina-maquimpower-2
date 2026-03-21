<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

// --- FUNCTION AUXILIAR: OBTENER TODOS LOS IDs DE SUBCATEGORÍAS (RECURSIVO) ---
/**
 * Obtiene todos los IDs de una categoría y sus subcategorías recursivamente.
 * Evita N+1 queries al obtener todo de una sola pasada.
 */
function getSubcategoryIds($pdo, $catId) {
    $ids = [$catId];
    $stack = [$catId];
    
    while (!empty($stack)) {
        $currentId = array_pop($stack);
        $stmt = $pdo->prepare("SELECT id FROM categorias WHERE padre_id = ? ORDER BY orden ASC");
        $stmt->execute([$currentId]);
        $children = $stmt->fetchAll();
        
        foreach ($children as $child) {
            $ids[] = $child['id'];
            $stack[] = $child['id'];
        }
    }
    
    error_log("[categoria.php] getSubcategoryIds($catId) retornó: " . json_encode($ids));
    return $ids;
}

// --- CONTROLADOR LÓGICO ---

$slug = isset($_GET['slug']) ? trim($_GET['slug']) : (isset($_GET['c']) ? trim($_GET['c']) : '');
$search = isset($_GET['q']) ? trim($_GET['q']) : '';

$modo = 'error'; 
$titulo = "CATÁLOGO";
$data = []; 
$breadcrumbs = [];
$categoriaActual = null;

try {
    if ($search) {
        // --- CASO 1: BÚSQUEDA ---
        $modo = 'search';
        $titulo = 'RESULTADOS: "' . htmlspecialchars($search) . '"';
        $stmt = $pdo->prepare("SELECT * FROM productos WHERE activo = 1 AND (nombre LIKE ? OR sku LIKE ?) ORDER BY id ASC");
        $stmt->execute(["%$search%", "%$search%"]);
        $data = $stmt->fetchAll();

    } elseif ($slug) {
        // --- CASO 2: NAVEGACIÓN POR CATEGORÍA ---
        
        // 1. Obtener datos de la categoría actual (ID y NOMBRE son vitales)
        $stmt = $pdo->prepare("SELECT * FROM categorias WHERE slug = ?");
        $stmt->execute([$slug]);
        $categoriaActual = $stmt->fetch();

        if ($categoriaActual) {
            $titulo = strtoupper($categoriaActual['nombre']);
            $catId = $categoriaActual['id'];
            
            error_log("[categoria.php] Cargando categoría: $slug (ID: $catId)");
            
            // Breadcrumbs
            $tempCat = $categoriaActual;
            while ($tempCat) {
                array_unshift($breadcrumbs, $tempCat);
                if ($tempCat['padre_id']) {
                    $stmtP = $pdo->prepare("SELECT * FROM categorias WHERE id = ?");
                    $stmtP->execute([$tempCat['padre_id']]);
                    $tempCat = $stmtP->fetch();
                } else {
                    $tempCat = null;
                }
            }

            // 2. BUSCAR HIJOS (Para saber si es Padre/Abuelo o Hoja final)
            $stmtHijos = $pdo->prepare("SELECT * FROM categorias WHERE padre_id = ? ORDER BY orden ASC");
            $stmtHijos->execute([$catId]);
            $hijos = $stmtHijos->fetchAll();

            if (count($hijos) > 0) {
                // === ES UNA CATEGORÍA PADRE (Ej: MAQUINARIAS o LIMPIEZA) ===
                $modo = 'parent';
                error_log("[categoria.php] Modo PARENT detectado. Hijos encontrados: " . count($hijos));
                
                foreach ($hijos as $hijo) {
                    // Verificamos si este hijo tiene a su vez hijos (Nietos)
                    $stmtNietos = $pdo->prepare("SELECT * FROM categorias WHERE padre_id = ? ORDER BY orden ASC");
                    $stmtNietos->execute([$hijo['id']]);
                    $nietos = $stmtNietos->fetchAll();

                    if (count($nietos) > 0) {
                        // SI TIENE NIETOS: Mostramos los productos de los NIETOS
                        error_log("[categoria.php] Hijo '{$hijo['nombre']}' tiene " . count($nietos) . " nietos");
                        
                        foreach ($nietos as $nieto) {
                            // ✅ OPTIMIZADO: Buscar por categoria_id en lugar de nombre
                            $stmtProd = $pdo->prepare("SELECT * FROM productos WHERE categoria_id = ? AND activo = 1 ORDER BY id DESC LIMIT 12");
                            $stmtProd->execute([$nieto['id']]);
                            $productosEncontrados = $stmtProd->fetchAll();

                            if (count($productosEncontrados) > 0) {
                                error_log("[categoria.php] Nieto '{$nieto['nombre']}' (ID: {$nieto['id']}) → " . count($productosEncontrados) . " productos");
                                $data[] = ['info' => $nieto, 'productos' => $productosEncontrados];
                            } else {
                                error_log("[categoria.php] ⚠️ Nieto '{$nieto['nombre']}' (ID: {$nieto['id']}) NO tiene productos en BD");
                            }
                        }
                    } else {
                        // SI NO TIENE NIETOS: Mostramos productos del HIJO directo
                        error_log("[categoria.php] Hijo '{$hijo['nombre']}' (ID: {$hijo['id']}) NO tiene nietos, buscando productos directos");
                        
                        // ✅ OPTIMIZADO: Buscar por categoria_id en lugar de nombre
                        $stmtProd = $pdo->prepare("SELECT * FROM productos WHERE categoria_id = ? AND activo = 1 ORDER BY id DESC LIMIT 12");
                        $stmtProd->execute([$hijo['id']]);
                        $productosEncontrados = $stmtProd->fetchAll();

                        if (count($productosEncontrados) > 0) {
                            error_log("[categoria.php] Hijo '{$hijo['nombre']}' (ID: {$hijo['id']}) → " . count($productosEncontrados) . " productos");
                            $data[] = ['info' => $hijo, 'productos' => $productosEncontrados];
                        } else {
                            error_log("[categoria.php] ⚠️ Hijo '{$hijo['nombre']}' (ID: {$hijo['id']}) NO tiene productos en BD");
                        }
                    }
                }
            } else {
                // === ES UNA CATEGORÍA FINAL (Ej: PULIDORAS) ===
                $modo = 'leaf';
                error_log("[categoria.php] Modo LEAF detectado para: {$categoriaActual['nombre']} (ID: $catId)");
                
                // ✅ OPTIMIZADO: Buscar por categoria_id en lugar de nombre
                $stmt = $pdo->prepare("SELECT * FROM productos WHERE categoria_id = ? AND activo = 1 ORDER BY id DESC");
                $stmt->execute([$catId]);
                $data = $stmt->fetchAll();
                
                error_log("[categoria.php] Productos encontrados para categoria_id=$catId: " . count($data));
            }
        } else {
            // Slug especial "ofertas"
            if ($slug == 'ofertas') {
                $modo = 'leaf';
                $titulo = "OFERTAS ESPECIALES";
                $data = $pdo->query("SELECT * FROM productos WHERE etiqueta = 'OFERTA' AND activo = 1")->fetchAll();
                error_log("[categoria.php] Modo OFERTAS: " . count($data) . " productos encontrados");
            } else {
                $titulo = "CATEGORÍA NO ENCONTRADA";
                $modo = 'error';
            }
        }

    } else {
        // --- CASO 3: CATÁLOGO COMPLETO ---
        $modo = 'leaf'; 
        $titulo = "CATÁLOGO COMPLETO";
        $data = $pdo->query("SELECT * FROM productos WHERE activo = 1 ORDER BY id DESC LIMIT 100")->fetchAll();
    }

} catch (Exception $e) {
    error_log($e->getMessage());
    $modo = 'error';
}

// --- HELPER RENDER (PREMIUM CARD) ---
function renderProductCard($p, $isSlider = false) {
    $agotado = ($p['stock_actual'] <= 0);
    $img = !empty($p['imagen_url']) ? $p['imagen_url'] : '/assets/img/no-photo.png';
    $img = str_replace('/var/www/html', '', $img);
    $link = "/producto/" . $p['slug']; 
    
    $tieneOferta = ($p['precio_oferta'] > 0);
    $precioShow = $tieneOferta ? $p['precio_oferta'] : $p['precio'];
    $descuento = $tieneOferta ? round((($p['precio'] - $p['precio_oferta']) / $p['precio']) * 100) : 0;

    $wrapperClass = $isSlider ? 'px-2 h-100' : 'col';
    // Clase 'reveal-item' para el JS de animación
    if (!$isSlider) {
        $wrapperClass .= ' reveal-item';
    }

    ob_start(); 
    ?>
    <div class="<?= $wrapperClass ?>">
        <a href="<?= $link ?>" class="text-decoration-none product-card-link">
            <div class="prod-card h-100 position-relative bg-white border border-light rounded-4 overflow-hidden p-2">
                
                <!-- EFECTO HOVER OVERLAY (Brillo sutil) -->
                <div class="card-overlay"></div>

                <!-- BADGES CON GRADIENTE -->
                <div class="d-flex justify-content-between w-100 position-absolute top-0 start-0 p-3 z-3">
                    <?php if($agotado): ?>
                        <span class="badge bg-dark text-white shadow-sm" style="font-size:0.65rem; backdrop-filter: blur(5px);">AGOTADO</span>
                    <?php elseif($tieneOferta): ?>
                        <span class="badge gradient-danger fw-bold shadow-sm" style="font-size:0.65rem">-<?= $descuento ?>%</span>
                    <?php else: ?>
                        <span class="badge gradient-primary text-white fw-bold shadow-sm" style="font-size:0.65rem">NUEVO</span>
                    <?php endif; ?>
                </div>

                <!-- IMAGEN CON SKELETON -->
                <div class="img-wrap mb-3 mt-3 d-flex align-items-center justify-content-center position-relative" style="height: 170px;">
                    <div class="skeleton position-absolute w-100 h-100 top-0 start-0 z-1 rounded-3"></div>
                    <img src="<?= $img ?>" alt="<?= htmlspecialchars($p['nombre']) ?>" 
                         class="img-fluid position-relative z-2 fade-in-img" 
                         style="max-height: 100%; opacity: 0; transition: transform 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);"
                         onload="this.style.opacity=1; this.previousElementSibling.style.display='none';">
                </div>

                <!-- INFO -->
                <div class="prod-info pt-3 px-1 border-top border-light position-relative z-2 bg-white">
                    <small class="text-muted text-uppercase d-block mb-1 font-monospace" style="font-size:0.6rem;">
                        SKU: <?= substr($p['sku'], 0, 10) ?>
                    </small>
                    
                    <h6 class="p-title text-dark fw-bold mb-2 text-truncate" style="font-size: 0.9rem; letter-spacing: -0.3px;">
                        <?= htmlspecialchars($p['nombre']) ?>
                    </h6>
                    
                    <div class="d-flex justify-content-between align-items-end mt-3">
                        <div class="price-box lh-1">
                            <?php if($tieneOferta): ?>
                                <small class="text-decoration-line-through text-muted fw-semibold" style="font-size:0.7rem">S/ <?= number_format($p['precio'], 2) ?></small>
                                <div class="p-price text-danger fw-black" style="font-size: 1.1rem;">S/ <?= number_format($precioShow, 2) ?></div>
                            <?php else: ?>
                                <div class="p-price text-dark fw-black" style="font-size: 1.1rem;">S/ <?= number_format($precioShow, 2) ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if(!$agotado): ?>
                        <div class="btn-add-mini rounded-circle d-flex align-items-center justify-content-center shadow-sm">
                            <i class="bi bi-bag-plus-fill"></i>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <?php
    return ob_get_clean();
}
?>
<!-- HEADER BANNER (Más limpio) -->
<!-- HEADER CINEMÁTICO -->
<div class="position-relative overflow-hidden py-5 py-lg-6" style="background-color: #050505; min-height: 350px; display: flex; align-items: center;">
    
    <!-- FONDO ANIMADO -->
    <div class="cat-header-overlay"></div>
    <div class="position-absolute w-100 h-100 top-0 start-0 bg-pattern z-0"></div>

    <div class="container position-relative z-2">
        <div class="row">
            <div class="col-lg-8">
                
                <!-- BREADCRUMBS -->
                <nav aria-label="breadcrumb" class="mb-3 animate-slide-up delay-1">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="/" class="text-decoration-none"><i class="bi bi-house-door-fill"></i> INICIO</a></li>
                        <?php foreach ($breadcrumbs as $b): ?>
                            <li class="breadcrumb-item <?php echo ($b['id'] == $categoriaActual['id']) ? 'active' : ''; ?>">
                                <?php if ($b['id'] != $categoriaActual['id']): ?>
                                    <a href="categoria.php?slug=<?= $b['slug'] ?>" class="text-decoration-none"><?= $b['nombre'] ?></a>
                                <?php else: ?>
                                    <?= $b['nombre'] ?>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                </nav>

                <!-- TÍTULO IMPACTANTE -->
                <h1 class="cat-title animate-slide-up delay-2">
                    <?= $titulo ?>
                </h1>

            </div>
            
            <!-- DECORACIÓN ANIMADA DERECHA -->
            <div class="col-lg-4 d-none d-lg-block h-100">
                <div class="hero-anim-wrapper animate-slide-up delay-2">
                    <!-- 1. Engranaje Giratorio de Fondo -->
                    <i class="bi bi-gear-wide-connected icon-gear-bg"></i>
                    
                    <!-- 2. Elemento Flotante (Rayo de Energía o Herramienta) -->
                    <i class="bi bi-lightning-charge-fill icon-float-front"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container py-5" style="min-height: 60vh;">
    
    <?php if($modo === 'error' || (empty($data) && $modo !== 'parent')): ?>
        <!-- ESTADO VACÍO ELEGANTE -->
        <div class="text-center py-5">
            <div class="mb-4">
                <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 120px; height: 120px;">
                    <i class="bi bi-search display-3 text-secondary opacity-25"></i>
                </div>
            </div>
            <h3 class="fw-bold text-dark">Sin resultados</h3>
            <p class="text-muted mb-4">No encontramos productos en esta sección.</p>
            <a href="/" class="btn btn-dark rounded-pill px-5 py-2 fw-bold shadow-lg hover-lift">
                Regresar al Inicio
            </a>
        </div>
    
    <?php elseif($modo === 'parent'): ?>
        <!-- MODO SECCIONES (PADRE) -->
        <div class="d-flex flex-column gap-5">
            <?php foreach($data as $seccion): ?>
                <div class="category-section">
                    
                    <!-- HEADER DE SECCIÓN -->
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-4">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary rounded-1 me-3" style="width: 5px; height: 25px;"></div>
                            <h3 class="fw-black text-dark m-0 text-uppercase h4">
                                <?= $seccion['info']['nombre'] ?>
                            </h3>
                        </div>
                        <a href="categoria.php?slug=<?= $seccion['info']['slug'] ?>" class="btn btn-link text-dark text-decoration-none fw-bold small">
                            VER TODO <i class="bi bi-arrow-right-circle-fill ms-1 text-primary"></i>
                        </a>
                    </div>

                    <?php 
                    $count = count($seccion['productos']);
                    if ($count > 4): 
                    ?>
                    
                        <!-- CARRUSEL (Más de 4 productos) -->
                        <div class="prod-slider-container">
                             <?php foreach($seccion['productos'] as $prod): ?>
                                <?= renderProductCard($prod, true) ?>
                            <?php endforeach; ?>
                        </div>
                    

                    <?php else: ?>
                        <!-- GRILLA (4 o menos) -->
                        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3">
                            <?php foreach($seccion['productos'] as $prod): ?>
                                <?= renderProductCard($prod, false) ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                </div>
            <?php endforeach; ?>
        </div>

    <?php else: ?>
        <!-- MODO HOJA (GRILLA COMPLETA) -->
        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3" id="productsGrid">
            <?php foreach($data as $prod): ?>
                <?= renderProductCard($prod, false) ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<script>
// 1. INICIALIZAR CARRUSEL
$(document).ready(function(){
    function logCategorySlider(message, extra) {
        console.log('[category-slider]', message, extra || {});
    }

    function applyCategoryWidths($slider, slick) {
        if (!$slider.length || !slick || typeof slick.slideWidth === 'undefined') return;
        slick.$slides.each(function() {
            this.style.setProperty('width', slick.slideWidth + 'px', 'important');
        });
    }

    $('.prod-slider-container').each(function(index){
        const $slider = $(this);
        const itemCount = $slider.children().length;

        $slider.on('init', function(event, slick) {
            applyCategoryWidths($slider, slick);
            logCategorySlider('init OK', {
                sliderIndex: index,
                slideCount: slick.slideCount,
                slidesToShow: slick.options.slidesToShow
            });
        });

        $slider.on('setPosition', function(event, slick) {
            applyCategoryWidths($slider, slick);
            logCategorySlider('setPosition', {
                sliderIndex: index,
                currentSlide: slick.currentSlide,
                slideWidth: slick.slideWidth,
                listWidth: slick.$list.width()
            });
        });

        $slider.slick({
            dots: false,
            infinite: itemCount > 1,
            speed: 450,
            slidesToShow: 4,
            slidesToScroll: 1,
            autoplay: itemCount > 1,
            autoplaySpeed: 3200,
            centerMode: false,
            variableWidth: false,
            initialSlide: 0,
            prevArrow: '<button type="button" class="slick-prev"><i class="bi bi-chevron-left text-dark fs-5"></i></button>',
            nextArrow: '<button type="button" class="slick-next"><i class="bi bi-chevron-right text-dark fs-5"></i></button>',
            responsive: [
                { breakpoint: 1200, settings: { slidesToShow: 3 } },
                { breakpoint: 768, settings: { slidesToShow: 2 } },
                { breakpoint: 480, settings: { slidesToShow: 1 } }
            ]
        });
    });
});

// 2. EFECTO CASCADA (WATERFALL ANIMATION)
document.addEventListener('DOMContentLoaded', function() {
    const observerOptions = {
        threshold: 0.1, // Se activa cuando el 10% del elemento es visible
        rootMargin: "0px 0px -50px 0px"
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                // Añadir clase 'visible' para activar CSS
                entry.target.classList.add('visible');
                // Dejar de observar para ahorrar recursos
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    // Seleccionar todos los items y aplicar un pequeño delay escalonado inline
    const items = document.querySelectorAll('.reveal-item');
    items.forEach((item, index) => {
        // En grillas grandes, reiniciar el delay cada fila (aprox cada 4) para que no sea eterno
        const delay = (index % 4) * 100; 
        item.style.transitionDelay = delay + 'ms';
        observer.observe(item);
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
