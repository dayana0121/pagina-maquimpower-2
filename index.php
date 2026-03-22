<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

// --- CONSULTAS BASE DE DATOS ---
// 1. Productos Destacados (Random)
$stmt = $pdo->query("SELECT * FROM productos WHERE activo = 1 ORDER BY RAND() LIMIT 12");
$productos = $stmt->fetchAll();

// 2. Ofertas (Últimas agregadas)
$stmtOfertas = $pdo->prepare("SELECT * FROM productos WHERE etiqueta = 'OFERTA' AND activo = 1 ORDER BY id DESC LIMIT 12");
$stmtOfertas->execute();
$productosOferta = $stmtOfertas->fetchAll();

// --- HELPER RENDER CARD (Diseño Premium) ---
function renderHomeCard($p, $useReveal = false)
{
    $agotado = ($p['stock_actual'] <= 0);
    $img = !empty($p['imagen_url']) ? $p['imagen_url'] : '/assets/img/no-photo.png';
    $img = str_replace('/var/www/html', '', $img);
    $link = "/producto/" . $p['slug'];

    $tieneOferta = ($p['precio_oferta'] > 0);
    $precioShow = $tieneOferta ? $p['precio_oferta'] : $p['precio'];
    $descuento = $tieneOferta ? round((($p['precio'] - $p['precio_oferta']) / $p['precio']) * 100) : 0;


    ob_start();
?>
    <div class="px-2 h-100<?= $useReveal ? ' reveal-item' : '' ?>">
        <a href="<?= $link ?>" class="text-decoration-none product-card-link">
            <div class="prod-card h-100 position-relative bg-white border border-light rounded-4 overflow-hidden p-2">
                <div class="card-overlay"></div>

                <!-- Badges -->
                <div class="d-flex justify-content-between w-100 position-absolute top-0 start-0 p-3 z-3">
                    <?php if ($agotado): ?>
                        <span class="badge bg-dark text-white shadow-sm" style="font-size:0.6rem;">AGOTADO</span>
                    <?php elseif ($tieneOferta): ?>
                        <span class="badge gradient-danger fw-bold shadow-sm"
                            style="font-size:0.6rem">-<?= $descuento ?>%</span>
                    <?php else: ?>
                        <span class="badge gradient-primary text-white fw-bold shadow-sm" style="font-size:0.6rem">NUEVO</span>
                    <?php endif; ?>
                </div>

                <!-- Imagen -->
                <div class="img-wrap mb-3 mt-3 d-flex align-items-center justify-content-center position-relative"
                    style="height: 180px;">
                    <div class="skeleton position-absolute w-100 h-100 top-0 start-0 z-1 rounded-3"></div>
                    <img src="<?= $img ?>" alt="<?= htmlspecialchars($p['nombre']) ?>"
                        class="img-fluid position-relative z-2 fade-in-img"
                        style="max-height: 100%; opacity: 0; transition: transform 0.5s;"
                        onload="this.style.opacity=1; this.previousElementSibling.style.display='none';">
                </div>

                <!-- Info -->
                <div class="prod-info pt-3 px-1 border-top border-light position-relative z-2 bg-white">
                    <small class="text-muted text-uppercase d-block mb-1 font-monospace" style="font-size:0.6rem;">
                        SKU: <?= substr($p['sku'], 0, 10) ?>
                    </small>
                    <h6 class="p-title text-dark fw-bold mb-2 text-truncate" style="font-size: 0.9rem;">
                        <?= htmlspecialchars($p['nombre']) ?>
                    </h6>
                    <div class="d-flex justify-content-between align-items-end mt-3">
                        <div class="price-box lh-1">
                            <?php if ($tieneOferta): ?>
                                <small class="text-decoration-line-through text-muted fw-semibold" style="font-size:0.7rem">S/
                                    <?= number_format($p['precio'], 2) ?></small>
                                <div class="p-price text-danger fw-black" style="font-size: 1.1rem;">S/
                                    <?= number_format($precioShow, 2) ?>
                                </div>
                            <?php else: ?>
                                <div class="p-price text-dark fw-black" style="font-size: 1.1rem;">S/
                                    <?= number_format($precioShow, 2) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php if (!$agotado): ?>
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




<!-- NAV MÓVIL -->
<div class="d-md-none bg-white border-bottom py-2 sticky-top shadow-sm" style="top: 0; z-index: 800;">
    <div class="container">
        <div class="d-flex gap-2 overflow-auto" style="scrollbar-width: none;">
            <a href="/categoria/ofertas"
                class="btn btn-sm btn-danger rounded-pill fw-bold text-nowrap px-3 shadow-sm"><i
                    class="bi bi-lightning-fill"></i> PROMOS</a>
            <a href="/categoria/maquinarias"
                class="btn btn-sm btn-light border rounded-pill fw-bold text-nowrap px-3 text-dark">MAQUINARIAS</a>
            <a href="/categoria/detailing"
                class="btn btn-sm btn-light border rounded-pill fw-bold text-nowrap px-3 text-dark">DETAILING</a>
            <a href="/categoria/repuestos"
                class="btn btn-sm btn-light border rounded-pill fw-bold text-nowrap px-3 text-dark">REPUESTOS</a>
        </div>
    </div>
</div>

<!-- =========================================
     1. HERO SECTION (RESTAURADO)
========================================== -->
<div class="hero-slider">

    <!-- SLIDE 1 → TU HERO ORIGINAL -->
    <div class="hero-slide active">
        <div class="hero-section-dark">
            <div class="container position-relative z-2">
                <div class="position-relative row align-items-center">
                    <div class="col-lg-6 pt-5 pt-lg-0">
                        <div class="pill-orange mb-3"></div>

                        <h1 class="hero-title text-uppercase mb-4">
                            Potencia Tu <br>
                            <span class="text-stroke-orange">NEGOCIO</span>
                        </h1>

                        <p class="text-white-50 lead mb-4">
                            Equipamiento profesional para Carwash e Industria.<br>
                            Soporte técnico real y garantía de fábrica.
                        </p>

                        <div class="d-flex gap-3">
                            <a href="/categoria/maquinarias" class="btn btn-light rounded-pill px-5 py-3 fw-bold">
                                VER CATÁLOGO
                            </a>

                            <a href="https://wa.me/51902010281"
                                class="btn btn-outline-light rounded-circle d-flex align-items-center justify-content-center"
                                style="width:55px;height:55px;">
                                <i class="bi bi-whatsapp fs-4"></i>
                            </a>
                        </div>
                    </div>

                    <div class="col-lg-6 d-flex justify-content-center justify-content-lg-end">
                        <img src="<?= $baseUrl ?>/assets/img/logo_mp/Logo_2.png" class="hero-logo-big">
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- SLIDE 2: BANNER 1 - HIDROLAVADORAS -->
    <div class="hero-slide hero-banner-slide">
        <img src="<?= $baseUrl ?>/assets/img/web/banner1.jpg" class="hero-banner desktop" alt="Banner 1 - Hidrolavadoras">
        <img src="<?= $baseUrl ?>/assets/img/mobile/banner1.jpg" class="hero-banner mobile" alt="Banner 1 - Hidrolavadoras Móvil">
    </div>

    <!-- SLIDE 3: BANNER 2 - ASPIRADORA -->
    <div class="hero-slide hero-banner-slide">
        <img src="<?= $baseUrl ?>/assets/img/web/banner2.jpg" class="hero-banner desktop" alt="Banner 2 - Aspiradora">
        <img src="<?= $baseUrl ?>/assets/img/mobile/banner2" class="hero-banner mobile" alt="Banner 2 - Aspiradora Móvil">
    </div>

    <!-- SLIDE 4: BANNER 3 - IMPLEMENTA TU CARWASH -->
    <div class="hero-slide hero-banner-slide">
        <img src="<?= $baseUrl ?>/assets/img/web/banner3.jpg" class="hero-banner desktop" alt="Banner 3 - Implementa tu carwash">
        <img src="<?= $baseUrl ?>/assets/img/mobile/banner3 class="hero-banner mobile" alt="Banner 3 - Implementa tu carwash Móvil">
    </div>

    <!-- SLIDE 5: BANNER 4 - PROYECTO DETAILING -->
    <div class="hero-slide hero-banner-slide">
        <img src="<?= $baseUrl ?>/assets/img/web/banner4.jpg" class="hero-banner desktop" alt="Banner 4 - Proyecto Detailing">
        <img src="<?= $baseUrl ?>/assets/img/mobile/banner4" class="hero-banner mobile" alt="Banner 4 - Proyecto Detailing Móvil">
    </div>

</div>


<!-- =========================================
     2. TARJETAS FLOTANTES (Superpuestas)
========================================== -->
<div class="container position-relative" style="margin-top: -60px; z-index: 10;">
    <div class="row g-4 justify-content-center">

        <!-- Card 1 -->
        <div class="col-md-4">
            <div class="service-card-float">
                <div class="icon-square bg-orange text-white" style="background-color: #FF4500;">
                    <i class="bi bi-truck"></i>
                </div>
                <div>
                    <h6 class="fw-black text-uppercase m-0">ENVÍO A TODO EL PERÚ</h6>
                    <small class="text-muted">Lima y Provincias</small>
                </div>
            </div>
        </div>

        <!-- Card 2 -->
        <div class="col-md-4">
            <div class="service-card-float">
                <div class="icon-square bg-dark text-white">
                    <i class="bi bi-shield-check"></i>
                </div>
                <div>
                    <h6 class="fw-black text-uppercase m-0">GARANTÍA REAL</h6>
                    <small class="text-muted">Servicio Técnico Propio</small>
                </div>
            </div>
        </div>

        <!-- Card 3 -->
        <div class="col-md-4">
            <div class="service-card-float">
                <div class="icon-square bg-success bg-opacity-10 text-success">
                    <i class="bi bi-whatsapp"></i>
                </div>
                <div>
                    <h6 class="fw-black text-uppercase m-0">ASESORÍA 24/7</h6>
                    <small class="text-muted">Expertos en Línea</small>
                </div>
            </div>
        </div>

    </div>
</div>


<!-- SECTION 3: DESTACADOS -->
<div class="container py-5 mt-5">
    <div class="d-flex align-items-end justify-content-between mb-4 border-bottom pb-2">
        <div>
            <span class="tendencia text-primary small text-uppercase ls-2"><i class="bi bi-star-fill me-1"></i>
                TENDENCIA</span>
            <h2 class="fw-black text-uppercase m-0 display-6">DESTACADOS</h2>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-dark rounded-circle slider-prev-1 shadow-sm border-0"><i
                    class="bi bi-chevron-left"></i></button>
            <button class="btn btn-outline-dark rounded-circle slider-next-1 shadow-sm border-0"><i
                    class="bi bi-chevron-right"></i></button>
        </div>
    </div>

    <!-- ✅ CONTENEDOR CENTRADO PARA DESTACADOS -->
    <div class="slider-destacados-container">
        <div class="slider-destacados-desktop d-none d-lg-block">
            <div class="slider-destacados-row slider-destacados-top">
                <?php foreach ($productos as $index => $p): ?>
                    <?php if ($index % 2 === 0): ?>
                        <div>
                            <?= renderHomeCard($p) ?>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <div class="slider-destacados-row slider-destacados-bottom mt-3">
                <?php foreach ($productos as $index => $p): ?>
                    <?php if ($index % 2 !== 0): ?>
                        <div>
                            <?= renderHomeCard($p) ?>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="slider-destacados-mobile d-block d-lg-none">
            <?php foreach ($productos as $p): ?>
                <div>
                    <?= renderHomeCard($p) ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>


<!-- SECTION 4: OFERTAS -->
<div class="container py-5">
    <div class="d-flex align-items-end justify-content-between mb-4 border-bottom pb-2">
        <div>
            <span class="text-danger fw-bold small text-uppercase ls-2"><i class="bi bi-lightning-fill me-1"></i> FLASH
                SALE</span>
            <h2 class="fw-black text-uppercase m-0 display-6">OFERTAS DEL MES</h2>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-dark rounded-circle slider-prev-2 shadow-sm border-0 bg-light"><i
                    class="bi bi-chevron-left"></i></button>
            <button class="btn btn-outline-dark rounded-circle slider-next-2 shadow-sm border-0 bg-light"><i
                    class="bi bi-chevron-right"></i></button>
        </div>
    </div>

    <!-- ✅ ENCUADRADO: Carrusel 2 con max-width centrado -->
    <div class="prod-slider-container slider-ofertas-container">
        <div class="slider-ofertas">
            <?php foreach ($productosOferta as $p): ?>
                <div>
                    <?= renderHomeCard($p) ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>


<!-- TIKTOK WALL (OPTIMIZADO + RESPONSIVE PERFECTO) -->
<section class="py-4 bg-light border-top">
    <div class="container">
        <!-- TÍTULO -->
        <div class="d-flex align-items-center mb-5 justify-content-center">
            <div class="bg-black text-white p-2 rounded-3 me-3 d-flex align-items-center justify-content-center shadow"
                style="width: 50px; height: 50px;">
                <i class="bi bi-tiktok fs-3"></i>
            </div>
            <div class="text-center text-md-start">
                <h6 class="text-muted m-0 small fw-bold text-uppercase ls-1">Síguenos en redes</h6>
                <h3 class="fw-black m-0 text-uppercase">MAQUIMPOWER TV</h3>
            </div>
        </div>

        <div class="tiktok-slider-container">

            <!-- BOTON IZQUIERDA -->
            <button class="tiktok-btn tiktok-prev">
                <i class="bi bi-chevron-left"></i>
            </button>

            <!-- CARRUSEL DE VIDEOS -->
            <div class="tiktok-carousel">
                <?php
                $tiktoks = [
                    ['id' => '7527428893381348664', 'title' => 'Tutorial Puzzi'],
                    ['id' => '7484263372117036294', 'title' => 'Espumadoras Pro'],
                    ['id' => '7502488436641697079', 'title' => 'Vaporizadoras'],
                    ['id' => '7502488436641697079', 'title' => 'Vaporizadoras'],
                    ['id' => '7502488436641697079', 'title' => 'Vaporizadoras'],
                ];
                foreach ($tiktoks as $vid): ?>
                    <div class="tiktok-slide"> <!-- SLIDERl -->
                        <div class="tiktok-wrapper shadow-sm border">
                            <!-- FACHADA -->
                            <div class="tiktok-facade" data-id="<?= $vid['id'] ?>" onclick="cargarVideoIframe(this)">
                                <div class="tiktok-placeholder">
                                    <i class="bi bi-tiktok brand-icon"></i>
                                    <div class="play-button">
                                        <i class="bi bi-play-fill"></i>
                                    </div>
                                    <p class="text-white mt-3 fw-bold small text-uppercase px-3 text-center text-truncate w-75">
                                        <?= $vid['title'] ?>
                                    </p>
                                </div>
                            </div>

                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- BOTON DERECHA -->
            <button class="tiktok-btn tiktok-next">
                <i class="bi bi-chevron-right"></i>
            </button>
        </div>
</section>
<?php if (isset($_GET['registro']) && $_GET['registro'] == 'exito'): ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (isset($_GET['mail']) && $_GET['mail'] == 'ok'): ?>
                // SI EL CORREO SE ENVIÓ BIEN
                Swal.fire({
                    title: '¡CUENTA CREADA!',
                    text: 'Te hemos enviado un correo de bienvenida. ¡Revisa tu bandeja de entrada!',
                    icon: 'success',
                    background: '#1a1a1a',
                    color: '#fff',
                    confirmButtonColor: '#FF4500'
                }).then(() => {
                    // Limpiamos la URL para que no vuelva a salir si recarga la página
                    window.history.replaceState(null, null, window.location.pathname);
                });
            <?php else: ?>
                // SI HUBO UN ERROR CON HOSTINGER
                Swal.fire({
                    title: '¡CUENTA CREADA!',
                    html: 'Tu cuenta está lista, pero <b>tuvimos un problema al enviarte el correo</b> de bienvenida. <br><br> (Revisa tus credenciales de Hostinger).',
                    icon: 'warning',
                    background: '#1a1a1a',
                    color: '#fff',
                    confirmButtonColor: '#FF4500'
                }).then(() => {
                    window.history.replaceState(null, null, window.location.pathname);
                });
            <?php endif; ?>
        });
    </script>
