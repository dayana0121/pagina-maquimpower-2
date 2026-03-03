<?php
require_once 'includes/db.php';

// 1. FUNCIONES DE LIMPIEZA
function normalizarParaTest($txt)
{
    $txt = strtolower($txt);
    $txt = strtr(utf8_decode($txt), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿ'), 'aaaaaceeeeiiiinooooouuuuyy');
    $txt = preg_replace('/[^a-z0-9]/', '', $txt);
    return $txt;
}

// 2. CAPTURA DEL SLUG
// Limpiamos la URL de posibles extensiones como .html o barras extra que mete Google
$slugOriginal = isset($_GET['slug']) ? trim($_GET['slug'], " /") : '';
$slugOriginal = str_replace('.html', '', $slugOriginal);

// --- FLUJO DE RESCATE AGRESIVO ---

// NIVEL A: INTENTO EXACTO
$stmt = $pdo->prepare("SELECT * FROM productos WHERE slug = :slug AND activo = 1 LIMIT 1");
$stmt->execute([':slug' => $slugOriginal]);
$p = $stmt->fetch();

// NIVEL B: SIMILITUD (Si el slug cambió un poco)
if (!$p && !empty($slugOriginal)) {
    $slugTest = normalizarParaTest($slugOriginal);
    $stmt2 = $pdo->query("SELECT slug, nombre FROM productos WHERE activo = 1");
    $mejorCoincidencia = null;
    $maxPorcentaje = 0;

    foreach ($stmt2 as $row) {
        // Comparamos contra el slug y contra el nombre (doble oportunidad)
        similar_text($slugTest, normalizarParaTest($row['slug']), $p_slug);
        similar_text($slugTest, normalizarParaTest($row['nombre']), $p_nom);

        $percent = max($p_slug, $p_nom);

        if ($percent > $maxPorcentaje) {
            $maxPorcentaje = $percent;
            $mejorCoincidencia = $row['slug'];
        }
    }

    // Bajamos el umbral a 60% para ser más "atrapalotodo"
    if ($maxPorcentaje > 60 && $mejorCoincidencia) {
        header("HTTP/1.1 301 Moved Permanently");
        header("Location: /producto/" . $mejorCoincidencia);
        exit;
    }
}

// NIVEL C: BÚSQUEDA POR PALABRAS SUELTAS (Para "Fregadora Karcher BDS")
if (!$p && !empty($slugOriginal)) {
    $busquedaLimpia = str_replace('-', ' ', $slugOriginal);
    // Buscamos si el nombre del producto contiene las palabras de la URL
    $stmtKey = $pdo->prepare("SELECT * FROM productos WHERE nombre LIKE ? AND activo = 1 LIMIT 1");
    $stmtKey->execute(["%$busquedaLimpia%"]);
    $p = $stmtKey->fetch();

    if ($p) {
        header("HTTP/1.1 301 Moved Permanently");
        header("Location: /producto/" . $p['slug']);
        exit;
    }
}

// NIVEL D: RED DE SEGURIDAD TOTAL (Si nada funcionó, NO MANDAR AL INDEX)
if (!$p) {
    // Mandamos a la página de categorías pero con el término de búsqueda ya puesto
    header("Location: /categoria.php?search=" . urlencode(str_replace('-', ' ', $slugOriginal)));
    exit;
}

// --- SI LLEGAMOS AQUÍ, ES PORQUE $p EXISTE ---

$agotado = ($p['stock_actual'] <= 0);
$img_raw = !empty($p['imagen_url']) ? $p['imagen_url'] : '/assets/img/no-photo.png';
$imgPrincipal = str_replace('/var/www/html', '', $img_raw);

// IMPORTANTE: Primero definimos todo, LUEGO el header
$galeriaDecode = json_decode($p['galeria'] ?? '[]', true);
$galeria = [];
if (empty($galeriaDecode)) {
    $galeria[] = $imgPrincipal;
} else {
    foreach ($galeriaDecode as $img) {
        $galeria[] = str_replace('/var/www/html', '', $img);
    }
    array_unshift($galeria, $imgPrincipal);
    $galeria = array_values(array_unique($galeria));
}

$videoUrl = $p['video_url'] ?? '';
$esTikTok = (!empty($videoUrl) && strpos($videoUrl, 'tiktok') !== false);

function renderVideo($url, $esTikTok)
{
    if ($esTikTok) {
        return '<div class="d-flex justify-content-center w-100">' . $url . '</div>';
    } else {
        return '<div class="ratio ratio-16x9 rounded-4 shadow-sm overflow-hidden">' . $url . '</div>';
    }
}

// Datos WhatsApp
$wspNumero = "51902010281";
$wspMensaje = "Hola *Maquimpower*! 👋%0AEstoy interesado en este equipo:%0A%0A🔹 *" . urlencode($p['nombre']) . "*%0A🔸 SKU: " . $p['sku'] . "%0A%0A¿Me brindan precio y detalles?";
$wspUrl = "https://wa.me/$wspNumero?text=$wspMensaje";

require_once 'includes/header.php';
?>

<!-- AQUÍ COMIENZA TU HTML (<style>, <div class="detail-bg">...) -->

<style>
    :root {
        --primary: #FF4500;
        --dark: #111;
    }

    /* --- LAYOUT GENERAL --- */
    .detail-bg {
        background-color: #f8f9fa;
        min-height: 100vh;
        padding-top: 30px;
        padding-bottom: 80px;
    }

    /* --- TARJETAS Y GALERÍA --- */
    .prod-gallery-card {
        background: white;
        border-radius: 16px;
        padding: 20px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.03);
        border: 1px solid #eee;
    }

    .thumb-img {
        width: 70px;
        height: 70px;
        object-fit: contain;
        border: 2px solid #fff;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.2s;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    }

    .thumb-img:hover,
    .thumb-img.active {
        border-color: var(--primary);
        transform: translateY(-2px);
    }

    .price-tag-large {
        font-size: 2.2rem;
        font-weight: 900;
        letter-spacing: -1px;
        color: #111;
        line-height: 1;
    }

    .video-clean-wrapper {
        margin-top: 2rem;
        width: 100%;
    }

    blockquote.tiktok-embed {
        margin: 0 !important;
        min-width: 300px !important;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }

    /* --- ACORDEÓN --- */
    .accordion-item {
        border: none;
        border-bottom: 1px solid #eee;
        background: #fff;
    }

    .accordion-button {
        font-weight: 800;
        text-transform: uppercase;
        font-size: 0.85rem;
        background-color: #fff !important;
        color: #333 !important;
        box-shadow: none !important;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .accordion-button:not(.collapsed) {
        color: var(--primary) !important;
    }

    .accordion-button::after {
        display: none;
    }

    .custom-chevron {
        transition: transform 0.3s ease;
        font-size: 1.1rem;
    }

    .rotated {
        transform: rotate(180deg);
    }

    /* --- ANIMACIÓN ACORDEÓN SUAVE --- */
    .accordion-collapse {
        transition: height 0.35s ease-out;
        /* Forzar suavidad */
    }

    /* Rotación de la flecha */
    .custom-chevron {
        transition: transform 0.3s ease-in-out;
    }

    .rotated {
        transform: rotate(180deg);
    }

    /* Evita que el botón cambie de tamaño al cambiar de icono */
    .btn i {
        display: inline-block;
        vertical-align: middle;
    }

    /* --- BOTONES --- */
    .btn-pay-glow {
        background: var(--dark);
        color: #fff;
        border: 1px solid transparent;
        font-weight: 800;
        letter-spacing: 1px;
        transition: 0.3s;
        text-transform: uppercase;
        padding: 12px;
    }

    .btn-pay-glow:hover:not(.disabled) {
        background: var(--primary);
        color: #fff;
        box-shadow: 0 5px 20px rgba(255, 69, 0, 0.4);
        transform: translateY(-2px);
    }

    .btn-whatsapp-pro {
        background: linear-gradient(45deg, #25D366, #128C7E);
        color: white !important;
        border: none;
        border-radius: 50px;
        padding: 12px 20px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .btn-whatsapp-pro:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(37, 211, 102, 0.4);
        filter: brightness(1.1);
    }

    .btn-whatsapp-pro:active {
        transform: scale(0.98);
    }

    .btn-share-icon {
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 20px;
    }

    /* --- ALERTAS PERSONALIZADAS UNIFICADAS (MP) --- */
    div:where(.swal2-container).mp-toast-container .swal2-popup {
        background: #000 !important;
        border: 1px solid #333 !important;
        border-left: 4px solid var(--primary) !important;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.8) !important;
        border-radius: 8px !important;
        padding: 0.8rem 1rem !important;
        display: flex !important;
        align-items: center !important;
        width: auto !important;
        min-width: 250px;
    }

    div:where(.swal2-container).mp-toast-container .swal2-title {
        color: #fff !important;
        font-size: 0.85rem !important;
        font-weight: 700 !important;
        margin: 0 !important;
        padding: 0 10px !important;
        text-align: left !important;
        text-transform: uppercase;
    }

    div:where(.swal2-container).mp-toast-container .swal2-timer-progress-bar {
        background: var(--primary) !important;
        height: 3px !important;
    }

    div:where(.swal2-container).mp-toast-container .swal2-icon {
        transform: scale(0.6) !important;
        margin: 0 !important;
        border-color: var(--primary) !important;
    }

    div:where(.swal2-container).mp-toast-container .swal2-icon.swal2-success [class^=swal2-success-line] {
        background-color: var(--primary) !important;
    }

    /* BOTÓN PDF TÉCNICO */
    .btn-pdf-download {
        border: 2px solid #e0e0e0;
        color: #555;
        background: #fff;
        font-weight: 800;
        border-radius: 50px;
        padding: 10px;
        text-transform: uppercase;
        font-size: 0.85rem;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .btn-pdf-download:hover {
        border-color: var(--primary);
        /* Naranja */
        color: var(--primary);
        background: #fff;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    }

    .btn-pdf-download i {
        color: #dc3545;
        /* Rojo clásico de PDF */
        font-size: 1.1rem;
        transition: transform 0.3s;
    }

    .btn-pdf-download:hover i {
        transform: scale(1.1);
    }

    /* ANIMACIÓN DE ENTRADA SUAVE */
    .animate-up {
        animation: fadeInUp 0.8s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
        opacity: 0;
        transform: translateY(30px);
    }

    .delay-1 {
        animation-delay: 0.1s;
    }

    .delay-2 {
        animation-delay: 0.2s;
    }

    @keyframes fadeInUp {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* FLECHAS GALERÍA */
    .gallery-nav {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 40px;
        height: 40px;
        border-radius: 50%;
        border: none;
        background: rgba(255, 255, 255, 0.8);
        color: #333;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        z-index: 5;
        transition: all 0.3s;
    }

    .gallery-nav:hover {
        background: var(--primary);
        color: white;
        transform: translateY(-50%) scale(1.1);
    }

    .gallery-nav.prev {
        left: 15px;
    }

    .gallery-nav.next {
        right: 15px;
    }
</style>

<div class="detail-bg">
    <div class="container">
        <!-- BREADCRUMB -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb small text-uppercase fw-bold m-0">
                <li class="breadcrumb-item"><a href="/" class="text-muted text-decoration-none">Inicio</a></li>
                <li class="breadcrumb-item"><a href="/categoria.php?c=todo"
                        class="text-muted text-decoration-none">Catálogo</a></li>
                <li class="breadcrumb-item active text-primary" aria-current="page">Detalle</li>
            </ol>
        </nav>

        <div class="row g-4 g-lg-5">

            <!-- COLUMNA IZQUIERDA -->
            <div class="col-lg-7 animate-up">

                <!-- 1. IMAGEN PRINCIPAL -->
                <div class="prod-gallery-card mb-3 position-relative shadow-lg border-0">
                    <!-- ETIQUETAS -->
                    <?php if ($agotado): ?>
                        <span class="badge bg-secondary position-absolute top-0 start-0 m-3 px-3 py-2 z-2">AGOTADO</span>
                    <?php elseif ($p['precio_oferta'] > 0): ?>
                        <span
                            class="badge bg-danger position-absolute top-0 start-0 m-3 px-3 py-2 z-2 shadow-sm">OFERTA</span>
                    <?php endif; ?>

                    <!-- FLECHAS DE NAVEGACIÓN (NUEVO) -->
                    <?php if (count($galeria) > 1): ?>
                        <button class="gallery-nav prev" onclick="moveGallery(-1)"><i
                                class="bi bi-chevron-left"></i></button>
                        <button class="gallery-nav next" onclick="moveGallery(1)"><i
                                class="bi bi-chevron-right"></i></button>
                    <?php endif; ?>

                    <!-- IMAGEN PRINCIPAL -->
                    <div class="d-flex align-items-center justify-content-center bg-white rounded-4 p-4"
                        style="height: 450px;">
                        <img src="<?php echo $imgPrincipal; ?>" id="mainImage" class="img-fluid"
                            style="max-height: 100%; max-width: 100%; object-fit: contain; transition: transform 0.3s;"
                            alt="<?php echo htmlspecialchars($p['imagen_alt'] ?? $p['nombre']); ?>" data-index="0">
                        <!-- Guardamos el índice actual -->
                    </div>
                </div>
                <!-- 2. MINIATURAS -->
                <?php if (count($galeria) > 1): ?>
                    <div class="d-flex gap-2 overflow-auto pb-2 justify-content-center">
                        <?php foreach ($galeria as $index => $foto): ?>
                            <img src="<?php echo $foto; ?>" class="thumb-img <?php echo $index === 0 ? 'active' : ''; ?>"
                                onclick="cambiarImagen(this, '<?php echo $foto; ?>')">
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- 3. VIDEO (SOLO VISIBLE EN PC: d-none d-lg-block) -->
                <?php if (!empty($p['video_url'])): ?>
                    <div class="video-clean-wrapper d-none d-lg-block">
                        <div class="text-uppercase fw-bold small text-muted mb-2"><i
                                class="bi bi-play-circle-fill text-danger me-1"></i> Video Demostrativo</div>
                        <?php echo renderVideo($p['video_url'], $esTikTok); ?>
                    </div>
                <?php endif; ?>

            </div>

            <!-- COLUMNA DERECHA -->
            <div class="col-lg-5 animate-up delay-1">
                <div class="ps-lg-3">

                    <!-- INFO HEADER -->
                    <h6 class="text-primary fw-bold text-uppercase ls-1 mb-2 small">
                        <i class="bi bi-tag-fill me-1"></i> <?php echo $p['categoria']; ?>
                    </h6>
                    <h1 class="fw-black text-dark mb-3 text-uppercase lh-sm display-6">
                        <?php echo htmlspecialchars($p['nombre']); ?>
                    </h1>

                    <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">
                        <small class="text-muted fw-bold font-monospace">SKU: <?php echo $p['sku']; ?></small>
                        <!-- BOTÓN COMPARTIR -->
                        <button class="btn btn-light btn-sm border rounded-circle shadow-sm" onclick="copiarLink(this)"
                            title="Copiar Link">
                            <i class="bi bi-share-fill"></i>
                        </button>
                    </div>

                    <!-- PRECIO -->
                    <div class="mb-4 bg-white p-3 rounded-4 shadow-sm border">
                        <?php if ($p['precio_oferta'] > 0): ?>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="text-decoration-line-through text-muted fs-6">S/
                                    <?php echo number_format($p['precio'], 2); ?></span>
                                <span
                                    class="badge bg-danger rounded-pill small">-<?php echo round((($p['precio'] - $p['precio_oferta']) / $p['precio']) * 100); ?>%</span>
                            </div>
                            <div class="price-tag-large text-danger">S/ <?php echo number_format($p['precio_oferta'], 2); ?>
                            </div>
                        <?php else: ?>
                            <div class="price-tag-large text-dark">S/ <?php echo number_format($p['precio'], 2); ?></div>
                        <?php endif; ?>
                        <small class="text-muted d-block mt-1"><i class="bi bi-check-circle-fill text-success"></i>
                            Precio incluye IGV + Garantía</small>
                    </div>

                    <!-- BOTONES DE COMPRA -->
                    <div class="card bg-white border-0 shadow-sm p-3 rounded-4 mb-4">
                        <div class="row g-2 mb-3">
                            <div class="col-4">
                                <input type="number" id="cantidad"
                                    class="form-control form-control-lg text-center fw-bold bg-light border-0 h-100"
                                    value="1" min="1" max="<?php echo $p['stock_actual']; ?>">
                            </div>
                            <div class="col-8">
                                <button
                                    class="btn btn-pay-glow w-100 h-100 rounded-3 <?php echo $agotado ? 'disabled' : ''; ?>"
                                    onclick='prepararCompra(
                                            <?php echo $p['id']; ?>, 
                                            <?php echo htmlspecialchars(json_encode($p['sku']), ENT_QUOTES, 'UTF-8'); ?>, 
                                            <?php echo htmlspecialchars(json_encode($p['nombre']), ENT_QUOTES, 'UTF-8'); ?>, 
                                            <?php echo $p['precio_oferta'] > 0 ? $p['precio_oferta'] : $p['precio']; ?>, 
                                            <?php echo htmlspecialchars(json_encode($imgPrincipal), ENT_QUOTES, 'UTF-8'); ?>, 
                                            <?php echo $p['stock_actual']; ?>
                                        )'>
                                    <?php echo $agotado ? 'AGOTADO' : 'AÑADIR AL CARRITO'; ?> <i
                                        class="bi bi-bag-plus-fill ms-2"></i>
                                </button>
                            </div>
                        </div>
                        <a href="<?php echo $wspUrl; ?>" target="_blank" rel="noopener noreferrer"
                            class="btn btn-whatsapp-pro w-100 shadow-sm">
                            <i class="bi bi-whatsapp fs-5 me-2"></i> Consultar por WhatsApp
                        </a>
                        <!-- BOTÓN PDF FICHA TÉCNICA -->
                        <?php if (!empty($p['pdf_url'])): ?>
                            <a href="<?php echo $p['pdf_url']; ?>" target="_blank"
                                class="btn btn-pdf-download w-100 mt-3 shadow-sm">
                                <i class="bi bi-file-earmark-pdf-fill me-2"></i> VER FICHA TÉCNICA
                            </a>
                        <?php endif; ?>
                    </div>
                    <!-- 4. VIDEO (SOLO VISIBLE EN MÓVIL: d-lg-none) -->
                    <?php if (!empty($p['video_url'])): ?>
                        <div class="video-clean-wrapper d-lg-none mb-4">
                            <div class="text-uppercase fw-bold small text-muted mb-2"><i
                                    class="bi bi-play-circle-fill text-danger me-1"></i> Video Demostrativo</div>
                            <?php echo renderVideo($p['video_url'], $esTikTok); ?>
                        </div>
                    <?php endif; ?>

                    <!-- ACORDEÓN -->
                    <div class="accordion shadow-sm rounded-4 overflow-hidden border" id="accordionProduct">

                        <!-- ITEM 1: DESCRIPCIÓN -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingOne">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapseDesc">
                                    <span><i class="bi bi-file-text me-2 text-primary"></i> DESCRIPCIÓN</span>
                                    <i class="bi bi-chevron-down custom-chevron rotated"></i>
                                </button>
                            </h2>
                            <div id="collapseDesc" class="accordion-collapse collapse show"
                                data-bs-parent="#accordionProduct">
                                <div class="accordion-body text-secondary small" style="white-space: pre-line;">
                                    <?php
                                    $cleanDesc = explode("Especificaciones:", $p['descripcion'])[0];
                                    echo strip_tags($cleanDesc);
                                    ?>
                                </div>
                            </div>
                        </div>

                        <!-- ITEM 2: FICHA TÉCNICA -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingTwo">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapseFicha">
                                    <span><i class="bi bi-tools me-2 text-primary"></i> FICHA TÉCNICA</span>
                                    <i class="bi bi-chevron-down custom-chevron"></i>
                                </button>
                            </h2>
                            <div id="collapseFicha" class="accordion-collapse collapse"
                                data-bs-parent="#accordionProduct">
                                <div class="accordion-body p-0">
                                    <table class="table table-striped table-hover m-0" style="font-size: 0.85rem;">
                                        <tbody>
                                            <?php
                                            $lineas = explode("\n", strip_tags($p['descripcion']));
                                            foreach ($lineas as $linea) {
                                                if (strpos($linea, ':') !== false && strlen($linea) < 150) {
                                                    $partes = explode(':', $linea, 2);
                                                    echo "<tr>
                                                            <th class='ps-4 text-muted fw-bold' style='width:40%'>" . trim($partes[0]) . "</th>
                                                            <td class='fw-bold text-dark'>" . trim($partes[1]) . "</td>
                                                          </tr>";
                                                }
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- ITEM 3: GARANTÍA -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingThree">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapseWarranty">
                                    <span><i class="bi bi-shield-check me-2 text-primary"></i> GARANTÍA</span>
                                    <i class="bi bi-chevron-down custom-chevron"></i>
                                </button>
                            </h2>
                            <div id="collapseWarranty" class="accordion-collapse collapse"
                                data-bs-parent="#accordionProduct">
                                <div class="accordion-body small text-muted">
                                    <p class="mb-1"><strong><i class="bi bi-check-circle text-success"></i> Garantía
                                            Maquimpower:</strong> 1 Año por defectos de fábrica.</p>
                                    <p class="mb-0"><strong><i class="bi bi-tools text-dark"></i> Soporte Técnico
                                            Profesional:</strong> Infraestructura técnica propia en Lima para
                                        mantenimiento correctivo y preventivo de equipos profesionales.</p>
                                </div>
                            </div>
                        </div>

                    </div>
                    <!-- FIN ACORDEÓN -->

                </div>
            </div> <!-- FIN COLUMNA DERECHA -->

        </div>
    </div>
</div>

<!-- SCRIPTS -->
<!-- LIBRERÍA ALERTAS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // ==========================================
    // 1. CONFIGURACIÓN Y VARIABLES GLOBALES
    // ==========================================
    const MiniAlerta = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 2500,
        timerProgressBar: true,
        background: '#000',
        color: '#fff',
        customClass: {
            container: 'mp-toast-container',
            popup: 'border-left-orange'
        },
        didOpen: (toast) => {
            const icon = toast.querySelector('.swal2-icon');
            if (icon) icon.style.display = 'none';
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
    });

    const productImages = <?php echo json_encode($galeria ?? []); ?>;
    let currentIndex = 0;

    // ==========================================
    // 2. LÓGICA CORE DEL CARRITO
    // ==========================================
    function addToCart(id, sku, nombre, precio, imagen, cantidad, stockMax) {
        cantidad = parseInt(cantidad);
        stockMax = parseInt(stockMax);

        let cart = JSON.parse(localStorage.getItem('maquim_cart')) || [];
        let existingIndex = cart.findIndex(item => item.id == id);

        if (existingIndex !== -1) {
            let nuevaCant = cart[existingIndex].cantidad + cantidad;
            if (nuevaCant > stockMax) {
                cart[existingIndex].cantidad = stockMax;
                MiniAlerta.fire({ title: 'STOCK MÁXIMO ALCANZADO', icon: 'warning' });
            } else {
                cart[existingIndex].cantidad = nuevaCant;
            }
            cart[existingIndex].maxStock = stockMax;
        } else {
            cart.push({
                id: id, sku: sku, nombre: nombre,
                precio: parseFloat(precio), img: imagen,
                cantidad: cantidad, maxStock: stockMax
            });
        }

        localStorage.setItem('maquim_cart', JSON.stringify(cart));
        updateCartBadge();
    }

    function updateCartBadge() {
        let cart = JSON.parse(localStorage.getItem('maquim_cart')) || [];
        let count = cart.reduce((sum, item) => sum + item.cantidad, 0);
        document.querySelectorAll('.badge.bg-danger').forEach(el => el.innerText = count);
    }

    // Declaramos la función original explícitamente en window para poder interceptarla luego
    window.prepararCompra = function (id, sku, nombre, precio, imagen, stockMax) {
        const cantInput = document.getElementById('cantidad');

        if (!cantInput) {
            alert("🚨 Error: Falta el ID 'cantidad' en tu HTML.");
            return;
        }

        const cantidad = parseInt(cantInput.value);

        if (isNaN(cantidad) || cantidad < 1) {
            MiniAlerta.fire({ title: 'Ingresa una cantidad válida', icon: 'error' });
            return;
        }

        if (cantidad > stockMax) {
            MiniAlerta.fire({ title: 'Stock insuficiente (Máx: ' + stockMax + ')', icon: 'warning' });
            return;
        }

        addToCart(id, sku, nombre, precio, imagen, cantidad, stockMax);
        MiniAlerta.fire({ title: 'PRODUCTO AÑADIDO AL CARRITO', icon: 'success' });
    };

    // ==========================================
    // 3. FUNCIONES DE UI (GALERÍA Y COMPARTIR)
    // ==========================================
    function cambiarImagen(el, src) {
        document.getElementById('mainImage').src = src;
        document.querySelectorAll('.thumb-img').forEach(img => img.classList.remove('active'));
        el.classList.add('active');
        currentIndex = productImages.indexOf(src); // Sincroniza las flechas
    }

    function moveGallery(direction) {
        const imgEl = document.getElementById('mainImage');
        currentIndex += direction;

        if (currentIndex < 0) currentIndex = productImages.length - 1;
        if (currentIndex >= productImages.length) currentIndex = 0;

        imgEl.style.opacity = 0;
        setTimeout(() => {
            imgEl.src = productImages[currentIndex];
            imgEl.style.opacity = 1;
            document.querySelectorAll('.thumb-img').forEach((t, i) => {
                if (i === currentIndex) {
                    t.classList.add('active');
                    t.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
                } else {
                    t.classList.remove('active');
                }
            });
        }, 200);
    }

    function copiarLink(btn) {
        const url = window.location.href;
        const icon = btn.querySelector('i');
        const originalClass = icon.className;

        const exito = () => {
            MiniAlerta.fire({ title: 'ENLACE COPIADO', icon: 'success' });
            icon.className = 'bi bi-check-lg text-success fw-bold';
            setTimeout(() => icon.className = originalClass, 2000);
        };

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(url).then(exito).catch(() => fallbackCopy(url, exito));
        } else {
            fallbackCopy(url, exito);
        }
    }

    function fallbackCopy(text, onSuccess) {
        let ta = document.createElement("textarea");
        ta.value = text; ta.style.position = "fixed"; ta.style.left = "-9999px";
        document.body.appendChild(ta); ta.focus(); ta.select();
        try { document.execCommand('copy'); onSuccess(); } catch (e) { }
        document.body.removeChild(ta);
    }

    // ==========================================
    // 4. BLOQUE DE DIAGNÓSTICO E INTERCEPCIÓN
    // ==========================================
    function debugActualizarMonitor() {
        const cart = JSON.parse(localStorage.getItem('maquim_cart')) || [];
        const div = document.getElementById('debug-cart-content');

        // Si el usuario borró el div de debug, no hacemos nada (evita romper JS)
        if (!div) return;

        if (cart.length === 0) {
            div.innerHTML = "<em style='color:gray'>El carrito está vacío</em>";
        } else {
            let html = "";
            cart.forEach((item, index) => {
                let tieneMax = (item.maxStock !== undefined) ?
                    `<span style='color:lime'>OK (${item.maxStock})</span>` :
                    `<span style='color:red; font-weight:bold;'>FALTA (ERROR)</span>`;

                html += `Item ${index + 1}: <b>${item.nombre.substring(0, 10)}...</b><br>`;
                html += `Cant: ${item.cantidad} | MaxStock: ${tieneMax}<br>`;
                html += `----------------<br>`;
            });
            div.innerHTML = html;
        }
    }

    function borrarCarritoDebug() {
        localStorage.removeItem('maquim_cart');
        location.reload();
    }

    // Secuestro de la función para el log
    const funcionOriginal = window.prepararCompra;
    window.prepararCompra = function (id, sku, nombre, precio, imagen, stockMax) {
        const log = document.getElementById('debug-log');

        // Si el div de debug existe, escribimos en él
        if (log) {
            log.innerHTML = "⚡ Clic detectado<br>";
            log.innerHTML += `Stock Recibido: <b style='color:white'>${stockMax}</b> (Tipo: ${typeof stockMax})<br>`;

            if (stockMax === undefined || stockMax === null || isNaN(stockMax)) {
                log.innerHTML += "<span style='color:red; background:white;'>🚨 ALERTA: El stock no está llegando bien.</span>";
            } else {
                log.innerHTML += "<span style='color:lime'>✅ Datos correctos. Intentando agregar...</span>";
            }
        }

        // Ejecutamos la función original (la que hace la compra real)
        if (typeof funcionOriginal === 'function') {
            funcionOriginal(id, sku, nombre, precio, imagen, stockMax);
        } else {
            if (log) log.innerHTML += "<br><span style='color:red'>❌ Error: No encuentro la función prepararCompra original.</span>";
        }

        setTimeout(debugActualizarMonitor, 500);
    };

    setInterval(debugActualizarMonitor, 1000);

    // ==========================================
    // 5. EVENTOS DE INICIO Y CAZADOR DE ERRORES
    // ==========================================
    window.addEventListener('error', function (e) {
        alert("🚨 ERROR DETECTADO EN JS:\n\n" + e.message + "\n\nLínea: " + e.lineno);
    });

    document.addEventListener('DOMContentLoaded', function () {
        updateCartBadge();
        debugActualizarMonitor();

        // Lógica visual del Acordeón
        const accordions = document.querySelectorAll('.accordion-collapse');
        accordions.forEach(acc => {
            acc.addEventListener('show.bs.collapse', function () {
                const btn = document.querySelector(`[data-bs-target="#${this.id}"]`);
                if (btn) btn.querySelector('.custom-chevron').classList.add('rotated');
            });
            acc.addEventListener('hide.bs.collapse', function () {
                const btn = document.querySelector(`[data-bs-target="#${this.id}"]`);
                if (btn) btn.querySelector('.custom-chevron').classList.remove('rotated');
            });
        });

        // Verificaciones de salud
        if (typeof Swal === 'undefined') {
            alert("⚠️ ATENCIÓN: La librería SweetAlert2 no está cargada.");
        }
    });
</script>
<?php require_once 'includes/footer.php'; ?>