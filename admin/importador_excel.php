<?php
require_once 'check_auth.php';
require_once '../includes/db.php';
require_once '../includes/SimpleXLSX.php'; // La librería que descargamos

$msg = "";
$log = "";

// AUMENTAR LÍMITES DE MEMORIA Y TIEMPO (Para archivos grandes)
ini_set('memory_limit', '512M');
set_time_limit(300); // 5 minutos máximo

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['excel'])) {
    
    if ($xlsx = SimpleXLSX::parse($_FILES['excel']['tmp_name'])) {
        
        $total_procesados = 0;
        $hojas_leidas = 0;

        // SQL PREPARADO
        $sql = "INSERT INTO productos (sku, slug, nombre, precio, imagen_url, descripcion, categoria, stock_actual, activo) 
                VALUES (:sku, :slug, :nombre, :precio, :img, :desc, :cat, 10, 1)
                ON DUPLICATE KEY UPDATE 
                nombre = VALUES(nombre), 
                precio = VALUES(precio),
                descripcion = VALUES(descripcion),
                imagen_url = VALUES(imagen_url),
                categoria = VALUES(categoria)";
        
        $stmt = $pdo->prepare($sql);

        // RECORRER TODAS LAS PESTAÑAS (HOJAS)
        foreach ($xlsx->sheetNames() as $sheetIndex => $sheetName) {
            $hojas_leidas++;
            $log .= "<strong>Leyendo Hoja: $sheetName</strong><br>";
            
            // Leer filas de esta hoja
            foreach ($xlsx->rows($sheetIndex) as $r => $row) {
                // Saltar encabezados (Fila 0)
                if ($r === 0) continue; 
                
                // Mapeo de Columnas (Basado en tu Excel):
                // 0:ID | 1:SLUG | 2:NOMBRE | 3:PRECIO | 4:IMG | 5:DESC | 6:DISP | 7:LARGA | 8:SPECS | 9:CATEGORIA
                
                // Validar que tenga datos mínimos
                if (empty($row[0]) || empty($row[2])) continue;

                $sku = trim($row[0]);
                $nombre = trim($row[2]);
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $nombre)));
                $precio = floatval(preg_replace('/[^0-9.]/', '', $row[3])); // Limpiar precio
                $categoria = !empty($row[9]) ? trim($row[9]) : $sheetName; // Si no tiene categoría, usa el nombre de la hoja
                
                // Construir descripción completa
                $desc = ($row[5] ?? '') . "<br><br>" . ($row[7] ?? '') . "<br><b>Especificaciones:</b><br>" . ($row[8] ?? '');

                // --- LÓGICA DE IMAGEN (DESCARGA AUTOMÁTICA) ---
                $imgFinal = '/assets/img/no-photo.png';
                $urlExterna = $row[4] ?? '';

                if (!empty($urlExterna) && filter_var($urlExterna, FILTER_VALIDATE_URL)) {
                    $ext = pathinfo($urlExterna, PATHINFO_EXTENSION);
                    if(!$ext || strlen($ext) > 4) $ext = 'jpg';
                    
                    $nombreArchivo = "prod_" . preg_replace('/[^A-Za-z0-9]/', '', $sku) . "." . $ext;
                    $rutaLocal = "../assets/img/" . $nombreArchivo;
                    
                    // Descargar si no existe
                    if (!file_exists($rutaLocal)) {
                        $contenido = @file_get_contents($urlExterna);
                        if ($contenido) file_put_contents($rutaLocal, $contenido);
                    }
                    
                    if (file_exists($rutaLocal)) {
                        $imgFinal = "/assets/img/" . $nombreArchivo;
                    }
                }

                // EJECUTAR BASE DE DATOS
                try {
                    $stmt->execute([
                        ':sku' => $sku,
                        ':slug' => $slug,
                        ':nombre' => $nombre,
                        ':precio' => $precio,
                        ':img' => $imgFinal,
                        ':desc' => $desc,
                        ':cat' => $categoria
                    ]);
                    $total_procesados++;
                } catch (Exception $e) {
                    $log .= "<small class='text-danger'>Error en SKU $sku: " . $e->getMessage() . "</small><br>";
                }
            }
        }
        
        $msg = "<div class='alert alert-success'>✅ Procesado exitoso. Se leyeron <b>$hojas_leidas hojas</b> y se actualizaron <b>$total_procesados productos</b>.</div>";
        
    } else {
        $msg = "<div class='alert alert-danger'>" . SimpleXLSX::parseError() . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Importador Maestro | MaquimAdmin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; font-family: sans-serif; padding-bottom:50px; }
        .upload-box { background: white; padding: 40px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); text-align: center; border: 2px dashed #ccc; transition:0.3s; }
        .upload-box:hover { border-color: #FF4500; background: #fffdfa; }
        .log-box { max-height: 200px; overflow-y: auto; background: #222; color: #0f0; padding: 15px; font-family: monospace; font-size: 0.8rem; border-radius: 8px; margin-top: 20px; }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>📂 Importador Maestro de Excel</h2>
        <a href="dashboard.php" class="btn btn-dark">Volver al Dashboard</a>
    </div>

    <?php echo $msg; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="upload-box">
            <img src="https://upload.wikimedia.org/wikipedia/commons/3/34/Microsoft_Office_Excel_%282019%E2%80%93present%29.svg" width="60" class="mb-3">
            <h4>Arrastra tu archivo Excel (.xlsx) aquí</h4>
            <p class="text-muted">El sistema leerá todas las pestañas y descargará las imágenes automáticamente.</p>
            
            <input type="file" name="excel" class="form-control form-control-lg mt-3" accept=".xlsx" required>
            
            <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold mt-4" style="background:#FF4500; border:none;">
                INICIAR IMPORTACIÓN MASIVA
            </button>
        </div>
    </form>

    <?php if($log): ?>
        <h5 class="mt-4">Registro de Proceso:</h5>
        <div class="log-box">
            <?php echo $log; ?>
        </div>
    <?php endif; ?>

</div>

</body>
</html>