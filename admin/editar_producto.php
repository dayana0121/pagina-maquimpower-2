<?php
// Configuración básica para evitar errores silenciosos
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'check_auth.php';
require_once '../includes/db.php';

// --- 1. PREPARACIÓN DE CATEGORÍAS ---
$stmtCats = $pdo->query("SELECT * FROM categorias ORDER BY padre_id ASC, nombre ASC");
$allCatsData = $stmtCats->fetchAll();

$tree = [];
$referencias = [];
foreach ($allCatsData as $c) {
    $c['children'] = [];
    $referencias[$c['id']] = $c;
    if ($c['padre_id'] == null) {
        $tree[$c['id']] = &$referencias[$c['id']];
    } else {
        if (isset($referencias[$c['padre_id']])) {
            $referencias[$c['padre_id']]['children'][] = &$referencias[$c['id']];
        }
    }
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$msg = '';

// --- 2. PROCESAR EL GUARDADO (POST) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // DATOS TEXTO
    $nombre = strtoupper(trim($_POST['nombre'] ?? ''));
    $sku = strtoupper(trim($_POST['sku'] ?? ''));
    $precio = floatval($_POST['precio'] ?? 0);
    $stock = (int) ($_POST['stock'] ?? 0);
    $desc = $_POST['descripcion'] ?? '';
    $cat = strtoupper(trim($_POST['categoria'] ?? ''));
    $video = $_POST['video_url'] ?? '';
    $etiqueta = $_POST['etiqueta'] ?? '';
    $meta_desc = $_POST['meta_description'] ?? '';

    // SEO / SLUG
    $imagen_alt = trim($_POST['imagen_alt'] ?? '');
    if (empty($imagen_alt)) $imagen_alt = $nombre;

    $slugInput = trim($_POST['slug'] ?? '');
    if (empty($slugInput)) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $nombre)));
    } else {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $slugInput)));
    }

    // PRECIOS
    $porcentaje = intval($_POST['descuento_porcentaje'] ?? 0);
    $precio_lista = ($porcentaje > 0 && $precio > 0) ? ($precio / (1 - ($porcentaje / 100))) : NULL;
    if ($porcentaje > 0 && empty($etiqueta)) $etiqueta = 'OFERTA';

    // ---------------------------------------------------------
    // 3. LÓGICA DEL PDF (ARREGLADA)
    // ---------------------------------------------------------
    $pdfFinal = $_POST['pdf_actual'] ?? null; // Mantener el anterior por defecto

    // A. Si pidieron borrar
    if (isset($_POST['borrar_pdf']) && $_POST['borrar_pdf'] == '1') {
        $pdfFinal = null; 
    }

    // B. Si subieron archivo nuevo
    if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['size'] > 0) {
        
        if ($_FILES['pdf_file']['error'] === UPLOAD_ERR_OK) {
            $pdfName = $_FILES['pdf_file']['name'];
            $pdfExt = strtolower(pathinfo($pdfName, PATHINFO_EXTENSION));
            
            if ($pdfExt === 'pdf') {
                // Definir carpeta física y url web
                $uploadDir = "../assets/fichas_tecnicas/"; // Dónde se guarda físicamente
                $webPath = "/assets/fichas_tecnicas/";     // Cómo se guarda en la BD

                // ¡LA SOLUCIÓN! Verificar si existe la carpeta, si no, crearla
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $newPdfName = "ficha-" . $slug . "-" . time() . ".pdf";
                
                // Mover archivo
                if (move_uploaded_file($_FILES['pdf_file']['tmp_name'], $uploadDir . $newPdfName)) {
                    $pdfFinal = $webPath . $newPdfName;
                } else {
                    $msg .= "⚠️ Error al mover el PDF a la carpeta. Revisa permisos. ";
                }
            } else {
                $msg .= "⚠️ El archivo subido no es un PDF válido. ";
            }
        }
    } 
    // C. Si pusieron link externo
    elseif (!empty($_POST['pdf_link'])) {
        $pdfFinal = trim($_POST['pdf_link']);
    }

    // ---------------------------------------------------------
    // 4. LÓGICA DE IMÁGENES
    // ---------------------------------------------------------
    $finalImages = $_POST['keep_images'] ?? [];
    $uploadedMap = [];

    if (isset($_FILES['galeria_files']) && !empty($_FILES['galeria_files']['name'][0])) {
        $targetDir = "../assets/img/productos/";
        if (!file_exists($targetDir)) mkdir($targetDir, 0777, true);

        foreach ($_FILES['galeria_files']['name'] as $i => $fname) {
            if ($_FILES['galeria_files']['error'][$i] === 0) {
                $ext = strtolower(pathinfo($fname, PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                    $newName = $slug . '-' . time() . '-' . $i . '.' . $ext;
                    if (move_uploaded_file($_FILES['galeria_files']['tmp_name'][$i], $targetDir . $newName)) {
                        $webUrl = "/assets/img/productos/" . $newName;
                        $finalImages[] = $webUrl; 
                        $uploadedMap['new_' . $i] = $webUrl; 
                    }
                }
            }
        }
    }

    // Portada
    $selection = $_POST['cover_selection'] ?? '';
    $imgFinal = '/assets/img/no-photo.png';

    if (strpos($selection, 'existing:') === 0) {
        $imgFinal = substr($selection, 9);
    } elseif (strpos($selection, 'new_') === 0) {
        if (isset($uploadedMap[$selection])) $imgFinal = $uploadedMap[$selection];
    } elseif (!empty($finalImages)) {
        $imgFinal = $finalImages[0];
    } elseif (!empty($_POST['img_actual']) && in_array($_POST['img_actual'], $finalImages)) {
        $imgFinal = $_POST['img_actual'];
    }

    $finalGallery = array_unique($finalImages);
    $finalGallery = array_diff($finalGallery, [$imgFinal]);
    array_unshift($finalGallery, $imgFinal);
    $galeriaJson = json_encode(array_values($finalGallery));

    // 5. GUARDAR EN BD
    try {
        if ($id > 0) {
            $sql = "UPDATE productos SET nombre=?, sku=?, precio=?, precio_lista=?, stock_actual=?, descripcion=?, meta_description=?, categoria=?, etiqueta=?, video_url=?, pdf_url=?, imagen_url=?, imagen_alt=?, galeria=?, slug=? WHERE id=?";
            $pdo->prepare($sql)->execute([$nombre, $sku, $precio, $precio_lista, $stock, $desc, $meta_desc, $cat, $etiqueta, $video, $pdfFinal, $imgFinal, $imagen_alt, $galeriaJson, $slug, $id]);
            $msg = "✅ Producto actualizado correctamente.";
        } else {
            $sql = "INSERT INTO productos (nombre, sku, precio, precio_lista, stock_actual, descripcion, meta_description, categoria, etiqueta, slug, video_url, pdf_url, imagen_url, imagen_alt, galeria, activo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)";
            $pdo->prepare($sql)->execute([$nombre, $sku, $precio, $precio_lista, $stock, $desc, $meta_desc, $cat, $etiqueta, $slug, $video, $pdfFinal, $imgFinal, $imagen_alt, $galeriaJson]);
            $msg = "✅ Producto creado exitosamente.";
            $id = $pdo->lastInsertId();
        }
    } catch (Exception $e) {
        $msg = "❌ Error SQL: " . $e->getMessage();
    }
}

