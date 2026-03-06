<?php
ob_start(); 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php'; 

// --- [NUEVO] LÓGICA DE BÚSQUEDA AJAX EN EL MISMO ARCHIVO ---
if (isset($_GET['ajax_search']) && isset($_GET['q'])) {
    // Limpiamos cualquier salida previa
    ob_clean(); 
    
    $q = trim($_GET['q']);
    if (strlen($q) >= 2) {
        try {
            // Buscamos coincidencia en nombre o SKU
            $stmt = $pdo->prepare("SELECT nombre, sku, imagen_url, slug FROM productos WHERE (nombre LIKE ? OR sku LIKE ?) AND activo = 1 LIMIT 6");
            $stmt->execute(["%$q%", "%$q%"]);
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($resultados) {
                foreach ($resultados as $r) {
                    $img = !empty($r['imagen_url']) ? $r['imagen_url'] : '/assets/img/no-photo.png';
                    // Ajuste rápido de ruta si es necesario
                    $img = str_replace('/var/www/html', '', $img); 

                    echo '
                    <a href="/pagina/producto/'.$r['slug'].'" class="search-item">
                        <img src="'.$img.'" alt="Producto">
                        <div class="info">
                            <h6>'.$r['nombre'].'</h6>
                            <span>SKU: '.$r['sku'].'</span>
                        </div>
                    </a>';
                }
                echo '<a href="/pagina/categoria.php?q='.urlencode($q).'" class="search-view-all">VER TODOS LOS RESULTADOS</a>';
            } else {
                echo '<div class="p-3 text-center text-muted small">No encontramos coincidencias.</div>';
            }
        } catch (Exception $e) {
            echo ''; // Silencio en caso de error para no romper el diseño
        }
    }
    exit; // <--- IMPORTANTE: Aquí matamos el script para que no cargue el resto de la página
}
// -----------------------------------------------------------


$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
$baseUrl = $protocol . "://" . $_SERVER['HTTP_HOST'] . "/pagina";

