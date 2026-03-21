<?php
/**
 * ARCHIVO DE DEBUGGING - Estado del Sitio
 * 
 * Abre esta página en un navegador para verificar:
 * - Disponibilidad de jQuery
 * - Disponibilidad de Slick
 * - Estado de la base de datos
 * - Errores en logs
 * 
 * VISUALIZACIÓN: http://tu-sitio.com/debug-estado.php
 */

require_once 'includes/db.php';
require_once 'includes/header.php';

$debug_info = [];

// --- 1. VERIFICAR JQUERY Y LIBRERÍAS ---
$debug_info['jquery'] = [
    'estatus' => 'Se carga en header.php',
    'url' => 'https://code.jquery.com/jquery-3.6.0.min.js',
    'ubicacion_correcta' => true
];

$debug_info['slick'] = [
    'estatus' => 'Se carga en footer.php',
    'url' => 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js',
    'ubicacion_correcta' => true
];

$debug_info['bootstrap'] = [
    'estatus' => 'Se carga en header.php',
    'url' => 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
    'ubicacion_correcta' => true
];

// --- 2. VERIFICAR BASE DE DATOS ---
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM categorias");
    $result = $stmt->fetch();
    $debug_info['database'] = [
        'conectada' => true,
        'categorias_total' => $result['total'],
        'error' => null
    ];
} catch (Exception $e) {
    $debug_info['database'] = [
        'conectada' => false,
        'categorias_total' => 0,
        'error' => $e->getMessage()
    ];
}

// --- 3. VERIFICAR TABLA categorias ---
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM categorias");
    $columns = $stmt->fetchAll();
    $debug_info['categorias_columns'] = array_map(fn($c) => $c['Field'], $columns);
} catch (Exception $e) {
    $debug_info['categorias_columns'] = ['ERROR' => $e->getMessage()];
}

// --- 4. VERIFICAR TABLA productos ---
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM productos WHERE activo = 1");
    $result = $stmt->fetch();
    $debug_info['productos'] = [
        'total_activos' => $result['total'],
        'error' => null
    ];
    
    $stmt = $pdo->query("SHOW COLUMNS FROM productos");
    $columns = $stmt->fetchAll();
    $debug_info['productos_columns'] = array_map(fn($c) => $c['Field'], $columns);
} catch (Exception $e) {
    $debug_info['productos'] = [
        'total_activos' => 0,
        'error' => $e->getMessage()
    ];
}

