<?php
/**
 * Este script actualiza todos los enlaces absolutos al root (/)
 * y los cambia por (/pagina/) en todos los archivos PHP, JS, HTML.
 */

$dir = 'C:/xampp/htdocs/pagina';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));

$archivosParaModificar = [];
foreach ($iterator as $file) {
    if ($file->isFile()) {
        $ext = strtolower($file->getExtension());
        if (in_array($ext, ['php', 'js', 'html'])) {
            $archivosParaModificar[] = $file->getPathname();
        }
    }
}

// Para JS también las comillas simples
$reemplazos = [
    'href="/' => 'href="/pagina/',
    "href='/" => "href='/pagina/",
    'action="/' => 'action="/pagina/',
    'src="/' => 'src="/pagina/',
    "src='/" => "src='/pagina/",
    "fetch('/" => "fetch('/pagina/",
    "fetch(\"/" => "fetch(\"/pagina/",
    "|| '/assets" => "|| '/pagina/assets",
    "'/api/" => "'/pagina/api/",
    "\"/api/" => "\"/pagina/api/",
    "<a href=\"/" => "<a href=\"/pagina/",
    "<a href='/" => "<a href='/pagina/",
];

$countGlobal = 0;

foreach ($archivosParaModificar as $archivo) {
    if (strpos($archivo, 'fix_links.php') !== false)
        continue;

    $contenido = file_get_contents($archivo);

    $nuevoContenido = str_replace(array_keys($reemplazos), array_values($reemplazos), $contenido);

    // Y para los redirect en PHP tipo header('Location: /')
    $nuevoContenido = str_replace("Location: /", "Location: /pagina/", $nuevoContenido);

    if ($contenido !== $nuevoContenido) {
        file_put_contents($archivo, $nuevoContenido);
        echo "Modificado: $archivo\n";
        $countGlobal++;
    }
}

echo "Total de archivos modificados ahora: $countGlobal\n";
