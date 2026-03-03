<?php
require_once 'includes/db.php';

// Simulación de URLs que Google podría tener (viejas, mal escritas, fragmentadas)
$urls_a_probar = [
    "pulidora-total-1400w",                    // Falta el "220v"
    "hidrolavadora-karcher-k2-oferta",         // Tiene palabra extra "oferta"
    "ASPIRADORA-MANO-BLACK-DECKER",            // Mayúsculas y falta modelo
    "generador-espuma-ik",                     // Fragmento muy corto
    "pulidora_total_1400w_220v"                // Guiones bajos en vez de medios
];

echo "<h2>Test de Rescate de Tráfico (Google -> MaquimPower)</h2>";
echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%; font-family: Arial;'>
        <tr style='background: #333; color: white;'>
            <th>URL que trae Google</th>
            <th>¿Qué hace tu sistema?</th>
            <th>Destino Final / Resultado</th>
        </tr>";

foreach ($urls_a_probar as $url_fake) {
    // Simulamos lo que hace detalle.php internamente
    $encontrado = false;
    $slugOriginal = $url_fake;
    
    // 1. Intento Exacto
    $stmt = $pdo->prepare("SELECT slug FROM productos WHERE slug = ? AND activo = 1");
    $stmt->execute([$slugOriginal]);
    $p = $stmt->fetch();

    if ($p) {
        $resultado = "✅ Conexión Directa";
        $destino = "/producto/" . $p['slug'];
    } else {
        // 2. Intento por Similitud (Tu lógica actual)
        $slugTest = strtolower(preg_replace('/[^a-z0-9]/', '', $slugOriginal));
        $stmt2 = $pdo->query("SELECT slug FROM productos WHERE activo = 1");
        $mejorCoincidencia = null;
        $maxPorcentaje = 0;

        foreach ($stmt2 as $row) {
            $slugDB = strtolower(preg_replace('/[^a-z0-9]/', '', $row['slug']));
            similar_text($slugTest, $slugDB, $percent);
            if ($percent > $maxPorcentaje) {
                $maxPorcentaje = $percent;
                $mejorCoincidencia = $row['slug'];
            }
        }

        if ($maxPorcentaje > 65) {
            $resultado = "🔄 Rescatado (Similitud $maxPorcentaje%)";
            $destino = "/producto/" . $mejorCoincidencia;
        } else {
            $resultado = "❌ 404 - No encontrado";
            $destino = "Página 404";
        }
    }

    echo "<tr>
            <td><code>/producto/$url_fake</code></td>
            <td>$resultado</td>
            <td><b>$destino</b></td>
          </tr>";
}
echo "</table>";
?>