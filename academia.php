<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

// Consultar registros
$stmt = $pdo->query("SELECT * FROM academia WHERE estado = 1 ORDER BY id DESC");
$registros = $stmt->fetchAll();
?>

<!-- BANNER HERO ACADEMIA -->
<div class="position-relative bg-dark overflow-hidden" style="height: 400px;">
    <!-- Fondo con imagen oscura -->
    <div class="position-absolute top-0 start-0 w-100 h-100" 
         style="background: url('/assets/img/hero-machine.png') center/cover; opacity: 0.4; filter: blur(5px);"></div>
    
    <div class="container position-relative z-2 h-100 d-flex flex-column justify-content-center align-items-center text-center text-white">
        <span class="badge bg-primary mb-3 px-3 py-2 text-uppercase ls-2">Educación Profesional</span>
        <h1 class="display-3 fw-black text-uppercase">La Academia <span style="color:var(--primary)">MaquimPower</span></h1>
        <p class="lead" style="max-width: 700px;">
            Refuerza tus conocimientos y brinda servicios profesionales certificados.
        </p>
        <div class="mt-4 d-flex gap-2">
            <button class="btn btn-primary fw-bold rounded-pill px-4">Ver Certificados</button>
            <button class="btn btn-outline-light fw-bold rounded-pill px-4">Próximos Cursos</button>
        </div>
    </div>
</div>

<!-- FILTROS (Estilo Píldoras) -->
<div class="container py-5">
    <div class="d-flex justify-content-center gap-2 mb-5 flex-wrap">
        <button class="btn btn-dark rounded-pill px-4">Todos</button>
        <button class="btn btn-light border rounded-pill px-4">Certificaciones</button>
        <button class="btn btn-light border rounded-pill px-4">Cursos Online</button>
        <button class="btn btn-light border rounded-pill px-4">Talleres</button>
    </div>

    <!-- GRILLA DE CERTIFICADOS -->
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
        <?php foreach($registros as $item): 
            $img = !empty($item['imagen_url']) ? $item['imagen_url'] : '/assets/img/no-photo.png';
        ?>
        <div class="col">
            <div class="card h-100 border-0 shadow-sm hover-up text-center p-3" style="transition:0.3s">
                <div class="mx-auto mb-3 position-relative" style="width: 150px; height: 150px;">
                    <img src="<?php echo $img; ?>" class="rounded-circle w-100 h-100 object-fit-cover border border-4 border-light shadow">
                    <div class="position-absolute bottom-0 end-0 bg-success rounded-circle p-2 border border-3 border-white shadow-sm" style="z-index: 10;">
                        <i class="bi bi-patch-check-fill text-white fs-5"></i>
                    </div>
                </div>
               <div class="card-body p-4 d-flex flex-column align-items-center">
                    <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($item['nombre']); ?></h5>
                    <p class="text-muted small mb-3"><?php echo htmlspecialchars($item['descripcion']); ?></p>
                    
                    <span class="badge bg-light text-dark border mb-3"><?php echo $item['tipo']; ?></span>

                    <!-- BOTÓN PDF DINÁMICO -->
                    <?php if(!empty($item['pdf_url'])): ?>
                        <a href="academia-detalle.php?id=<?php echo $item['id']; ?>" class="btn btn-outline-primary btn-sm rounded-pill fw-bold px-4 mt-auto w-100">
                            VER PERFIL <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    <?php else: ?>
                        <button class="btn btn-sm btn-light text-muted disabled rounded-pill px-4 mt-auto">En Proceso</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<style>
    .hover-up:hover { transform: translateY(-10px); }
</style>

<?php require_once 'includes/footer.php'; ?>