// 2. LÓGICA DE CATEGORÍAS
$menu_jerarquico = [];
try {
    $stmt = $pdo->query("SELECT * FROM categorias ORDER BY padre_id ASC, orden ASC");
    $todas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $referencias = [];
    foreach ($todas as $cat) {
        $cat['sub'] = [];
        $referencias[$cat['id']] = $cat;
    }
    foreach ($referencias as $id => &$cat) {
        if ($cat['padre_id'] == NULL) {
            $menu_jerarquico[$id] = &$cat;
        } else {
            if (isset($referencias[$cat['padre_id']])) {
                $referencias[$cat['padre_id']]['sub'][$id] = &$cat;
            }
        }
    }
} catch (PDOException $e) {
    error_log("Error en menú: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-XXXXXXXXXX"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', 'G-XXXXXXXXXX');
    </script>
    <base href="/pagina/">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <?php
    if (!isset($baseUrl)) {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        $baseUrl = $protocol . "://" . $_SERVER['HTTP_HOST'] . "/pagina";
    }
    /* =========================
       CONFIGURACIÓN SEO GLOBAL
    ========================== */
    $siteName  = "Maquimpower";
    $pageTitle = "Maquinaria y equipos profesionales de limpieza en Perú | Maquimpower";
    $pageDesc  = "Maquinaria, equipos e insumos de limpieza profesional en Perú. Especialistas en carwash y detailing con marcas premium y asesoría continua.";
    $pageImage = $baseUrl . "/assets/img/hero-machine.png";
    $pageUrl   = $baseUrl . $_SERVER['REQUEST_URI'];
    $pageType  = "website";

    /* =========================
       SEO ESPECÍFICO PRODUCTO
    ========================== */
    if (isset($p) && !empty($p['nombre'])) {
        $pageTitle = $p['nombre'] . " | " . $siteName;
        $pageDesc  = strip_tags($p['descripcion']);

        if (strlen($pageDesc) > 160) {
            $pageDesc = substr($pageDesc, 0, 157) . "...";
        }

        if (!empty($p['imagen_url'])) {
            $pageImage = (strpos($p['imagen_url'], 'http') === 0)
                ? $p['imagen_url']
                : $baseUrl . $p['imagen_url'];
        }

        $pageType = "product";
    }
    ?>

    <!-- TITLE -->
    <title><?= htmlspecialchars($pageTitle) ?></title>

    <!-- SEO BÁSICO -->
    <meta name="description" content="<?= htmlspecialchars($pageDesc) ?>">
    <meta name="keywords" content="hidrolavadoras, aspiradoras industriales, carwash peru, limpieza profesional, detailing, karcher, nilfisk">
    <meta name="author" content="Corporacion Maquimsa E.I.R.L.">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?= $pageUrl ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    <!-- OPEN GRAPH -->
    <meta property="og:site_name" content="<?= $siteName ?>">
    <meta property="og:title" content="<?= htmlspecialchars($pageTitle) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($pageDesc) ?>">
    <meta property="og:image" content="<?= htmlspecialchars($pageImage) ?>">
    <meta property="og:url" content="<?= $pageUrl ?>">
    <meta property="og:type" content="<?= $pageType ?>">
    <meta property="og:locale" content="es_PE">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">

    <!-- TWITTER -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($pageTitle) ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($pageDesc) ?>">
    <meta name="twitter:image" content="<?= htmlspecialchars($pageImage) ?>">

    <!-- SCHEMA -->
    <?php if ($pageType === 'product'): ?>
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Product",
      "name": "<?= $p['nombre'] ?>",
      "image": "<?= $pageImage ?>",
      "description": "<?= $pageDesc ?>",
      "sku": "<?= $p['sku'] ?>",
      "brand": {
        "@type": "Brand",
        "name": "MaquimPower"
      },
      "offers": {
        "@type": "Offer",
        "url": "<?= $pageUrl ?>",
        "priceCurrency": "PEN",
        "price": "<?= ($p['precio_oferta'] > 0 ? $p['precio_oferta'] : $p['precio']) ?>",
        "availability": "<?= ($p['stock_actual'] > 0) ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock' ?>",
        "itemCondition": "https://schema.org/NewCondition"
      }
    }
    </script>
    <?php else: ?>
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Organization",
      "name": "MaquimPower",
      "url": "<?= $baseUrl ?>",
      "logo": "<?= $baseUrl ?>/assets/img/logo_mp/Logo_3.jpg",
      "contactPoint": {
        "@type": "ContactPoint",
        "telephone": "+51-902-010-281",
        "contactType": "customer service",
        "areaServed": "PE",
        "availableLanguage": "Spanish"
      },
      "sameAs": [
        "https://www.facebook.com/MaquimPower/",
        "https://www.instagram.com/maquimpower/",
        "https://www.tiktok.com/@maquimpower",
        "https://www.youtube.com/@Maquimpower"
      ]
    }
    </script>
    <?php endif; ?>

    <!-- FAVICON -->
    <!-- FAVICON usando la misma imagen que el logo -->
<link rel="icon" type="image/png" href="<?= $baseUrl ?>/assets/img/logo_mp/Logo_2.jpg">
<link rel="apple-touch-icon" href="<?= $baseUrl ?>/assets/img/logo_mp/Logo_2.jpg">


    <!-- CSS LIBRERÍAS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/pagina//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css">
    <link rel="stylesheet" href="/pagina//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css">
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/style.css?v=<?= time() ?>">

    <!-- ESTILOS INLINE (UI / USER MENU) -->