<?php endif; ?>

<!-- SCRIP DE BUCLE DE DESTACADOS -->


<!-- SCRIPT (BUCLE + AUTOPLAY) -->
<script>
    function cargarVideoIframe(elemento) {
        const videoId = elemento.getAttribute('data-id');
        const embedUrl = `https://www.tiktok.com/embed/v2/${videoId}?lang=es-ES&autoplay=1&loop=1`;

        const iframeHTML = `
        <iframe 
            src="${embedUrl}" 
            style="width: 100%; height: 100%; border: none; background: #000;" 
            allow="autoplay; encrypted-media;" 
            allowfullscreen
            loading="lazy">
        </iframe>
    `;

        elemento.style.transition = "opacity 0.4s";
        elemento.style.opacity = "0";

        setTimeout(() => {
            elemento.parentNode.innerHTML = iframeHTML;
        }, 400);
    }
</script>

<!-- MARCAS INFINITE -->
<div class="py-5 bg-white overflow-hidden border-top">
    <div class="container text-center mb-4">
        <span class="text-muted text-uppercase fw-bold ls-2 small opacity-50">Marcas que representamos</span>
    </div>
    <div class="marquee-container" style="white-space: nowrap; overflow: hidden; position: relative;">
        <div class="d-inline-block animate-marquee">
            <?php
            $brands = glob('assets/img/marcas/*.{png,jpg,webp,svg}', GLOB_BRACE);
            for ($i = 0; $i < 2; $i++):
                foreach ($brands as $img): ?>
                    <img src="<?= $baseUrl . '/' . ltrim(str_replace('\\', '/', $img), '/') ?>" class="mx-4 opacity-50 hover-opacity-100 transition-opacity"
                        style="height: 40px; width: auto; filter: grayscale(100%); transition: 0.3s;"
                        onmouseover="this.style.filter='none'" onmouseout="this.style.filter='grayscale(100%)'">
            <?php endforeach;
            endfor; ?>
        </div>
    </div>
