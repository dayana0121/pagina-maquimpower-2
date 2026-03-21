<?php
/**
 * Arregla las duplicaciones manuales de / 
 * dejándolo solo como /
 */

$dir = 'C:/xampp/htdocs/pagina';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));

$countGlobal = 0;

foreach ($iterator as $file) {
    if ($file->isFile()) {
        $ext = strtolower($file->getExtension());
        if (in_array($ext, ['php', 'js', 'html'])) {
            $archivo = $file->getPathname();
            if (strpos($archivo, 'fix_links') !== false)
                continue;

            $contenido = file_get_contents($archivo);

            // Reemplazar múltiples / por un solo /
            $nuevoContenido = preg_replace('#(/pagina)+/#', '/', $contenido);

            // Reemplazar baseUrl si tiene múltiples /pagina
            $nuevoContenido = preg_replace('#"//pagina#', '"//pagina', $nuevoContenido); // Por si acaso
            $nuevoContenido = preg_replace('#\$_SERVER\[\'HTTP_HOST\'\] \. "(/pagina)+"#', '$_SERVER[\'HTTP_HOST\'] . "/pagina"', $nuevoContenido);

            if ($contenido !== $nuevoContenido) {
                file_put_contents($archivo, $nuevoContenido);
                echo "Arreglado: $archivo\n";
                $countGlobal++;
            }
        }
    }
}

echo "Total de archivos arreglados: $countGlobal\n";