<style>
    /* --- ARREGLO VISUAL DEL INPUT Y FONDO --- */
    /* Forzamos que la cajita del buscador sea blanca */
    .search-modern-mobile {
        background-color: #ffffff !important; 
        border: 1px solid #d1d1d1 !important;
        color: #000 !important;
    }
    
    /* Forzamos que el texto que escribes sea negro */
    #mobileSearchInput {
        color: #000000 !important;
        background: transparent !important;
    }
    
    /* Color del texto de ayuda (placeholder) */
    #mobileSearchInput::placeholder {
        color: #6c757d !important;
        opacity: 1;
    }

    /* --- CONTENEDOR DE RESULTADOS FLOTANTE --- */
    .live-search-results {
        position: absolute;
        top: 100%;
        left: 0;
        width: 100%;
        background-color: #ffffff !important; /* Fondo blanco puro */
        border: 1px solid #ddd;
        border-top: none;
        border-radius: 0 0 15px 15px;
        box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        z-index: 99999 !important;
        max-height: 400px;
        overflow-y: auto;
    }

    /* Estilo de cada fila de producto */
    .search-item {
        display: flex;
        align-items: center;
        padding: 12px 15px;
        border-bottom: 1px solid #f0f0f0;
        text-decoration: none;
        background-color: #ffffff !important;
        transition: background 0.2s;
    }

    .search-item:hover, .search-item:active {
        background-color: #f2f2f2 !important; /* Gris suave al tocar */
    }

    /* Imagen pequeña */
    .search-item img {
        width: 45px; 
        height: 45px; 
        object-fit: contain; 
        margin-right: 12px;
        border: 1px solid #eee;
        border-radius: 6px;
        background: #fff;
    }

    /* Textos */
    .search-item .info h6 {
        color: #000000 !important;
        font-weight: 800;
        font-size: 0.8rem;
        margin: 0;
        line-height: 1.2;
        text-transform: uppercase;
    }
    .search-item .info span {
        color: #666 !important;
        font-size: 0.7rem;
    }

    /* Enlace "Ver todos" */
    .search-view-all {
        display: block;
        text-align: center;
        padding: 12px;
        color: #ff4500 !important; /* Tu color primario */
        font-weight: 800;
        font-size: 0.75rem;
        text-transform: uppercase;
        text-decoration: none;
        background: #fff;
    }

    /* Scrollbar */
    .live-search-results::-webkit-scrollbar { width: 4px; }
    .live-search-results::-webkit-scrollbar-thumb { background: #ccc; border-radius: 4px; }
    
    /* Estilos generales que ya tenías */
    .user-avatar-circle { width: 35px; height: 35px; background-color: var(--dark); color: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 1rem; border: 2px solid #eee; }
    .user-dropdown-wrapper { position: relative; z-index: 1050; }
    .user-dropdown-wrapper .dropdown-menu { display: none; position: absolute !important; z-index: 99999 !important; right: 0 !important; left: auto !important; box-shadow: 0 10px 40px rgba(0,0,0,0.2) !important; background-color: #ffffff !important; }
    .user-dropdown-wrapper .dropdown-menu.show { display: block !important; }
    .col-lg-4 { overflow: visible !important; }
    .nav-item-mega { position: static; }
    .nav-link-corp { color: #333; padding: 1rem 1.2rem; font-size: 0.85rem; display: block; text-decoration: none; transition: all 0.3s ease; }
    .nav-item-mega:hover .nav-link-corp { color: var(--primary) !important; }
    .mega-menu { position: absolute; top: 100%; left: 0; width: 100%; background: rgba(255,255,255,0.98); backdrop-filter: blur(10px); z-index: 9999; opacity: 0; visibility: hidden; transform: translateY(20px); transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1); border-top: 3px solid var(--primary); }
    .nav-item-mega:hover .mega-menu { opacity: 1; visibility: visible; transform: translateY(0); }
    .fw-black { font-weight: 900; }
    .avatar-sm { width: 35px; height: 35px; font-size: 0.9rem; }
    .pill-link { background: #fff; color: #666; padding: 8px 16px; border-radius: 12px; font-size: 0.8rem; text-decoration: none; border: 1px solid #e0e0e0; font-weight: 500; }
    .accordion-button:not(.collapsed) { color: var(--primary) !important; background: transparent !important; }
</style>
    
</head>

<body class="d-flex flex-column min-vh-100">

    <!-- 1. TOP BAR -->
    <div class="top-bar d-none d-md-block">
        <div class="container d-flex justify-content-between">
            <span><i class="bi bi-truck"></i> Envíos a todo el Perú</span>
            <span><i class="bi bi-shield-check"></i> Garantía Asegurada</span>
        </div>
    </div>

<div class="header-main bg-white py-2 py-lg-3 shadow-sm sticky-top" style="min-height: 100px; display: flex; align-items: center;">
    <div class="container">
        
        <!-- VISTA ESCRITORIO (PC) -->
        <div class="row align-items-center d-none d-lg-flex">
            <!-- 1. LOGO -->
            <div class="col-lg-4"> 
                <a href="/pagina/" class="d-inline-block">
                    <img src="<?= $baseUrl ?>/assets/img/logo_mp/MaquimPower_Logotipo_Agosto.png" 
                         alt="MAQUIMPOWER" 
                         class="header-logo-img" 
                         style="max-height: 200px; width: auto; transition: transform 0.3s ease; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.05));">
                </a>
            </div>

            <!-- 2. BUSCADOR -->
            <div class="col-lg-4">
                <form class="search-modern" action="/pagina/categoria.php" method="GET">
                    <input type="text" name="q" placeholder="¿Qué máquina buscas hoy?" required>
                    <button type="submit"><i class="bi bi-search"></i></button>
                </form>
            </div>

            <!-- 3. ACCIONES (USUARIO + CARRITO) -->
            <div class="col-lg-4 d-flex justify-content-end align-items-center gap-4">
                
                <!-- A. Dropdown Usuario -->
              <div class="user-dropdown-wrapper">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <div class="dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center border-0 p-0" 
                            href="javascript:void(0)" 
                            id="userMenuClick" 
                            role="button">
                                <div class="avatar-circle shadow-sm">
                                    <?= strtoupper(substr($_SESSION['user_name'], 0, 1)); ?>
                                </div>
                                <div class="ms-2 text-start lh-1 d-none d-md-block">
                                    <div class="welcome-text">HOLA,</div>
                                    <div class="user-name-text"><?= explode(' ', $_SESSION['user_name'])[0]; ?></div>
                                </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-3 p-2 rounded-4" style="min-width: 230px;">
                            
                            <li class="px-3 py-2 mb-2 bg-light rounded-3 d-md-none">
                                <span class="fw-bold d-block text-dark"><?= $_SESSION['user_name']; ?></span>
                                <small class="text-muted"><?= $_SESSION['user_email'] ?? 'Usuario'; ?></small>
                            </li>

                            <?php if(isset($_SESSION['user_role']) && strtolower($_SESSION['user_role']) === 'admin'): ?>
                                <li class="px-2 mb-2">
                                    <div class="admin-badge">
                                        <i class="bi bi-shield-lock-fill me-2"></i>MODO ADMINISTRADOR
                                    </div>
                                </li>
                                <li>
                                    <a class="dropdown-item py-2 fw-bold text-primary" href="/pagina/admin/dashboard.php">
                                        <i class="bi bi-speedometer2 me-2"></i> Dashboard Control
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider opacity-50"></li>
                            <?php endif; ?>
                            
                            <li><a class="dropdown-item py-2" href="/pagina/perfil.php"><i class="bi bi-bag-check me-2"></i> Mis Pedidos</a></li>
                            <li><a class="dropdown-item py-2" href="/pagina/perfil.php#cuenta"><i class="bi bi-person-gear me-2"></i> Mi Perfil</a></li>
                            <li><hr class="dropdown-divider opacity-50"></li>
                            
                            <li>
                                <a class="dropdown-item py-2 text-danger fw-bold" href="/pagina/controllers/auth.php?action=logout">
                                    <i class="bi bi-box-arrow-right me-2"></i> Cerrar Sesión
                                </a>
                            </li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="/pagina/login.php" class="btn btn-outline-dark btn-sm rounded-pill px-3 fw-bold shadow-sm hover-primary">
                        <i class="bi bi-person me-1"></i> INGRESAR
                    </a>
                <?php endif; ?>
            </div>

                <!-- B. Carrito (Recuperado) -->
                <a href="/pagina/carrito" class="text-dark d-flex align-items-center text-decoration-none position-relative">
                    <i class="bi bi-cart3 fs-2"></i>
                    <span class="badge bg-danger position-absolute top-0 start-100 translate-middle rounded-pill border border-white" style="font-size: 0.7rem;">
                        <?= isset($_SESSION['carrito']) ? count($_SESSION['carrito']) : 0 ?>
                    </span>
                    <div class="ms-2 text-start lh-1 d-none d-xl-block">
                        <small class="text-muted" style="font-size:10px">CARRITO</small><br>
                        <span id="header-total" class="fw-bold">S/ <?= number_format($carritoTotal ?? 0, 2) ?></span>
                    </div>
                </a>

            </div> <!-- CIERRE col-lg-4 (Faltaba este) -->
        </div> <!-- CIERRE row (Faltaba este) -->

        <!-- VISTA MÓVIL (MOBILE) -->
        <div class="d-lg-none">
            <div class="d-flex align-items-center justify-content-between">
                <button class="btn btn-link text-dark p-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu">
                    <i class="bi bi-list fs-1"></i>
                </button>
                
                <a href="/pagina/" class="mx-auto">
                    <img src="<?= $baseUrl ?>/assets/img/logo_mp/MaquimPower_Logotipo_Agosto.png" 
                         alt="Logo" 
                         style="max-height: 60px; width: auto; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.05));">
                </a>

                <div class="d-flex align-items-center gap-2">
                    <?php if(!isset($_SESSION['user_id'])): ?>
                        <a href="/pagina/login.php" class="text-dark"><i class="bi bi-person-circle fs-2"></i></a>
                    <?php else: ?>
                        <a href="/pagina/perfil.php" class="text-dark"><i class="bi bi-person-circle fs-2"></i></a>
                    <?php endif; ?>
                    <a href="/pagina/carrito" class="text-dark position-relative">
                        <i class="bi bi-cart3 fs-2"></i>
                        <span class="badge bg-danger position-absolute top-0 start-100 translate-middle rounded-pill" style="font-size: 0.5rem;">0</span>
                    </a>
                </div>
            </div>
            
            <div class="mt-3 position-relative"> <form class="search-modern-mobile bg-light d-flex rounded-pill px-3 py-2" action="/pagina/categoria.php" method="GET">
        <input type="text" id="mobileSearchInput" name="q" class="form-control border-0 bg-transparent shadow-none" placeholder="Buscar máquinas o repuestos..." autocomplete="off" required>
        <button type="submit" class="btn text-primary p-0"><i class="bi bi-search fs-5"></i></button>
    </form>
    
    <div id="mobileSearchResults" class="live-search-results d-none"></div>
</div>
        </div>

    </div> 
</div> 

    <!-- 3. MEGA MENÚ DINÁMICO (PC) -->
<div class="header-nav-wrapper d-none d-lg-block bg-white border-bottom shadow-sm">
    <div class="container">
        <ul class="nav justify-content-center position-relative">
            
            <li class="nav-item-mega">
                <a href="/pagina/categoria/ofertas" class="nav-link-corp text-danger fw-bold d-flex align-items-center">
                    <span class="pulse-red me-2"></span> ⚡ PROMOS
                </a>
            </li>

            <?php foreach ($menu_jerarquico as $h1): ?>
            <li class="nav-item-mega group">
                <a href="/pagina/categoria/<?= $h1['slug'] ?>" class="nav-link-corp text-uppercase fw-bold">
                    <?= $h1['nombre'] ?> 
                    <?php if(!empty($h1['sub'])) echo '<i class="bi bi-chevron-down ms-1 small opacity-50"></i>'; ?>
                </a>
                
                <?php if(!empty($h1['sub'])): ?>
                <div class="mega-menu shadow-lg rounded-bottom-4">
                    <div class="container py-4">
                        <div class="row g-4">
                            
                            <?php foreach($h1['sub'] as $h2): ?>
                                <div class="col-lg-3 mega-column">
                                    <h6 class="text-dark fw-black text-uppercase mb-3 border-bottom pb-2" style="font-size: 0.8rem; letter-spacing: 1px;">
                                        <?= $h2['nombre'] ?>
                                    </h6>
                                  <div class="nietos-wrapper">
                                    <?php if(!empty($h2['sub'])): ?>
                                        <?php foreach($h2['sub'] as $h3): ?>
                                            <a href="/pagina/categoria/<?= $h3['slug'] ?>" class="d-block py-1 text-muted text-decoration-none hover-orange small">
                                                <i class="bi bi-caret-right-fill text-primary opacity-25 me-1"></i>
                                                <?= $h3['nombre'] ?>
                                            </a>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                                    <a href="/pagina/categoria/<?= $h2['slug'] ?>" class="fw-bold text-primary mt-3 d-inline-block small text-decoration-none">
                                        VER TODO <i class="bi bi-arrow-right"></i>
                                    </a>
                                </div>
                            <?php endforeach; ?>

                            <div class="col-lg-3 ms-auto border-start ps-4">
                                <div class="menu-promo-card text-center bg-light p-3 rounded-4">
                                    <div class="overflow-hidden rounded-3 mb-3 shadow-sm">
                                        <img src="<?= $baseUrl ?>/assets/img/logo_mp/img_2.jpg" class="img-fluid hover-zoom" alt="MaquimPower Promo">
                                    </div>
                                    <p class="small fw-bold text-dark mb-1">CALIDAD GARANTIZADA</p>
                                    <p class="text-muted mb-3" style="font-size: 0.7rem;">Soporte técnico especializado en toda la línea <?= $h1['nombre'] ?>.</p>
                                    <a href="/pagina/categoria/<?= $h1['slug'] ?>" class="btn btn-primary btn-sm rounded-pill w-100 fw-bold shadow-sm">
                                        VER CATÁLOGO
                                    </a>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </li>

            <?php endforeach; ?>

            <li class="nav-item-mega"><a href="academia.php" class="nav-link-corp">Academia</a></li>
            <li class="nav-item-mega"><a href="blog.php" class="nav-link-corp">Blog</a></li>
            <li class="nav-item-mega"><a href="contacto.php" class="nav-link-corp">Contacto</a></li>
            <li class="nav-item-mega">
                <a href="https://www.tiktok.com/@maquimpower" target="_blank" class="nav-link-corp text-dark fw-bold">
                    <i class="bi bi-tiktok me-1"></i> TikTok
                </a>
            </li>
        </ul>
    </div>
</div>

<style>
/* --- ESTILOS DE IMPACTO --- */
.nav-item-mega { position: static; }

.nav-link-corp {
    color: #333;
    padding: 1rem 1.2rem;
    font-size: 0.85rem;
    display: block;
    text-decoration: none;
    transition: all 0.3s ease;
}

.nav-item-mega:hover .nav-link-corp {
    color: var(--primary) !important;
}

.mega-menu {
    position: absolute;
    top: 100%;
    left: 0;
    width: 100%;
    background: rgba(255,255,255,0.98);
    backdrop-filter: blur(10px);
    z-index: 9999;
    opacity: 0;
    visibility: hidden;
    transform: translateY(20px);
    transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
    border-top: 3px solid var(--primary);
}

.nav-item-mega:hover .mega-menu {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.hover-orange:hover {
    color: var(--primary) !important;
    padding-left: 5px;
}

.hover-zoom {
    transition: transform 0.5s ease;
}
.menu-promo-card:hover .hover-zoom {
    transform: scale(1.1);
}

.pulse-red {
    width: 8px;
    height: 8px;
    background: #ff0000;
    border-radius: 50%;
    display: inline-block;
    box-shadow: 0 0 0 rgba(255, 0, 0, 0.4);
    animation: pulse-red 2s infinite;
}

@keyframes pulse-red {
    0% { box-shadow: 0 0 0 0 rgba(255, 0, 0, 0.7); }
    70% { box-shadow: 0 0 0 10px rgba(255, 0, 0, 0); }
    100% { box-shadow: 0 0 0 0 rgba(255, 0, 0, 0); }
}

.fw-black { font-weight: 900; }
</style>

    <!-- 4. MENÚ MÓVIL OFFCANVAS -->
    <div class="offcanvas offcanvas-start border-0 shadow-lg" tabindex="-1" id="mobileMenu" style="width: 85%;">
    
    <div class="offcanvas-header bg-white border-bottom py-3">
        <h5 class="offcanvas-title fw-black text-dark" style="letter-spacing: -1px;">EXPLORAR</h5>
        <button type="button" class="btn-close shadow-none" data-bs-dismiss="offcanvas"></button>
    </div>

    <div class="offcanvas-body p-0 bg-white">
        <div class="px-3 py-4">
            <?php if(isset($_SESSION['user_id'])): ?>
                <div class="user-card-mobile d-flex align-items-center p-3 rounded-4 bg-dark text-white shadow-sm">
                    <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center fw-bold me-3">
                        <?= strtoupper(substr($_SESSION['user_name'], 0, 1)); ?>
                    </div>
                    <div class="lh-sm">
                        <p class="m-0 small text-white-50">Bienvenido,</p>
                        <p class="m-0 fw-bold"><?= explode(' ', $_SESSION['user_name'])[0]; ?></p>
                    </div>
                    <a href="/pagina/controllers/auth.php?action=logout" class="ms-auto text-white-50 fs-4"><i class="bi bi-box-arrow-right"></i></a>
                </div>
            <?php else: ?>
                <a href="/pagina/login.php" class="btn btn-primary w-100 py-3 rounded-4 fw-black shadow-sm" style="font-size: 0.9rem;">
                    INGRESAR A MI CUENTA
                </a>
            <?php endif; ?>
        </div>

        <div class="accordion accordion-flush" id="mobAcc">
            
            <div class="px-3 mb-2">
                <a href="/pagina/categoria/ofertas" class="d-flex align-items-center p-3 rounded-3 bg-danger bg-opacity-10 text-danger text-decoration-none fw-bold">
                    <i class="bi bi-lightning-charge-fill me-2"></i> OFERTAS DEL MES
                    <i class="bi bi-chevron-right ms-auto small"></i>
                </a>
            </div>

            <?php foreach ($menu_jerarquico as $h1): ?>
                <div class="accordion-item border-0">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed py-3 px-4 fw-bold text-dark text-uppercase border-0 shadow-none bg-transparent" 
                                type="button" data-bs-toggle="collapse" data-bs-target="#m-<?= $h1['id'] ?>" style="font-size: 0.95rem;">
                            <?= $h1['nombre'] ?>
                        </button>
                    </h2>
                    <div id="m-<?= $h1['id'] ?>" class="accordion-collapse collapse" data-bs-parent="#mobAcc">
                        <div class="accordion-body p-0 pb-3 bg-light">
                            
                            <?php foreach ($h1['sub'] as $h2): ?>
                                <div class="px-4 py-3">
                                    <a href="/pagina/categoria/<?= $h2['slug'] ?>" class="d-flex align-items-center mb-3 text-decoration-none">
                                        <div class="bg-primary rounded-1 me-2" style="width: 4px; height: 16px;"></div>
                                        <span class="fw-bold text-dark small text-uppercase"><?= $h2['nombre'] ?></span>
                                    </a>
                                    
                                    <div class="d-flex flex-wrap gap-2">
                                        <?php foreach ($h2['sub'] as $h3): ?>
                                            <a href="/pagina/categoria/<?= $h3['slug'] ?>" class="pill-link">
                                                <?= $h3['nombre'] ?>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <div class="px-4 pt-2">
                                <a href="/pagina/categoria/<?= $h1['slug'] ?>" class="btn btn-outline-dark btn-sm w-100 rounded-pill fw-bold py-2">
                                    Ver todo <?= $h1['nombre'] ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="mt-2">
            <div class="px-3 mb-3">
                <a href="https://www.tiktok.com/@maquimpower" target="_blank" class="tiktok-card d-flex align-items-center p-3 rounded-4 text-white text-decoration-none shadow-sm">
                    <div class="tiktok-icon-wrapper me-3">
                        <i class="bi bi-tiktok fs-4"></i>
                    </div>
                    <div>
                        <p class="m-0 fw-black" style="font-size: 0.9rem; letter-spacing: 0.5px;">SÍGUENOS EN TIKTOK</p>
                        <p class="m-0 small opacity-75">Mira nuestras máquinas en acción</p>
                    </div>
                    <i class="bi bi-arrow-up-right ms-auto opacity-50"></i>
                </a>
            </div>

            <div class="list-group list-group-flush border-top">
                <a href="academia.php" class="list-group-item list-group-item-action py-3 px-4 d-flex align-items-center border-0">
                    <i class="bi bi-mortarboard-fill me-3 text-primary"></i>
                    <span class="fw-bold">Academia Maquimpower</span>
                </a>
                <a href="blog.php" class="list-group-item list-group-item-action py-3 px-4 d-flex align-items-center border-0">
                    <i class="bi bi-journal-text me-3 text-primary"></i>
                    <span class="fw-bold">Blog de Noticias</span>
                </a>
                <a href="contacto.php" class="list-group-item list-group-item-action py-3 px-4 d-flex align-items-center border-0 mb-4">
                    <i class="bi bi-chat-left-dots-fill me-3 text-primary"></i>
                    <span class="fw-bold">Contacto Directo</span>
                </a>
            </div>
        </div>
    </div>
</div>
<style>
/* CSS Limpio para Móvil */
.fw-black { font-weight: 900; }

.avatar-sm { width: 35px; height: 35px; font-size: 0.9rem; }

.pill-link {
    background: #fff;
    color: #666;
    padding: 8px 16px;
    border-radius: 12px;
    font-size: 0.8rem;
    text-decoration: none;
    border: 1px solid #e0e0e0;
    font-weight: 500;
}

.pill-link:active {
    background: var(--primary);
    color: white !important;
    border-color: var(--primary);
}

.accordion-button::after {
    filter: grayscale(1);
    transform: scale(0.8);
}

.accordion-button:not(.collapsed) {
    color: var(--primary) !important;
    background: transparent !important;
}

.user-card-mobile {
    background: linear-gradient(135deg, #0a0a0a 0%, #222 100%);
}
.tiktok-card {
    background: #000000;
    background: linear-gradient(135deg, #000000 0%, #25F4EE 300%);
    position: relative;
    overflow: hidden;
    transition: transform 0.2s;
}

.tiktok-card:active {
    transform: scale(0.98);
}

.tiktok-icon-wrapper {
    width: 45px;
    height: 45px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

/* Ajuste de los iconos corporativos */
.list-group-item i {
    font-size: 1.2rem;
    transition: transform 0.3s;
}

.list-group-item:hover i {
    transform: scale(1.2);
}

.fw-black { font-weight: 900; }
</style>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // =========================================================
    // 1. LÓGICA DEL MENÚ DE USUARIO 
    // =========================================================
    const userMenuTrigger = document.getElementById('userMenuClick');
    const dropdownMenu = userMenuTrigger?.nextElementSibling;

    if (userMenuTrigger && dropdownMenu) {
        userMenuTrigger.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation(); // Evita clics fantasmas

            // 1. Verificamos si YA estaba abierto antes de cerrar todo
            const estabaAbierto = dropdownMenu.classList.contains('show');

            // 2. Cerramos TODOS los menús abiertos (limpieza general)
            document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                menu.classList.remove('show');
            });

            // 3. Si NO estaba abierto, lo abrimos ahora
            if (!estabaAbierto) {
                dropdownMenu.classList.add('show');
                // Forzamos la posición para asegurar que se vea bien
                dropdownMenu.style.position = 'absolute';
                dropdownMenu.style.inset = '0px 0px auto auto';
                dropdownMenu.style.transform = 'translate3d(0px, 45px, 0px)';
            }
        });

        // Cerrar al hacer clic fuera del menú
        document.addEventListener('click', function(e) {
            if (!userMenuTrigger.contains(e.target) && !dropdownMenu.contains(e.target)) {
                dropdownMenu.classList.remove('show');
            }
        });
    }

    // =========================================================
    // 2. LÓGICA DEL BUSCADOR MÓVIL (AJAX)
    // =========================================================
    const inputMovil = document.getElementById('mobileSearchInput');
    const resultadosMovil = document.getElementById('mobileSearchResults');

    if (inputMovil && resultadosMovil) {
        inputMovil.addEventListener('keyup', function() {
            let termino = this.value.trim();

            if (termino.length >= 2) {
                resultadosMovil.classList.remove('d-none');
                resultadosMovil.innerHTML = '<div class="p-3 text-center text-muted small"><div class="spinner-border spinner-border-sm text-primary me-2"></div>Buscando...</div>';

                // Construimos la URL segura (sin parámetros viejos)
                let urlBase = window.location.origin + window.location.pathname;
                let searchUrl = urlBase + '?ajax_search=1&q=' + encodeURIComponent(termino);

                fetch(searchUrl)
                .then(response => {
                    if (!response.ok) throw new Error('Error en red');
                    return response.text();
                })
                .then(html => {
                    // Verificamos si la respuesta es válida
                    if (html.trim() === "" || html.includes("<!DOCTYPE")) { 
                        resultadosMovil.innerHTML = '<div class="p-3 text-center text-muted small">Sin resultados.</div>';
                    } else {
                        resultadosMovil.innerHTML = html;
                    }
                })
                .catch(err => {
                    console.error(err);
                    resultadosMovil.innerHTML = '<div class="p-3 text-center text-danger small">Error de conexión.</div>';
                });

            } else {
                resultadosMovil.classList.add('d-none');
            }
        });

        // Cerrar resultados al tocar fuera
        document.addEventListener('click', function(e) {
            if (!inputMovil.contains(e.target) && !resultadosMovil.contains(e.target)) {
                resultadosMovil.classList.add('d-none');
            }
        });
    }
});
</script>

    <!-- Contenedor Principal -->
<div class="main-content flex-grow-1">