</div>

<!-- SCRIPT DE CARGA DIFERIDA (BUCLE + AUTOPLAY) -->
<script>
    function cargarVideoReal(elemento) {
        const videoId = elemento.getAttribute('data-id');

        // Usamos el embed V2 directo en iframe para controlar parámetros
        // autoplay=1 : Inicia solo
        // loop=1     : Se repite al terminar
        // rel=0      : Intenta no mostrar videos relacionados ajenos
        const embedUrl = `https://www.tiktok.com/embed/v2/${videoId}?lang=es-ES&autoplay=1&loop=1`;

        const iframeHTML = `
        <iframe 
            src="${embedUrl}" 
            style="width: 100%; height: 100%; border: none; border-radius: 15px; background: #000;" 
            allow="autoplay; encrypted-media;" 
            allowfullscreen
            title="Video MaquimPower">
        </iframe>
    `;

        // Efecto de transición suave
        elemento.style.opacity = '0';

        setTimeout(() => {
            // Reemplazamos la fachada con el iframe
            elemento.parentNode.innerHTML = iframeHTML;
            // El iframe cargará y el video empezará gracias a los parámetros
        }, 200);
    }
</script>
<script>
    // ANIMACIÓN CASCADA
    document.addEventListener('DOMContentLoaded', function() {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.1
        });
        document.querySelectorAll('.reveal-item').forEach((item, index) => {
            item.style.transitionDelay = (index % 4) * 100 + 'ms';
            observer.observe(item);
        });
    });
