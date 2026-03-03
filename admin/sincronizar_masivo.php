<?php
require_once 'check_auth.php';
require_once '../includes/db.php';

// Potencia máxima para el script
ini_set('memory_limit', '1024M');
set_time_limit(1200);

$msg = "";
$log_exito = [];
$log_fallo = [];

// Ruta base de imágenes
$ruta_base = '../assets/img/banco_drive/'; 
// Auto-detectar si hay una carpeta contenedora
$dirs = glob($ruta_base . '*', GLOB_ONLYDIR);
if(count($dirs) == 1) $ruta_base = $dirs[0] . '/';

// 1. CARGAR PRODUCTOS EN MEMORIA (Para comparar rápido)
$stmt = $pdo->query("SELECT id, slug, sku, nombre FROM productos");
$productos_db = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Función para limpiar cadenas (quitar guiones, espacios, tildes)
function limpiar_str($str) {
    $str = strtolower($str);
    $str = str_replace(['-', '_', ' ', '/', '.'], '', $str);
    return $str;
}

// Mapa de búsqueda optimizado
$mapa_productos = [];
foreach ($productos_db as $p) {
    $item = [
        'id' => $p['id'],
        'sku_clean' => limpiar_str($p['sku']),
        'nombre_clean' => limpiar_str($p['nombre']),
        'slug_clean' => limpiar_str($p['slug']),
        'nombre_real' => $p['nombre']
    ];
    $mapa_productos[] = $item;
}

// Función Recursiva de Búsqueda Inteligente
function escanear_inteligente($dir, $mapa_productos, $pdo, &$log_exito, &$log_fallo) {
    $items = scandir($dir);

    foreach ($items as $item) {
        if ($item == '.' || $item == '..') continue;
        $ruta_completa = $dir . '/' . $item;

        if (is_dir($ruta_completa)) {
            $nombre_carpeta = $item;
            $carpeta_clean = limpiar_str($nombre_carpeta);
            
            $producto_encontrado = null;
            $metodo_match = "";

            // --- INTELIGENCIA ARTIFICIAL (CASERA) ---
            
            foreach ($mapa_productos as $prod) {
                // 1. Coincidencia por SKU (La más fuerte)
                // Ej: Carpeta "taladro-sdh600ka" contiene SKU "sdh600ka"
                if (strpos($carpeta_clean, $prod['sku_clean']) !== false && strlen($prod['sku_clean']) > 3) {
                    $producto_encontrado = $prod;
                    $metodo_match = "Por SKU";
                    break;
                }

                // 2. Coincidencia por Slug Exacto (Limpio)
                if ($carpeta_clean == $prod['slug_clean']) {
                    $producto_encontrado = $prod;
                    $metodo_match = "Por Slug";
                    break;
                }

                // 3. Coincidencia Parcial (El nombre del producto está DENTRO del nombre de la carpeta)
                // Ej: Prod "NT 20/1" (nt201) está dentro de Carpeta "aspiradora-nt-20-1-classic" (aspiradorant201classic)
                if (strpos($carpeta_clean, $prod['nombre_clean']) !== false) {
                    $producto_encontrado = $prod;
                    $metodo_match = "Nombre incluido";
                    break;
                }
                
                // 4. Coincidencia Inversa (El nombre de la carpeta está DENTRO del producto)
                // Ej: Carpeta "puzzi-10-1" está dentro de Prod "Lavatapiz Puzzi 10/1"
                if (strpos($prod['nombre_clean'], $carpeta_clean) !== false && strlen($carpeta_clean) > 4) {
                    $producto_encontrado = $prod;
                    $metodo_match = "Carpeta en Nombre";
                    break;
                }
            }

            if ($producto_encontrado) {
                procesar_fotos($ruta_completa, $producto_encontrado['id'], $pdo, $log_exito, $nombre_carpeta, $producto_encontrado['nombre_real'], $metodo_match);
            } else {
                // Si no encontramos, seguimos bajando por si es una categoría (ej: "aspiradoras_industriales")
                // No lo marcamos como fallo todavía, solo profundizamos
                $archivos_dentro = glob($ruta_completa . "/*.{jpg,png}", GLOB_BRACE);
                if (count($archivos_dentro) > 0) {
                    // Si tiene fotos y no encontramos dueño, ahí sí es fallo
                    $log_fallo[] = "⚠️ Carpeta <b>$nombre_carpeta</b> tiene fotos pero no coincide con ningún producto.";
                }
                escanear_inteligente($ruta_completa, $mapa_productos, $pdo, $log_exito, $log_fallo);
            }
        }
    }
}

