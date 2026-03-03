<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

// Consultar noticias (Más reciente primero)
$stmt = $pdo->query("SELECT * FROM blog ORDER BY fecha DESC");
$posts = $stmt->fetchAll();

// Separar la primera noticia (Destacada) del resto
$destacado = !empty($posts) ? array_shift($posts) : null;
?>

<!-- CABECERA -->
<div class="bg-light py-5 text-center border-bottom">
    <h6 class="text-primary fw-bold text-uppercase ls-2 mb-2">NOTICIAS & NOVEDADES</h6>
    <h1 class="fw-black display-4">BLOG INDUSTRIAL</h1>
</div>

<div class="container py-5">
    
    <?php if ($destacado): 
        $imgDest = !empty($destacado['imagen_url']) ? $destacado['imagen_url'] : 'https://via.placeholder.com/1200x600';
    ?>
    <!-- 1. ARTÍCULO DESTACADO (HERO) -->
    <a href="blog-detalle.php?slug=<?php echo $destacado['slug']; ?>" class="text-decoration-none text-dark">
        <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-5 blog-hero">
            <div class="row g-0">
                <div class="col-lg-7 position-relative overflow-hidden" style="min-height: 400px;">
                    <img src="<?php echo $imgDest; ?>" class="w-100 h-100 object-fit-cover transition-zoom">
                </div>
                <div class="col-lg-5 d-flex align-items-center bg-dark text-white">
                    <div class="card-body p-5">
                        <span class="badge bg-primary mb-3">NUEVO</span>
                        <small class="d-block text-white-50 mb-2"><?php echo date('d M, Y', strtotime($destacado['fecha'])); ?></small>
                        <h2 class="card-title fw-black mb-3 display-6"><?php echo htmlspecialchars($destacado['titulo']); ?></h2>
                        <p class="card-text text-white-50 mb-4">
                            <?php echo substr(strip_tags($destacado['contenido']), 0, 150); ?>...
                        </p>
                        <span class="btn btn-outline-light rounded-pill px-4 fw-bold">LEER ARTÍCULO COMPLETO</span>
                    </div>
                </div>
            </div>
        </div>
    </a>
    <?php endif; ?>

    <!-- 2. GRILLA DE ARTÍCULOS SECUNDARIOS -->
    <div class="row g-4">
        <?php foreach($posts as $post): 
             $img = !empty($post['imagen_url']) ? $post['imagen_url'] : 'https://via.placeholder.com/800x400';
        ?>
        <div class="col-md-6 col-lg-4">
            <a href="blog-detalle.php?slug=<?php echo $post['slug']; ?>" class="text-decoration-none text-dark">
                <article class="card h-100 border-0 shadow-sm overflow-hidden blog-card">
                    <div class="position-relative overflow-hidden" style="height: 220px;">
                        <img src="<?php echo $img; ?>" class="w-100 h-100 object-fit-cover transition-zoom">
                        <div class="position-absolute top-0 end-0 m-3 bg-white px-2 py-1 rounded fw-bold small shadow-sm">
                            <?php echo date('d M', strtotime($post['fecha'])); ?>
                        </div>
                    </div>
                    <div class="card-body p-4 d-flex flex-column">
                        <small class="text-primary fw-bold text-uppercase mb-2" style="font-size:0.7rem;">NOTICIA</small>
                        <h5 class="fw-bold mb-3 lh-sm"><?php echo htmlspecialchars($post['titulo']); ?></h5>
                        <p class="text-muted small flex-grow-1 mb-4">
                            <?php echo substr(strip_tags($post['contenido']), 0, 90); ?>...
                        </p>
                        <div class="d-flex align-items-center text-muted small border-top pt-3 mt-auto">
                            <i class="bi bi-person-circle me-2"></i> <?php echo htmlspecialchars($post['autor']); ?>
                        </div>
                    </div>
                </article>
            </a>
        </div>
        <?php endforeach; ?>
    </div>

    <?php if(!$destacado && empty($posts)): ?>
        <div class="text-center py-5">
            <h3 class="text-muted">Aún no hay publicaciones.</h3>
        </div>
    <?php endif; ?>
</div>

<style>
    /* Efecto Zoom Suave */
    .transition-zoom { transition: transform 0.5s ease; }
    .blog-hero:hover .transition-zoom, 
    .blog-card:hover .transition-zoom { transform: scale(1.05); }
    
    /* Elevación */
    .blog-card { transition: transform 0.3s, box-shadow 0.3s; }
    .blog-card:hover { transform: translateY(-5px); box-shadow: 0 10px 30px rgba(0,0,0,0.1) !important; }
</style>

<?php require_once 'includes/footer.php'; ?>