</script>
<script>
    // ROTACIÓN AUTOMÁTICA DEL HERO SLIDER
    document.addEventListener('DOMContentLoaded', function() {
        const slides = document.querySelectorAll('.hero-slider .hero-slide');
        const slider = document.querySelector('.hero-slider');
        
        console.log('🎬 HERO SLIDER INICIANDO...');
        console.log('Total de slides encontrados:', slides.length);
        console.log('Contenedor slider:', slider);
        
        if (slider) {
            console.log('✅ Slider encontrado');
            console.log('Altura del slider:', window.getComputedStyle(slider).height);
        }
        
        if (slides.length > 0) {
            console.log('✅ Slides encontrados:', slides.length);
            
            // Mostrar info de cada slide
            slides.forEach((slide, i) => {
                console.log(`Slide ${i + 1}:`, slide.className, 'Active:', slide.classList.contains('active'));
            });
            
            let index = 0;
            
            // Mostrar el primer slide inmediatamente
            if (slides[0]) {
                slides[0].classList.add('active');
                console.log('✅ Slide 1 activado');
            }
            
            // Rotación cada 6 segundos
            setInterval(() => {
                // Remover active del slide actual
                slides[index].classList.remove('active');
                console.log('❌ Slide', index + 1, 'desactivado');
                
                // Pasar al siguiente slide
                index = (index + 1) % slides.length;
                
                // Agregar active al nuevo slide
                slides[index].classList.add('active');
                console.log('✅ Slide', index + 1, 'activado');
                
                // Verificar clases aplicadas
                console.log('Clase active en slide', index + 1, ':', slides[index].classList.contains('active'));
            }, 6000);
        } else {
            console.error('❌ No se encontraron slides en el DOM');
        }
    });
</script>

<?php require_once 'includes/footer.php'; ?>
