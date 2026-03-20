<?php
header("Content-Type: application/xml; charset=utf-8");
require_once 'includes/db.php';

// Detectar el dominio automáticamente para evitar errores
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
$basePath = ($scriptDir === '/' || $scriptDir === '.') ? '' : rtrim($scriptDir, '/');
$baseUrl = $protocol . "://" . $_SERVER['HTTP_HOST'] . $basePath;

echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

// 1. PÁGINA DE INICIO
echo '<url>';
echo '<loc>' . $baseUrl . '/</loc>';
echo '<changefreq>daily</changefreq>';
echo '<priority>1.0</priority>';
echo '</url>';

// 2. PRODUCTOS ACTIVOS (Con la ruta limpia que configuramos en .htaccess)
$stmt = $pdo->query("SELECT slug, updated_at FROM productos WHERE activo = 1");
while ($row = $stmt->fetch()) {
    $fecha = date('Y-m-d', strtotime($row['updated_at']));
    echo '<url>';
    // AQUÍ ESTABA EL ERROR: Cambiamos producto.php?slug= por /producto/
    echo '<loc>' . $baseUrl . '/producto/' . htmlspecialchars($row['slug']) . '</loc>';
    echo '<lastmod>' . $fecha . '</lastmod>';
    echo '<changefreq>weekly</changefreq>';
    echo '<priority>0.8</priority>';
    echo '</url>';
}

echo '</urlset>';
?>