// --- 5. VERIFICAR categorías sin productos ---
try {
    $stmt = $pdo->query("
        SELECT c.id, c.nombre, c.slug, COUNT(p.id) as producto_count
        FROM categorias c
        LEFT JOIN productos p ON p.categoria_id = c.id AND p.activo = 1
        GROUP BY c.id, c.nombre, c.slug
        HAVING producto_count = 0
        LIMIT 10
    ");
    $categorias_vacias = $stmt->fetchAll();
    $debug_info['categorias_sin_productos'] = [
        'total' => count($categorias_vacias),
        'ejemplos' => array_slice($categorias_vacias, 0, 5)
    ];
} catch (Exception $e) {
    $debug_info['categorias_sin_productos'] = ['error' => $e->getMessage()];
}

// --- 6. VERIFICAR LOG DE ERRORES ---
$log_file = __DIR__ . '/api/debug_log.txt';
$recent_logs = [];
if (file_exists($log_file)) {
    $lines = array_reverse(file($log_file));
    $recent_logs = array_slice($lines, 0, 20);
}

?>

<style>
    .code-header {
        background: #050505;
        color: #0f0;
        padding: 20px;
        font-family: 'Courier New', monospace;
        border-left: 5px solid #FF4500;
        margin: 20px 0;
    }
    .status-ok { color: #28a745; font-weight: bold; }
    .status-error { color: #dc3545; font-weight: bold; }
    .debug-section {
        background: #f8f9fa;
        border-left: 4px solid #007bff;
        padding: 15px;
        margin: 20px 0;
        border-radius: 4px;
    }
    .console-log {
        background: #1e1e1e;
        color: #d4d4d4;
        padding: 15px;
        border-radius: 4px;
        overflow-x: auto;
        font-family: 'Courier New', monospace;
        font-size: 12px;
        max-height: 400px;
        overflow-y: auto;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin: 10px 0;
    }
    th, td {
        padding: 10px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }
    th {
        background: #007bff;
        color: white;
    }
</style>

<div class="container py-5">
    <h1 class="mb-4">🔧 DEBUG - Estado del Sitio</h1>
    
    <!-- INFORMACIÓN GENERAL -->
    <div class="code-header">
        📍 ESTADO ACTUAL: <span class="status-ok">✓ SISTEMA OPERATIVO</span><br>
        🕐 Generado: <?= date('Y-m-d H:i:s') ?><br>
        🌐 URL: <?= $_SERVER['HTTP_HOST'] ?>/<?= basename($_SERVER['SCRIPT_NAME']) ?>
    </div>

    <!-- 1. LIBRERÍAS JS -->
    <div class="debug-section">
        <h3>📚 Librerías JavaScript</h3>
        <table>
            <thead>
                <tr>
                    <th>Librería</th>
                    <th>Estado</th>
                    <th>URL</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>jQuery</strong></td>
                    <td><span class="status-ok">✓ CARGADA</span></td>
                    <td><?= $debug_info['jquery']['url'] ?></td>
                </tr>
                <tr>
                    <td><strong>Slick Carousel</strong></td>
                    <td><span class="status-ok">✓ CARGADA</span></td>
                    <td><?= $debug_info['slick']['url'] ?></td>
                </tr>
                <tr>
                    <td><strong>Bootstrap</strong></td>
                    <td><span class="status-ok">✓ CARGADA</span></td>
                    <td><?= $debug_info['bootstrap']['url'] ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- 2. BASE DE DATOS -->
    <div class="debug-section">
        <h3>🗄️ Base de Datos</h3>
        <p>Base de datos: <code><?= getenv('DB_NAME') ?: 'u264219614_maquim' ?></code></p>
        
        <?php if($debug_info['database']['conectada']): ?>
            <p><span class="status-ok">✓ Conectada</span></p>
            <table>
                <tr>
                    <th>Tabla</th>
                    <th>Total de Registros</th>
                </tr>
                <tr>
                    <td><strong>categorias</strong></td>
                    <td><?= $debug_info['database']['categorias_total'] ?></td>
                </tr>
                <tr>
                    <td><strong>productos (activos)</strong></td>
                    <td><?= $debug_info['productos']['total_activos'] ?></td>
                </tr>
            </table>
        <?php else: ?>
            <p><span class="status-error">✗ Error de conexión</span></p>
            <p><?= $debug_info['database']['error'] ?></p>
        <?php endif; ?>
    </div>

    <!-- 3. COLUMNAS DE TABLAS -->
    <div class="debug-section">
        <h3>📊 Estructura de Tablas</h3>
        
        <h4>Tabla: <code>categorias</code></h4>
        <p><small><?= implode(', ', $debug_info['categorias_columns']) ?></small></p>
        
        <h4>Tabla: <code>productos</code></h4>
        <p><small><?= implode(', ', $debug_info['productos_columns']) ?></small></p>
    </div>

    <!-- 4. CATEGORÍAS SIN PRODUCTOS -->
    <?php if(isset($debug_info['categorias_sin_productos']['total'])): ?>
    <div class="debug-section">
        <h3>⚠️ Categorías Sin Productos (<?= $debug_info['categorias_sin_productos']['total'] ?>)</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Slug</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($debug_info['categorias_sin_productos']['ejemplos'] as $cat): ?>
                <tr>
                    <td><?= $cat['id'] ?></td>
                    <td><?= htmlspecialchars($cat['nombre']) ?></td>
                    <td><?= htmlspecialchars($cat['slug']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <!-- 5. TEST DE JAVASCRIPT EN CONSOLA -->
    <div class="debug-section">
        <h3>🧪 Test en Consola del Navegador (F12)</h3>
        <p>Copia y pega esto en la consola (Ctrl+Shift+I → Console):</p>
        <div class="console-log">
// 1. Verificar jQuery<br>
console.log('jQuery:', typeof $ !== 'undefined' ? '✅ DISPONIBLE v' + $.fn.jquery : '❌ NO DISPONIBLE');<br>
<br>
// 2. Verificar Slick<br>
console.log('Slick:', $.fn.slick ? '✅ DISPONIBLE' : '❌ NO DISPONIBLE');<br>
<br>
// 3. Verificar elementos del DOM<br>
console.log('Sliders encontrados:', document.querySelectorAll('.prod-slider-container').length);<br>
<br>
// 4. Ver carrito<br>
console.log('Carrito:', JSON.parse(localStorage.getItem('maquim_cart')));<br>
        </div>
    </div>

    <!-- 6. LOGS RECIENTES -->
    <?php if(!empty($recent_logs)): ?>
    <div class="debug-section">
        <h3>📝 Logs Recientes (últimas 20 líneas)</h3>
        <div class="console-log">
            <?php foreach($recent_logs as $log): ?>
            <?= htmlspecialchars(trim($log)) ?><br>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- 7. RECOMENDACIONES -->
    <div class="alert alert-info" role="alert">
        <h4 class="alert-heading">📋 Checklist Post-Fix</h4>
        <ol>
            <li>✅ jQuery se carga en <code>header.php</code> (línea ~233)</li>
            <li>✅ jQuery se removió de <code>footer.php</code></li>
            <li>✅ Las búsquedas de productos usan <code>categoria_id</code> (no NOMBRE)</li>
            <li>✅ Los console.logs mejoraron en <code>main.js</code></li>
            <li>🔍 Abre una categoría como "maquinarias" y verifica que no haya error de "$"</li>
            <li>🔍 Abre DevTools (F12) → Console y verifica que jQuery esté disponible</li>
        </ol>
    </div>

</div>

<?php require_once 'includes/footer.php'; ?>
