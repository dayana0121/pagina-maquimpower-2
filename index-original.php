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
function renderHomeCard($p)
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
    <div class="px-2 h-100 reveal-item">
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

<!-- =========================================
     ESTILOS ESPECÍFICOS (RESTAURADOS)
========================================== -->
<style>
    /* 1. HERO SECTION DARK (Estilo Original Restaurado) */
    .hero-section-dark {
        background-color: #0e0e0e;
        /* Negro Profundo */
        background-image: radial-gradient(#333 1px, transparent 1px);
        /* Puntos */
        background-size: 30px 30px;
        /* Separación de puntos */
        min-height: 580px;
        position: relative;
        display: flex;
        align-items: center;
        overflow: hidden;
    }

    /* Tipografía Hero */
    .hero-title {
        font-size: 3.5rem;
        line-height: 1.1;
        font-weight: 900;
        color: #fff;
    }

    .text-stroke-orange {
        color: transparent;
        -webkit-text-stroke: 2px #FF4500;
        /* Naranja MaquimPower */
        font-family: 'Arial Black', sans-serif;
        /* Grueso */
    }

    /* Logo Hero */
    .hero-logo-big {
        max-height: 320px;
        width: auto;
        filter: drop-shadow(0 10px 20px rgba(0, 0, 0, 0.5));
        transition: transform 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        /* Efecto rebote suave */

    }

    /* Pill Naranja */
    .pill-orange {
        background-color: #FF4500;
        color: #fff;
        border-radius: 50px;
        padding: 5px 20px;
        /* Más ancho */
        font-weight: 800;
        display: inline-block;
        box-shadow: 0 0 15px rgba(255, 69, 0, 0.4);
        width: 150px;
        /* Tamaño fijo como en la foto */
        height: 15px;
        /* Para simular la barra naranja de la foto */
        margin-bottom: 20px;
    }

    /* 2. TARJETAS FLOTANTES (SERVICES) */
    .service-card-float {
        background: #fff;
        border-radius: 15px;
        padding: 20px;
        box-shadow: 0 15px 40px rgba(105, 105, 105, 0.1);
        /* Sombra suave */
        display: flex;
        align-items: center;
        height: 100%;
        transition: transform 0.3s;
        border: 1px solid #f0f0f0;
    }

    .service-card-float:hover {
        transform: translateY(-5px);
    }

    .icon-square {
        width: 55px;
        height: 55px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        margin-right: 15px;
    }

    /* 3. PRODUCTOS & SLIDERS */
    .gradient-primary {
        background: linear-gradient(135deg, #FF4500 0%, #ff6b33 100%);
    }

    .gradient-danger {
        background: linear-gradient(135deg, #dc3545 0%, #ff4d5e 100%);
    }

    .prod-card {
        transition: all 0.4s;
        box-shadow: 0 4px 6px rgba(82, 80, 80, 0.02);
    }

    .prod-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.08);
        border-color: rgba(255, 69, 0, 0.3) !important;
    }

    .btn-add-mini {
        width: 35px;
        height: 35px;
        background: #464444;
        color: white;
        transition: all 0.3s;
    }

    .prod-card:hover .btn-add-mini {
        background: var(--primary);
        transform: rotate(90deg) scale(1.1);
    }

    /* Slick Arrows */
    .slick-prev,
    .slick-next {
        width: 45px;
        height: 45px;
        background: white;
        border-radius: 50%;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        z-index: 10;
        display: flex !important;
        align-items: center;
        justify-content: center;
    }

    .slick-prev:before,
    .slick-next:before {
        display: none;
    }

    .slick-prev:hover,
    .slick-next:hover {
        background: var(--primary);
        color: white;
    }

    /* Skeleton */
    @keyframes shimmer {
        0% {
            background-position: -1000px 0;
        }

        100% {
            background-position: 1000px 0;
        }
    }

    .skeleton {
        background: #f6f7f8;
        background-image: linear-gradient(to right, #f6f7f8 0%, #edeef1 20%, #f6f7f8 40%, #f6f7f8 100%);
        background-repeat: no-repeat;
        background-size: 1000px 100%;
        animation: shimmer 2s infinite linear;
    }

    /* Mobile Adjustments */
    @media (max-width: 991px) {
        .hero-title {
            font-size: 2.5rem;
            text-align: center;
        }

        .hero-section-dark {
            padding-bottom: 60px;
            text-align: center;
        }

        .hero-logo-big {
            max-height: 120px;
            margin-top: 20px;
        }

        .service-card-float {
            margin-bottom: 10px;
        }

        .col-lg-6 {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
    }

    .hero-slider {
        position: relative;
        width: 100%;
        /* Altura controlada por media queries */
        overflow: hidden;
    }

    .hero-slide {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        transform: scale(1.05);
        transition: opacity 1s ease, transform 1s ease;
        z-index: 1;
    }

    .hero-slide.active {
        opacity: 1;
        transform: scale(1);
        z-index: 2;
    }

    /* Los banners */
    .hero-banner {
        width: 100%;
        height: auto;
        object-fit: cover;
        display: block;
    }

    /* Asegura que el hero original siga bien */
    .hero-slide .hero-section-dark {
        height: 100%;
    }

    /* (Bloque duplicado eliminado) */

    /* ===== BANNERS ===== */
    .hero-banner-slide {
        position: relative;
        width: 100%;
        height: 580px;
    }

    .hero-banner {
        width: 100%;
        height: 100%;
    }

    /* PC: llena el hero */
    .hero-banner.desktop {
        object-fit: cover;
    }

    /* Mobile: NO se recorta */
    .hero-banner.mobile {
        object-fit: contain;
        background: #000;
        /* relleno elegante si sobra espacio */
    }


    /* ===== RESPONSIVE SWITCH ===== */
    .hero-banner.mobile {
        display: none;
    }

    .hero-banner.desktop {
        display: block;
    }

    .hero-bg.mobile {
        display: none;
    }

    /* Global Banner Slide Style */
    .hero-banner-slide {
        position: relative;
        width: 100%;
        /* Eliminamos altura fija aquí, heredará del slider */
        height: 100%;
    }

    .hero-banner {
        width: 100%;
        height: auto;
        object-fit: cover;
        display: block;
    }

    .hero-bg {
        position: absolute;
        inset: 0;
        /* Usamos contain para asegurar que se vea TODO el texto del banner */
        /* OJO: cover recorta. Si el cliente quiere que NO se corte, contain es seguro, 
       pero puede dejar bordes.
       Mejor: Ajustamos la altura del slider para que 'cover' no recorte tanto.
       Y usamos background-position: center;
    */
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
    }

    /* Breakpoints para ajustar altura y evitar recorte lateral excesivo */
    /* Desktop Grande (Default) */
    .hero-slider {
        height: 580px;
    }

    /* Laptop Grande */
    @media (max-width: 1440px) {
        .hero-slider {
            height: 500px;
        }

        .hero-section-dark {
            min-height: 500px;
        }

        .hero-banner-slide {
            min-height: 500px;
        }

        .hero-title {
            font-size: 3rem;
        }

        /* Ajustar texto también */
    }

    /* Laptop Estándar / Tablet Landscape */
    @media (max-width: 1200px) {
        .hero-slider {
            height: 420px;
        }

        .hero-section-dark {
            min-height: 420px;
        }

        .hero-banner-slide {
            min-height: 420px;
        }

        .hero-title {
            font-size: 2.5rem;
        }
    }

    @media (max-width: 992px) {

        /* Tablet Portrait */
        .hero-slider {
            height: 380px;
        }

        .hero-section-dark {
            min-height: 380px;
        }

        .hero-banner-slide {
            min-height: 380px;
        }

        /* El slide 1 (texto) necesita ajustes de padding */
        .hero-section-dark .container {
            padding-top: 20px;
        }
    }

    @media (max-width: 768px) {

        /* Mobile - Cambio de imagen */
        .hero-banner.desktop {
            display: none;
        }

        .hero-banner.mobile {
            display: block;
        }

        .hero-slider {
            height: 500px;
        }

        /* Más alto en móvil para la imagen vertical */
        .hero-section-dark {
            min-height: 500px;
        }

        .hero-banner-slide {
            height: 100%;
            min-height: 500px;
        }

        /* Ajuste altura móvil */

        .hero-bg.desktop {
            display: none;
        }

        .hero-bg.mobile {
            display: block;
            background-size: cover;
            background-color: #000;
        }
    }
</style>

<!-- NAV MÓVIL -->
<div class="d-md-none bg-white border-bottom py-2 sticky-top shadow-sm" style="top: 0; z-index: 800;">
    <div class="container">
        <div class="d-flex gap-2 overflow-auto" style="scrollbar-width: none;">
            <a href="categoria.php?c=ofertas"
                class="btn btn-sm btn-danger rounded-pill fw-bold text-nowrap px-3 shadow-sm"><i
                    class="bi bi-lightning-fill"></i> ⚡ PROMOS</a>
            <a href="categoria.php?c=limpieza"
                class="btn btn-sm btn-light border rounded-pill fw-bold text-nowrap px-3 text-dark">Limpieza</a>
            <a href="categoria.php?c=carwash"
                class="btn btn-sm btn-light border rounded-pill fw-bold text-nowrap px-3 text-dark">Carwash</a>
            <a href="categoria.php?c=herramientas"
                class="btn btn-sm btn-light border rounded-pill fw-bold text-nowrap px-3 text-dark">Herramientas</a>
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
                <div class="row align-items-center">

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
                            <a href="/categoria.php/maquinarias" class="btn btn-light rounded-pill px-5 py-3 fw-bold">
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
                        <img src="/assets/img/logo_mp/MaquimPower_Logotipo_Agosto.png" class="hero-logo-big">
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- SLIDE 2 -->
    <div class="hero-slide hero-banner-slide">
        <img src="/assets/img/Banner 1 Hidrolavadoras.webp" class="hero-banner desktop" alt="Banner Hidrolavadoras">
        <img src="/assets/img/Banner 1 Hidrolavadoras (Mobile).webp" class="hero-banner mobile"
            alt="Banner Hidrolavadoras Móvil">
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
            <span class="text-primary fw-bold small text-uppercase ls-2"><i class="bi bi-star-fill me-1"></i>
                TENDENCIA</span>
            <h2 class="fw-black text-uppercase m-0 display-6">DESTACADOS</h2>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-dark rounded-circle slider-prev-1 shadow-sm border-0 bg-light"><i
                    class="bi bi-chevron-left"></i></button>
            <button class="btn btn-outline-dark rounded-circle slider-next-1 shadow-sm border-0 bg-light"><i
                    class="bi bi-chevron-right"></i></button>
        </div>
    </div>

    <!-- Carrusel 1 -->
    <div class="slider-destacados">
        <?php foreach ($productos as $p): ?>
            <?= renderHomeCard($p) ?>
        <?php endforeach; ?>
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

    <!-- Carrusel 2 -->
    <div class="slider-ofertas">
        <?php foreach ($productosOferta as $p): ?>
            <?= renderHomeCard($p) ?>
        <?php endforeach; ?>
    </div>
</div>


<!-- TIKTOK WALL (OPTIMIZADO + RESPONSIVE PERFECTO) -->
<section class="py-5 bg-light border-top mt-4">
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

        <!-- GRILLA DE VIDEOS -->
        <div class="row g-4 justify-content-center">
            <?php
            $tiktoks = [
                ['id' => '7527428893381348664', 'title' => 'Tutorial Puzzi'],
                ['id' => '7484263372117036294', 'title' => 'Espumadoras Pro'],
                ['id' => '7502488436641697079', 'title' => 'Vaporizadoras']
            ];
            foreach ($tiktoks as $vid): ?>
                <div class="col-12 col-md-4 d-flex justify-content-center"> <!-- Centrado en móvil -->
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
    </div>
</section>
<?php if (isset($_GET['registro']) && $_GET['registro'] == 'exito'): ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
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

<!-- ESTILOS ANTI-DEFORMACIÓN -->
<style>
    .tiktok-wrapper {
        border-radius: 15px;
        overflow: hidden;
        background-color: #000;
        position: relative;
        width: 100%;

        /* MAGIA RESPONSIVE: Mantiene formato vertical SIEMPRE */
        aspect-ratio: 9 / 16;

        /* LÍMITE MÓVIL: Evita que sea gigante en pantallas anchas */
        max-width: 350px;

        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .tiktok-wrapper:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
        border-color: #FE2C55 !important;
        /* Borde TikTok al hover */
    }

    .tiktok-facade,
    .tiktok-placeholder {
        width: 100%;
        height: 100%;
    }

    .tiktok-placeholder {
        background: linear-gradient(180deg, #111 0%, #222 100%);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .brand-icon {
        font-size: 3rem;
        color: rgba(255, 255, 255, 0.1);
        margin-bottom: 20px;
    }

    /* BOTÓN PLAY ANIMADO */
    .play-button {
        width: 65px;
        height: 65px;
        background: rgba(254, 44, 85, 0.9);
        /* Rojo TikTok */
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        padding-left: 5px;
        /* Ajuste visual del ícono play */
        box-shadow: 0 0 0 0 rgba(254, 44, 85, 0.7);
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        animation: pulse-red 2s infinite;
    }

    .tiktok-wrapper:hover .play-button {
        background: white;
        color: #FE2C55;
        transform: scale(1.15);
        animation: none;
    }

    @keyframes pulse-red {
        0% {
            box-shadow: 0 0 0 0 rgba(254, 44, 85, 0.7);
        }

        70% {
            box-shadow: 0 0 0 15px rgba(254, 44, 85, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(254, 44, 85, 0);
        }
    }
</style>

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
                    <img src="<?= $img ?>" class="mx-4 opacity-50 hover-opacity-100 transition-opacity"
                        style="height: 40px; width: auto; filter: grayscale(100%); transition: 0.3s;"
                        onmouseover="this.style.filter='none'" onmouseout="this.style.filter='grayscale(100%)'">
                <?php endforeach; endfor; ?>
        </div>
    </div>
</div>

<style>
    @keyframes marquee {
        0% {
            transform: translateX(0);
        }

        100% {
            transform: translateX(-50%);
        }
    }

    .animate-marquee {
        animation: marquee 30s linear infinite;
    }
</style>

<!-- SCRIPTS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>

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
    $(document).ready(function () {
        const commonSettings = {
            dots: false, infinite: true, speed: 500, slidesToShow: 4, slidesToScroll: 1, autoplay: true, autoplaySpeed: 4000,
            responsive: [
                { breakpoint: 1200, settings: { slidesToShow: 3 } },
                { breakpoint: 992, settings: { slidesToShow: 2 } },
                { breakpoint: 576, settings: { slidesToShow: 1, centerMode: true, centerPadding: '40px' } }
            ]
        };
        $('.slider-destacados').slick({ ...commonSettings, prevArrow: $('.slider-prev-1'), nextArrow: $('.slider-next-1') });
        $('.slider-ofertas').slick({ ...commonSettings, prevArrow: $('.slider-prev-2'), nextArrow: $('.slider-next-2') });
    });

    // ANIMACIÓN CASCADA
    document.addEventListener('DOMContentLoaded', function () {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) { entry.target.classList.add('visible'); observer.unobserve(entry.target); }
            });
        }, { threshold: 0.1 });
        document.querySelectorAll('.reveal-item').forEach((item, index) => {
            item.style.transitionDelay = (index % 4) * 100 + 'ms';
            observer.observe(item);
        });
    });
</script>
<script>
    const slides = document.querySelectorAll('.hero-slide');
    let index = 0;

    setInterval(() => {
        slides[index].classList.remove('active');
        index = (index + 1) % slides.length;
        slides[index].classList.add('active');
    }, 6000);
</script>


<?php require_once 'includes/footer.php'; ?>