function procesar_fotos($dir, $prod_id, $pdo, &$log_exito, $carpeta, $nombre_prod, $metodo) {
    $fotos = glob($dir . "/*.{jpg,jpeg,png,webp,JPG,PNG,WEBP}", GLOB_BRACE);
    
    if (!empty($fotos)) {
        $rutas_web = [];
        foreach ($fotos as $f) {
            $rutas_web[] = str_replace('../', '/', $f);
        }
        
        $img_main = $rutas_web[0];
        $galeria = json_encode($rutas_web);

        $upd = $pdo->prepare("UPDATE productos SET imagen_url = ?, galeria = ? WHERE id = ?");
        $upd->execute([$img_main, $galeria, $prod_id]);
        
        $log_exito[] = "✅ <b>$carpeta</b> asignada a <b>$nombre_prod</b> ($metodo) - " . count($rutas_web) . " fotos.";
    }
}

// --- EJECUCIÓN ---
if (isset($_POST['sync'])) {
    if (!file_exists($ruta_base)) {
        $msg = "<div class='alert alert-danger'>❌ No encuentro la carpeta base.</div>";
    } else {
        escanear_inteligente($ruta_base, $mapa_productos, $pdo, $log_exito, $log_fallo);
        
        $count = count($log_exito);
        $msg = "<div class='alert alert-success'><h4>¡Proceso Inteligente Terminado!</h4>Se enlazaron <b>$count</b> productos automáticamente.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Sincronizador IA | MaquimAdmin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>body { background: #1a1a1a; color: #fff; font-family: sans-serif; }</style>
</head>
<body class="p-5">

<div class="container">
    <div class="d-flex justify-content-between mb-4 align-items-center">
        <h2>🧠 Sincronizador Inteligente (Fuzzy Match)</h2>
        <a href="dashboard.php" class="btn btn-outline-light">Volver</a>
    </div>

    <?php echo $msg; ?>

    <div class="row">
        <!-- EXITO -->
        <div class="col-md-7">
            <div class="card bg-dark border-success mb-4">
                <div class="card-header bg-success text-white fw-bold d-flex justify-content-between">
                    <span>COINCIDENCIAS ENCONTRADAS</span>
                    <span class="badge bg-white text-success"><?php echo count($log_exito); ?></span>
                </div>
                <div class="card-body" style="max-height:500px; overflow:auto; font-size:0.85rem;">
                    <?php 
                    if(empty($log_exito)) echo "<p class='text-muted'>Esperando ejecución...</p>";
                    foreach($log_exito as $l) echo "$l<br>"; 
                    ?>
                </div>
            </div>
        </div>
        
        <!-- FALLOS -->
        <div class="col-md-5">
            <div class="card bg-dark border-warning mb-4">
                <div class="card-header bg-warning text-dark fw-bold">CARPETAS HUÉRFANAS (Con fotos)</div>
                <div class="card-body text-warning" style="max-height:500px; overflow:auto; font-size:0.85rem;">
                    <?php 
                    if(empty($log_fallo)) echo "<p class='text-muted'>Nada por aquí.</p>";
                    foreach(array_unique($log_fallo) as $f) echo "$f<br>"; 
                    ?>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" class="text-center mt-4">
        <p class="text-muted">Este script ignorará guiones, mayúsculas y palabras extra para encontrar la mejor coincidencia.</p>
        <button type="submit" name="sync" class="btn btn-primary btn-lg fw-bold px-5 py-3 shadow border-0" style="background:#FF4500;">
            🚀 INICIAR VINCULACIÓN INTELIGENTE
        </button>
    </form>
</div>

</body>
</html>