// --- 6. LEER DATOS PARA FORMULARIO ---
$p = null;
if ($id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE id = ?");
    $stmt->execute([$id]);
    $p = $stmt->fetch();
}

$defaults = [
    'nombre' => '', 'sku' => '', 'precio' => '', 'precio_lista' => '',
    'stock_actual' => 10, 'descripcion' => '', 'meta_description' => '',
    'categoria' => '', 'etiqueta' => '', 'video_url' => '',
    'pdf_url' => '', 'imagen_url' => '', 'imagen_alt' => '',
    'galeria' => '[]', 'slug' => ''
];
$p = is_array($p) ? array_merge($defaults, $p) : $defaults;

$porcentaje_actual = 0;
if ($p['precio'] > 0 && $p['precio_lista'] > 0) {
    $porcentaje_actual = round((($p['precio_lista'] - $p['precio']) / $p['precio_lista']) * 100);
}
$rutaVolver = isset($_SESSION['dashboard_url_volver']) ? $_SESSION['dashboard_url_volver'] : 'dashboard.php';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Editor Maestro | MaquimAdmin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #FF4500;
            --dark-bg: #f4f6f9;
            --header-bg: #1a1a1a;
        }

        body {
            background-color: var(--dark-bg);
            font-family: 'Inter', sans-serif;
            padding-bottom: 50px;
        }

        .admin-header {
            background: var(--header-bg);
            color: white;
            padding: 20px 0;
            border-bottom: 4px solid var(--primary);
            margin-bottom: 30px;
        }

        .card-custom {
            border: none;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            background: white;
            overflow: hidden;
        }

        .card-header-custom {
            background: #222;
            color: white;
            padding: 15px 20px;
            font-weight: bold;
            border-left: 5px solid var(--primary);
        }

        .form-control,
        .form-select {
            border: 2px solid #eee;
            padding: 10px;
            border-radius: 6px;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: none;
        }

        label {
            font-size: 0.75rem;
            font-weight: 800;
            color: #555;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .btn-save {
            background: var(--primary);
            color: white;
            border: none;
            padding: 15px;
            width: 100%;
            border-radius: 50px;
            font-weight: 800;
            letter-spacing: 1px;
            transition: 0.3s;
            box-shadow: 0 5px 15px rgba(255, 69, 0, 0.3);
        }

        .btn-save:hover {
            transform: translateY(-2px);
            background: #ff5714;
        }

        .img-preview {
            width: 100%;
            height: 150px;
            object-fit: contain;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 6px;
        }

        .btn-save:hover {
            transform: translateY(-2px);
            background: #ff5714;
        }

        /* New Gallery Styles */
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }

        .gallery-item {
            position: relative;
            border-radius: 8px;
            overflow: hidden;
            background: white;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            border: 2px solid transparent;
            aspect-ratio: 1;
            transition: 0.2s;
        }

        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Selector de Portada */
        .cover-selector {
            position: absolute;
            top: 8px;
            left: 8px;
            width: 32px;
            height: 32px;
            background: rgba(0, 0, 0, 0.6);
            color: rgba(255, 255, 255, 0.7);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: 0.2s;
            z-index: 10;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .cover-selector:hover {
            background: rgba(0, 0, 0, 0.8);
            color: white;
            transform: scale(1.1);
        }

        .cover-selector.active {
            background: #FFD700;
            color: #000;
            border-color: #FFD700;
            box-shadow: 0 0 10px rgba(255, 215, 0, 0.6);
        }

        /* Botón ELIMINAR */
        .btn-remove-img {
            position: absolute;
            top: 8px;
            right: 8px;
            background: rgba(220, 53, 69, 0.9);
            color: white;
            border: none;
            width: 28px;
            height: 28px;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 10;
            font-size: 14px;
            transition: 0.2s;
        }

        .btn-remove-img:hover {
            background: #dc3545;
            transform: scale(1.1);
        }

        .upload-area {
            transition: 0.3s;
            background-color: #f8f9fa;
        }

        .upload-area:hover {
            background-color: #e9ecef;
            border-color: var(--primary) !important;
        }
        /* Indica que es arrastrable */
.gallery-item {
    cursor: grab; 
    transition: transform 0.2s, box-shadow 0.2s;
}

.gallery-item:active {
    cursor: grabbing;
}

/* Estilo cuando estás arrastrando un elemento (transparencia) */
.sortable-ghost {
    opacity: 0.4;
    background-color: #f8f9fa;
    border: 2px dashed #ccc;
}

/* Estilo del elemento que tienes agarrado */
.sortable-drag {
    opacity: 1;
    background: white;
    box-shadow: 0 10px 20px rgba(0,0,0,0.2);
    transform: scale(1.05); /* Efecto pop-up */
}
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
</head>

<body>

    <div class="admin-header">
            <div class="container d-flex justify-content-between align-items-center mb-4">
            <h3 class="m-0 fw-black"><i class="bi bi-gear-fill me-2"></i> EDITOR DE PRODUCTO</h3>
            
            <a href="<?php echo $rutaVolver; ?>" class="btn btn-dark rounded-pill px-4 fw-bold shadow-sm border-0">
                <i class="bi bi-arrow-left me-2"></i>Volver
            </a>
        </div>
    </div>

    <div class="container">
        <form method="POST" enctype="multipart/form-data">
            <div class="row g-4">

                <div class="col-lg-8">
                    <div class="card-custom mb-4">
                        <div class="card-header-custom">DATOS TÉCNICOS</div>
                        <div class="card-body p-4">
                            <?php if ($msg): ?>
                                <div class="alert alert-success fw-bold mb-4"><?php echo $msg; ?></div><?php endif; ?>

                            <div class="row g-3">
                                <div class="col-12">
                                    <label>Nombre</label>
                                    <input type="text" name="nombre" id="input_nombre" class="form-control fw-bold"
                                        value="<?php echo htmlspecialchars($p['nombre']); ?>" required
                                        oninput="generarSlug()">
                                </div>
                                <div class="col-12">
                                    <label class="text-muted small">Slug (URL Amigable)</label>
                                    <input type="text" name="slug" id="input_slug"
                                        class="form-control form-control-sm text-secondary bg-light"
                                        value="<?php echo htmlspecialchars($p['slug'] ?? ''); ?>">
                                    <small class="text-muted" style="font-size: 0.7rem;">Se genera automáticamente, pero
                                        puedes editarlo.</small>
                                </div>
                                <div class="col-md-6">
                                    <label>SKU</label>
                                    <input type="text" name="sku" class="form-control"
                                        value="<?php echo htmlspecialchars($p['sku']); ?>" required>
                                </div>

                                <!-- CATEGORÍA DINÁMICA -->
                                <div class="mb-4">
                                    <label class="form-label small fw-bold text-primary"><i class="bi bi-diagram-3"></i>
                                        UBICACIÓN EN TIENDA (JERARQUÍA)</label>
                                    <select name="categoria" class="form-select border-primary">
                                        <option value="">Seleccione destino...</option>
                                        <?php foreach ($tree as $m): ?>
                                            <option value="<?= $m['nombre'] ?>" <?= ($p['categoria'] == $m['nombre']) ? 'selected' : '' ?> class="fw-bold bg-light">
                                                <?= $m['nombre'] ?> (PRINCIPAL)
                                            </option>
                                            <?php foreach ($m['children'] as $h): ?>
                                                <option value="<?= $h['nombre'] ?>" <?= ($p['categoria'] == $h['nombre']) ? 'selected' : '' ?>>
                                                    &nbsp;&nbsp;&nbsp;↳ SECCIÓN: <?= $h['nombre'] ?>
                                                </option>
                                                <?php foreach ($h['children'] as $n): ?>
                                                    <option value="<?= $n['nombre'] ?>" <?= ($p['categoria'] == $n['nombre']) ? 'selected' : '' ?>>
                                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;↳ SUB: <?= $n['nombre'] ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php endforeach; ?>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label>Descripción</label>
                                    <textarea name="descripcion" class="form-control"
                                        rows="6"><?php echo htmlspecialchars($p['descripcion']); ?></textarea>
                                </div>
                                <div class="col-12 mt-3">
                                    <label class="text-primary"><i class="bi bi-google me-1"></i> META DESCRIPCIÓN
                                        (SEO)</label>
                                    <textarea name="meta_description" class="form-control" rows="2" maxlength="160"
                                        placeholder="Resumen breve para Google (máx. 160 caracteres)"><?php echo htmlspecialchars($p['meta_description'] ?? ''); ?></textarea>
                                    <small class="text-muted">Esto es lo que aparecerá debajo del título en los
                                        resultados de Google.</small>
                                </div>
                                <div class="col-12">
                                    <label>Video (YouTube / TikTok / Instagram)</label>
                                    <textarea name="video_url" class="form-control" rows="6"
                                        placeholder="Pegue aquí el EMBED completo (iframe, blockquote, script)">
                            <?php echo htmlspecialchars($p['video_url']); ?>
                                </textarea>
                                    <small class="text-muted">
                                        Pegue el código embed completo, no solo el enlace.
                                    </small>
                                </div>
                                <!-- CAMPO PDF (NUEVO) -->
                                <div class="col-12 mt-3">
                                    <label class="text-danger fw-bold"><i class="bi bi-file-earmark-pdf-fill me-1"></i>
                                        FICHA TÉCNICA (PDF)</label>

                                    <?php if (!empty($p['pdf_url'])): ?>
                                        <div class="input-group mb-2">
                                            <span class="input-group-text bg-light text-success"><i
                                                    class="bi bi-check-circle-fill"></i> PDF Actual</span>
                                            <input type="text" class="form-control bg-white"
                                                value="<?= basename($p['pdf_url']) ?>" readonly>
                                            <a href="<?= $p['pdf_url'] ?>" target="_blank"
                                                class="btn btn-outline-secondary"><i class="bi bi-eye"></i></a>

                                            <!-- Checkbox oculto para borrar -->
                                            <div class="input-group-text bg-danger bg-opacity-10">
                                                <input class="form-check-input mt-0" type="checkbox" name="borrar_pdf"
                                                    value="1" id="del_pdf">
                                                <label class="form-check-label ms-2 small text-danger fw-bold"
                                                    for="del_pdf">Borrar</label>
                                            </div>
                                        </div>
                                        <input type="hidden" name="pdf_actual" value="<?= $p['pdf_url'] ?>">
                                    <?php endif; ?>

                                    <div class="row g-2">
                                        <div class="col-md-6">
                                            <input type="file" name="pdf_file" class="form-control"
                                                accept="application/pdf">
                                            <small class="text-muted">Subir archivo (.pdf)</small>
                                        </div>
                                        <div class="col-md-6">
                                            <input type="url" name="pdf_link" class="form-control"
                                                placeholder="O enlace externo (Google Drive, etc)">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PRECIOS Y MARKETING -->
                    <div class="card-custom mb-4" style="border-left: 5px solid #FF4500;">
                        <div class="card-body p-4 bg-light">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label>PRECIO DE VENTA (S/)</label>
                                    <input type="number" step="0.01" name="precio" id="precio_real"
                                        class="form-control form-control-lg fw-black text-primary"
                                        value="<?php echo $p['precio']; ?>" oninput="calcularOferta()">
                                </div>
                                <div class="col-md-4">
                                    <label>INFLAR PRECIO (%)</label>
                                    <div class="input-group">
                                        <input type="number" name="descuento_porcentaje" id="porcentaje"
                                            class="form-control form-control-lg fw-bold"
                                            value="<?php echo $porcentaje_actual; ?>" min="0" max="99"
                                            oninput="calcularOferta()">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label>PRECIO LISTA (TACHADO)</label>
                                    <input type="text" id="precio_tachado"
                                        class="form-control form-control-lg text-muted text-decoration-line-through"
                                        readonly value="<?php echo $p['precio_lista']; ?>">
                                    <small class="text-muted d-block mt-1">Automático</small>
                                </div>

                                <div class="col-md-6">
                                    <label>Stock</label>
                                    <input type="number" name="stock" class="form-control"
                                        value="<?php echo $p['stock_actual']; ?>">
                                </div>
                                <div class="col-md-6 pt-4">
                                    <div class="d-flex gap-2">
                                        <input type="radio" class="btn-check" name="etiqueta" id="tag_none" value=""
                                            <?php echo $p['etiqueta'] == '' ? 'checked' : ''; ?>>
                                        <label class="btn btn-outline-secondary btn-sm" for="tag_none">Nada</label>
                                        <input type="radio" class="btn-check" name="etiqueta" id="tag_new" value="NUEVO"
                                            <?php echo $p['etiqueta'] == 'NUEVO' ? 'checked' : ''; ?>>
                                        <label class="btn btn-outline-success btn-sm" for="tag_new">NUEVO</label>
                                        <input type="radio" class="btn-check" name="etiqueta" id="tag_hot"
                                            value="OFERTA" <?php echo $p['etiqueta'] == 'OFERTA' ? 'checked' : ''; ?>>
                                        <label class="btn btn-outline-danger btn-sm" for="tag_hot">OFERTA</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- COLUMNA DERECHA: IMÁGENES UNIFICADAS -->
                <div class="col-lg-4">
                    <div class="card-custom">
                        <div class="card-header-custom bg-dark text-white">
                            <i class="bi bi-images me-2"></i> GALERÍA MULTIMEDIA
                        </div>
                        <div class="card-body p-4">

                            <div class="alert alert-secondary small mb-3 border-0 bg-light rounded-3">
                                <div class="d-flex text-dark">
                                    <i class="bi bi-info-circle-fill me-2 text-primary"></i>
                                    <span>Selecciona la <i class="bi bi-star-fill text-warning"></i> <b>estrella</b>
                                        para elegir la imagen principal (portada).</span>
                                </div>
                            </div>

                            <!-- CONTENEDOR GRID DE IMÁGENES -->
                            <div id="unified-gallery" class="gallery-grid">
                                <?php
                                // Unificar Galería + Portada para mostrarlas todas juntas
                                $currentImages = json_decode($p['galeria'] ?? '[]', true);

                                // Asegurar que la imagen_url también esté (por si acaso no está en el json de galería)
                                if (!empty($p['imagen_url']) && $p['imagen_url'] != '/assets/img/no-photo.png') {
                                    if (!in_array($p['imagen_url'], $currentImages)) {
                                        array_unshift($currentImages, $p['imagen_url']);
                                    }
                                }   
                                // Limpiar
                                $currentImages = array_values(array_unique($currentImages));

                                foreach ($currentImages as $idx => $img):
                                    $isCover = ($img === $p['imagen_url']);
                                    ?>
                                    <div class="gallery-item existing-item">
                                        <img src="<?= $img ?>">

                                        <!-- Selector Portada -->
                                        <label class="cover-selector <?= $isCover ? 'active' : '' ?>"
                                            title="Marcar como Portada">
                                            <input type="radio" name="cover_selection" value="existing:<?= $img ?>"
                                                <?= $isCover ? 'checked' : '' ?> class="d-none"
                                                onchange="updateCoverUI(this)">
                                            <i class="bi bi-star-fill"></i>
                                        </label>

                                        <!-- Eliminar -->
                                        <button type="button" class="btn-remove-img" onclick="removeExisting(this)">
                                            <i class="bi bi-trash"></i>
                                        </button>

                                        <!-- Input oculto para 'mantener' esta imagen al guardar -->
                                        <input type="hidden" name="keep_images[]" value="<?= $img ?>">
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- ÁREA DE SUBIDA -->
                            <div class="upload-area border-2 border-dashed border-secondary rounded-4 p-4 text-center mt-3 cursor-pointer"
                                onclick="document.getElementById('fileInput').click()">
                                <i class="bi bi-cloud-arrow-up-fill display-4 text-primary opacity-50"></i>
                                <h6 class="fw-bold mt-2 text-dark">Click para subir imágenes</h6>
                                <small class="text-muted d-block">Puedes seleccionar múltiples archivos a la
                                    vez.</small>
                                <input type="file" id="fileInput" name="galeria_files[]" class="d-none" multiple
                                    accept="image/*" onchange="handleFileSelect(this)">
                            </div>

                            <hr class="my-4 opacity-10">

                            <!-- TEXTO ALT (SEO) -->
                            <div class="text-start">
                                <label class="text-secondary fw-bold small mb-1">TEXTO ALT (SEO)</label>
                                <input type="text" name="imagen_alt" class="form-control form-control-sm border-2"
                                    value="<?= htmlspecialchars($p['imagen_alt'] ?? '') ?>"
                                    placeholder="Descripción breve de la imagen para Google...">
                            </div>

                            <button type="submit" class="btn-save shadow-lg mt-4">
                                <i class="bi bi-check-circle-fill me-2"></i> GUARDAR PRODUCTO
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- LÓGICA DE CATEGORÍAS DEPENDIENTES -->
    <script>
        function calcularOferta() {
            let precio = parseFloat(document.getElementById('precio_real').value) || 0;
            let porc = parseInt(document.getElementById('porcentaje').value) || 0;
            let tachado = document.getElementById('precio_tachado');

            if (porc > 0 && precio > 0) {
                let inflado = precio / (1 - (porc / 100));
                tachado.value = "S/ " + inflado.toFixed(2);
            } else {
                tachado.value = "";
            }
        }

        function generarSlug() {
            const nombre = document.getElementById('input_nombre') ? document.getElementById('input_nombre').value : '';
            const slugField = document.getElementById('input_slug');

            if (slugField && nombre) {
                let slug = nombre.toLowerCase()
                    .trim()
                    .replace(/[^\w\s-]/g, '')
                    .replace(/[\s_-]+/g, '-')
                    .replace(/^-+|-+$/g, '');
                slugField.value = slug;
            }
        }

    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>

    <!-- LOGICA DE IMAGENES AVANZADA -->
    <script>
        // DataTransfer para manipular los archivos del input
        const dt = new DataTransfer();

        function handleFileSelect(input) {
            const files = input.files;
            const grid = document.getElementById('unified-gallery');

            // Si es la primera vez o se agregan más, los agregamos al DataTransfer
            // Nota: Esta lógica permite agregar incrementalmente si el usuario vuelve a dar click
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                // Evitar duplicados por nombre y tamaño (básico)
                let exists = false;
                for (let j = 0; j < dt.items.length; j++) {
                    if (dt.items[j].getAsFile().name === file.name && dt.items[j].getAsFile().size === file.size) {
                        exists = true;
                        break;
                    }
                }
                if (!exists) dt.items.add(file);
            }

            // Actualizar el input con todos los archivos acumulados
            input.files = dt.files;

            // Re-renderizar SOLO las nuevas (Limpiamos las pendientes previas para evitar duplicados visuales y redibujamos todo lo nuevo)
            document.querySelectorAll('.new-pending-item').forEach(e => e.remove());

            // Dibujar todo lo que hay en dt.files
            Array.from(dt.files).forEach((file, index) => {
                const reader = new FileReader();
                reader.readAsDataURL(file);
                reader.onload = function (e) {
                    const div = document.createElement('div');
                    div.className = 'gallery-item new-pending-item animate__animated animate__fadeIn';
                    div.innerHTML = `
                        <img src="${e.target.result}">
                        
                        <label class="cover-selector" title="Usar como Portada">
                            <input type="radio" name="cover_selection" value="new_${index}" class="d-none" onchange="updateCoverUI(this)">
                            <i class="bi bi-star-fill"></i>
                        </label>
                        
                        <button type="button" class="btn-remove-img" onclick="removeNewFile('${file.name}', this)">
                            <i class="bi bi-trash"></i>
                        </button>

                        <div class="position-absolute bottom-0 start-0 w-100 p-1 text-center bg-success text-white" style="font-size: 0.65rem; font-weight:bold;">
                            NUEVA
                        </div>
                    `;
                    grid.appendChild(div);

                    // Auto-seleccionar si es el único
                    if (dt.files.length === 1 && !document.querySelector('input[name="cover_selection"]:checked')) {
                        div.querySelector('input').checked = true;
                        updateCoverUI(div.querySelector('input'));
                    }
                }
            });
        }

        function removeNewFile(fileName, btn) {
            // Eliminar del DataTransfer
            for (let i = 0; i < dt.items.length; i++) {
                if (fileName === dt.items[i].getAsFile().name) {
                    dt.items.remove(i);
                    break;
                }
            }
            // Actualizar input
            document.getElementById('fileInput').files = dt.files;
            // Eliminar elemento visual
            btn.closest('.gallery-item').remove();

            // Re-calcular índices de los radio buttons para que coincidan con el nuevo array
            // Esto es crucial porque el backend espera new_0, new_1 alineado con el array de archivos
            // Simplemente redibujamos todo o actualizamos los values. 
            // Para simplificar y evitar errores de índice: Forzamos redibujado simulando evento? 
            // No, mejor actualizamos los values in-place
            const newItems = document.querySelectorAll('.new-pending-item input[type="radio"]');
            newItems.forEach((radio, idx) => {
                const checked = radio.checked;
                radio.value = `new_${idx}`;
                // Mantener selección si era el que quedó
            });
        }

        function updateCoverUI(radio) {
            document.querySelectorAll('.cover-selector').forEach(el => el.classList.remove('active'));
            radio.parentElement.classList.add('active');
        }

        function removeExisting(btn) {
            Swal.fire({
                title: '¿Eliminar imagen?',
                text: "Esta imagen se eliminará del producto al guardar.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    btn.parentElement.remove();
                    /* Swal.fire(
                        'Eliminada',
                        'La imagen ha sido quitada de la lista.',
                        'success'
                    ) */
                }
            })
        }
        document.addEventListener('DOMContentLoaded', function() {
    var el = document.getElementById('unified-gallery');
    
    if(el) {
        var sortable = Sortable.create(el, {
            animation: 150, // Suavidad de la animación (ms)
            ghostClass: 'sortable-ghost', // Clase para el elemento fantasma (el hueco)
            chosenClass: 'sortable-chosen', // Clase para el elemento seleccionado
            dragClass: 'sortable-drag', // Clase mientras se arrastra
            
            // Esto asegura que cuando arrastres, el input oculto también se mueva
            // y PHP reciba el nuevo orden automáticamente al guardar.
            onEnd: function (evt) {
                console.log('Nuevo orden establecido');
            }
        });
    }
});
    </script>

</body>

</html>