<?php
/**
 * DEBUG - Productos con Etiqueta OFERTA
 * 
 * Verifica si los productos con etiqueta = 'OFERTA' tienen categoria_id asignado
 * y si aparecen correctamente en sus categorías.
 */

require_once 'includes/db.php';
require_once 'includes/header.php';

echo '<div class="container py-5">';
echo '<h1 class="mb-4">🔍 DEBUG - Productos OFERTA y Categorías</h1>';

// --- 1. PRODUCTOS CON ETIQUETA OFERTA ---
$stmt = $pdo->query("
    SELECT id, nombre, categoria_id, categoria, etiqueta 
    FROM productos 
    WHERE etiqueta = 'OFERTA' AND activo = 1
    LIMIT 20
");
$ofertasConProblema = $stmt->fetchAll();

echo '<div class="alert alert-info" role="alert">';
echo '<h4>📊 Productos con etiqueta OFERTA: ' . count($ofertasConProblema) . '</h4>';

if (count($ofertasConProblema) > 0) {
    echo '<table class="table table-sm">';
    echo '<thead><tr><th>ID</th><th>Nombre</th><th>categoria_id</th><th>categoria (texto)</th><th>etiqueta</th><th>Estado</th></tr></thead>';
    echo '<tbody>';
    
    foreach ($ofertasConProblema as $p) {
        $estado = $p['categoria_id'] ? '✅ Tiene categoria_id' : '❌ categoria_id NULO';
        $rowClass = $p['categoria_id'] ? '' : 'table-danger';
        
        echo "<tr class=\"$rowClass\">";
        echo '<td>' . $p['id'] . '</td>';
        echo '<td>' . htmlspecialchars($p['nombre']) . '</td>';
        echo '<td><strong>' . ($p['categoria_id'] ?: 'NULL') . '</strong></td>';
        echo '<td>' . htmlspecialchars($p['categoria'] ?: '(vacío)') . '</td>';
        echo '<td>' . $p['etiqueta'] . '</td>';
        echo '<td>' . $estado . '</td>';
        echo '</tr>';
    }
    echo '</tbody></table>';
} else {
    echo '<p class="text-muted">No hay productos con etiqueta OFERTA.</p>';
}

echo '</div>';

// --- 2. VERIFICAR SI LOS PRODUCTOS OFERTA APARECEN EN SUS CATEGORÍAS ---
echo '<div class="alert alert-warning" role="alert">';
echo '<h4>🔬 Verificación: ¿Aparecen en sus categorías?</h4>';

if (count($ofertasConProblema) > 0) {
    foreach (array_slice($ofertasConProblema, 0, 5) as $p) {
        if ($p['categoria_id']) {
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as total 
                FROM productos 
                WHERE categoria_id = ? AND id = ? AND activo = 1
            ");
            $stmt->execute([$p['categoria_id'], $p['id']]);
            $result = $stmt->fetch();
            
            $aparece = $result['total'] > 0 ? '✅ SÍ' : '❌ NO';
            echo "<p>Producto: <strong>{$p['nombre']}</strong> (ID: {$p['id']}) → Categoría ID: {$p['categoria_id']} → $aparece aparece en su categoría</p>";
        }
    }
} else {
    echo '<p class="text-muted">No hay productos OFERTA para verificar.</p>';
}

echo '</div>';

// --- 3. SOLUCIONES ---
echo '<div class="alert alert-success" role="alert">';
echo '<h4>✅ Soluciones Recomendadas</h4>';
echo '<ol>';
echo '<li><strong>Si categoria_id es NULL:</strong> El producto OFERTA no tiene categoría asignada.';
echo '<ul>';
echo '<li>Solución: Asignar un `categoria_id` válido al crear/editar un producto OFERTA</li>';
echo '<li>Query SQL para fijar: <code>UPDATE productos SET categoria_id = 5 WHERE etiqueta = "OFERTA" AND categoria_id IS NULL;</code></li>';
echo '</ul>';
echo '</li>';
echo '<li><strong>Si categoria_id existe pero no aparece:</strong> Verificar que `activo = 1`</li>';
echo '<li><strong>Para que aparezca en AMBAS secciones:</strong> Solo asignar `etiqueta = "OFERTA"` sin cambiar `categoria_id`</li>';
echo '</ol>';
echo '</div>';

// --- 4. REPORTE RESUMEN ---
echo '<div class="alert alert-secondary" role="alert">';
echo '<h4>📋 Resumen</h4>';

$stmtOfertas = $pdo->query("SELECT COUNT(*) as total FROM productos WHERE etiqueta = 'OFERTA' AND activo = 1");
$totalOfertas = $stmtOfertas->fetch()['total'];

$stmtOfertasSinCat = $pdo->query("SELECT COUNT(*) as total FROM productos WHERE etiqueta = 'OFERTA' AND activo = 1 AND (categoria_id IS NULL OR categoria_id = 0)");
$totalSinCat = $stmtOfertasSinCat->fetch()['total'];

$stmtOfertasConCat = $pdo->query("SELECT COUNT(*) as total FROM productos WHERE etiqueta = 'OFERTA' AND activo = 1 AND categoria_id > 0");
$totalConCat = $stmtOfertasConCat->fetch()['total'];

echo "<p><strong>Total Productos OFERTA Activos:</strong> $totalOfertas</p>";
echo "<p><strong>Con categoria_id asignado:</strong> $totalConCat ✅</p>";
echo "<p><strong>Sin categoria_id (problema):</strong> $totalSinCat ❌</p>";

echo '</div>';

echo '</div>';

require_once 'includes/footer.php';
