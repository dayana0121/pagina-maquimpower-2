<?php
require_once 'check_auth.php';
require_once '../includes/db.php';

$log = [];
$contador = 0;

if (isset($_POST['run'])) {
    $productos = $pdo->query("SELECT id, nombre, categoria FROM productos")->fetchAll();

    foreach ($productos as $p) {
        $nombre = strtoupper($p['nombre']);
        $cat = 'OTROS';
        $sub = 'General';
        
        // REGLAS DE CLASIFICACIÓN (El Cerebro)
        if (strpos($nombre, 'ASPIRADORA') !== false) {
            $cat = 'LIMPIEZA';
            $sub = (strpos($nombre, 'INDUSTRIAL') !== false) ? 'Aspiradoras Industriales' : 'Aspiradoras Domesticas';
        }
        elseif (strpos($nombre, 'HIDROLAVADORA') !== false) {
            $cat = 'LIMPIEZA';
            $sub = (strpos($nombre, 'INDUSTRIAL') !== false || strpos($nombre, 'HD') !== false) ? 'Hidrolavadoras Industriales' : 'Hidrolavadoras Domesticas';
        }
        elseif (strpos($nombre, 'LAVATAPIZ') !== false) {
            $cat = 'LIMPIEZA';
            $sub = 'Lavatapices';
        }
        elseif (strpos($nombre, 'FREGADORA') !== false) {
            $cat = 'LIMPIEZA';
            $sub = 'Fregadoras';
        }
        elseif (strpos($nombre, 'COMPRESOR') !== false) {
            $cat = 'CARWASH';
            $sub = 'Compresores de Aire';
        }
        elseif (strpos($nombre, 'SHAMPOO') !== false) {
            $cat = 'CARWASH';
            $sub = 'Shampooneras';
        }
        elseif (strpos($nombre, 'TALADRO') !== false) {
            $cat = 'HERRAMIENTAS';
            $sub = 'Taladros Inalambricos';
        }

        // Aplicar cambios
        if ($cat != 'OTROS') {
            $pdo->prepare("UPDATE productos SET categoria = ?, subcategoria = ? WHERE id = ?")->execute([$cat, $sub, $p['id']]);
            $log[] = "✅ $nombre -> $cat / $sub";
            $contador++;
        }
    }
}
?>

<h1>Reparador Automático de Categorías</h1>
<form method="POST">
    <button type="submit" name="run" style="padding:20px; font-size:20px; cursor:pointer;">EJECUTAR REPARACIÓN AHORA</button>
</form>
<hr>
<h3><?php echo $contador; ?> Productos actualizados</h3>
<div style="height:300px; overflow:auto; background:#eee; padding:10px;">
    <?php foreach($log as $l) echo $l . "<br>"; ?>
</div>