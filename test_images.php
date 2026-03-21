<?php
header('Content-Type: text/html; charset=utf-8');

$baseUrl = 'http://maquimpower.com';

echo "<h1>TEST DE IMÁGENES DEL SLIDER</h1>";
echo "<p>Probando si las imágenes cargan correctamente...</p>";
echo "<hr>";

$images = [
    'web' => [
        'banner-1-pc.webp',
        'banner-2-pc.webp',
        'banner-3-pc.webp',
        'banner-4-pc.webp'
    ],
    'mobile' => [
        'banner-1-mobile.webp',
        'banner-2-mobile.webp',
        'banner-3-mobile.webp',
        'banner-4-mobile.webp'
    ]
];

echo "<h2>DESKTOP IMAGES</h2>";
echo "<div style='display: flex; flex-wrap: wrap; gap: 20px;'>";
foreach ($images['web'] as $img) {
    $url = $baseUrl . '/assets/img/web/' . $img;
    echo "<div style='border: 2px solid #ccc; padding: 10px;'>";
    echo "<p><strong>" . $img . "</strong></p>";
    echo "<img src='" . $url . "' style='max-width: 300px; max-height: 200px; border: 1px solid #999;' loading='lazy'>";
    echo "<p style='font-size: 12px; color: #666;'>" . $url . "</p>";
    echo "</div>";
}
echo "</div>";

echo "<h2>MOBILE IMAGES</h2>";
echo "<div style='display: flex; flex-wrap: wrap; gap: 20px;'>";
foreach ($images['mobile'] as $img) {
    $url = $baseUrl . '/assets/img/mobile/' . $img;
    echo "<div style='border: 2px solid #ccc; padding: 10px;'>";
    echo "<p><strong>" . $img . "</strong></p>";
    echo "<img src='" . $url . "' style='max-width: 200px; max-height: 300px; border: 1px solid #999;' loading='lazy'>";
    echo "<p style='font-size: 12px; color: #666;'>" . $url . "</p>";
    echo "</div>";
}
echo "</div>";

echo "<h2>VERIFICACIÓN DE ARCHIVOS EN SERVIDOR</h2>";
echo "<p><strong>Archivos en /assets/img/web/:</strong></p>";
if (is_dir($_SERVER['DOCUMENT_ROOT'] . '/assets/img/web')) {
    $files_web = scandir($_SERVER['DOCUMENT_ROOT'] . '/assets/img/web');
    echo "<ul>";
    foreach ($files_web as $f) {
        if (strpos($f, '.webp') !== false) {
            $path = $_SERVER['DOCUMENT_ROOT'] . '/assets/img/web/' . $f;
            $size = filesize($path);
            echo "<li>$f (" . round($size/1024, 2) . " KB)</li>";
        }
    }
    echo "</ul>";
} else {
    echo "<p style='color: red;'>❌ Directorio no encontrado</p>";
}

echo "<p><strong>Archivos en /assets/img/mobile/:</strong></p>";
if (is_dir($_SERVER['DOCUMENT_ROOT'] . '/assets/img/mobile')) {
    $files_mobile = scandir($_SERVER['DOCUMENT_ROOT'] . '/assets/img/mobile');
    echo "<ul>";
    foreach ($files_mobile as $f) {
        if (strpos($f, '.webp') !== false) {
            $path = $_SERVER['DOCUMENT_ROOT'] . '/assets/img/mobile/' . $f;
            $size = filesize($path);
            echo "<li>$f (" . round($size/1024, 2) . " KB)</li>";
        }
    }
    echo "</ul>";
} else {
    echo "<p style='color: red;'>❌ Directorio no encontrado</p>";
}
?>
