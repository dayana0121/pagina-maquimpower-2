<?php
require_once 'check_auth.php';
require_once '../includes/db.php';

// --- A. LÓGICA DE ACTUALIZACIÓN DE ESTADO (BACKEND) ---
// Si se envía el formulario por POST (Ajax o normal), actualizamos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    try {
        $stmt = $pdo->prepare("UPDATE pedidos SET estado = ? WHERE id = ?");
        $stmt->execute([$_POST['estado'], $_POST['id']]);

        // Aquí podrías agregar la lógica de enviar correo si lo deseas

        // Redirigir para evitar reenvío de formulario
        header("Location: gestionar_pedidos.php?msg=updated");
        exit;
    } catch (Exception $e) {
        header("Location: gestionar_pedidos.php?msg=error");
        exit;
    }
}

// --- B. MOTOR DE BÚSQUEDA INTELIGENTE ---
$whereClauses = ["1=1"];
$params = [];

// Filtro Texto (ID, Cliente, DNI/RUC guardado en JSON o Teléfono)
if (!empty($_GET['q'])) {
    $term = "%" . $_GET['q'] . "%";
    // Buscamos en ID, Nombre Usuario, o dentro del JSON de dirección (DNI/Teléfono)
    $whereClauses[] = "(p.id LIKE :q OR u.nombre LIKE :q OR u.apellido LIKE :q OR p.direccion_json LIKE :q)";
    $params[':q'] = $term;
}

// Filtro Estado
if (!empty($_GET['estado'])) {
    $whereClauses[] = "p.estado = :est";
    $params[':est'] = $_GET['estado'];
}

$whereSql = implode(" AND ", $whereClauses);

$sql = "SELECT p.*, u.nombre, u.apellido, u.email, u.telefono as tel_user 
        FROM pedidos p 
        LEFT JOIN usuarios u ON p.usuario_id = u.id 
        WHERE $whereSql 
        ORDER BY p.id DESC LIMIT 50";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$pedidos = $stmt->fetchAll();
$totalResultados = count($pedidos);

// Helper para colores de estado
function getStatusColor($est)
{
    return match ($est) {
        'pendiente' => 'warning',
        'pagado' => 'success',
        'enviado' => 'primary',
        'preparacion' => 'info',
        'entregado' => 'dark',
        'cancelado' => 'danger',
        default => 'secondary'
    };
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Pedidos | MaquimPower Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

    <!-- ESTILO CORPORATIVO -->
    <style>
        :root {
            --mp-orange: #FF4500;
            --mp-black: #111;
        }

        body {
            background: #f4f6f9;
            font-family: 'Segoe UI', system-ui, sans-serif;
        }

        /* HEADER */
        .header-corp {
            background: var(--mp-black);
            color: white;
            padding: 15px 0;
            border-bottom: 4px solid var(--mp-orange);
            margin-bottom: 30px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        /* CARDS */
        .card-corp {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
            background: white;
        }

        /* TABLA */
        .table-head-corp {
            background: #222;
            color: white;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 1px;
        }

        .badge-status {
            font-size: 0.75rem;
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .row-pagado {
            border-left: 5px solid #198754 !important;
        }

        .row-pendiente {
            border-left: 5px solid #ffc107 !important;
        }

        /* INPUTS */
        .search-input {
            border-radius: 50px 0 0 50px;
            border: 1px solid #ddd;
            padding: 12px 20px;
        }

        .search-btn {
            border-radius: 0 50px 50px 0;
            background: var(--mp-black);
            color: white;
            border: 1px solid var(--mp-black);
            padding: 10px 25px;
        }

        .search-btn:hover {
            background: var(--mp-orange);
            border-color: var(--mp-orange);
        }

        /* EMPTY STATE */
        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: #999;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 15px;
            display: block;
            opacity: 0.3;
        }
    </style>
</head>

<body>

    <!-- HEADER -->
    <div class="header-corp">
        <div class="container-fluid px-4 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <h4 class="m-0 fw-black text-uppercase"><i class="bi bi-box-seam-fill text-danger me-2"></i> Gestión de
                    Pedidos</h4>
            </div>
            <a href="dashboard.php" class="btn btn-outline-light btn-sm rounded-pill px-4">Volver</a>
        </div>
    </div>

    <div class="container-fluid px-4">

        <!-- BARRA DE HERRAMIENTAS INTELIGENTE -->
        <div class="row mb-4">
            <div class="col-md-8 mx-auto">
                <div class="card card-corp p-2">
                    <form class="d-flex w-100" id="searchForm">
                        <input type="text" name="q" class="form-control search-input shadow-none"
                            placeholder="🔍 Buscar por ID (#001), Cliente, DNI o Teléfono..."
                            value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">

                        <select name="estado" class="form-select border-start-0 border-end-0 rounded-0 bg-light"
                            style="max-width: 150px; border-color: #ddd;">
                            <option value="">Estado: Todos</option>
                            <option value="pendiente" <?= ($_GET['estado'] ?? '') == 'pendiente' ? 'selected' : '' ?>>Pendiente
                            </option>
                            <option value="pagado" <?= ($_GET['estado'] ?? '') == 'pagado' ? 'selected' : '' ?>>Pagado</option>
                            <option value="enviado" <?= ($_GET['estado'] ?? '') == 'enviado' ? 'selected' : '' ?>>Enviado
                            </option>
                        </select>

                        <button type="submit" class="btn search-btn px-4 fw-bold">FILTRAR</button>

                        <?php if (!empty($_GET['q']) || !empty($_GET['estado'])): ?>
                            <a href="gestionar_pedidos.php"
                                class="btn btn-danger ms-2 rounded-circle d-flex align-items-center justify-content-center"
                                style="width: 40px;" title="Limpiar Filtros">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>

        <!-- RESULTADOS -->
        <div class="card card-corp overflow-hidden">
            <?php if ($totalResultados > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-head-corp">
                            <tr>
                                <th class="ps-4">ID</th>
                                <th>Cliente</th>
                                <th>Fecha</th>
                                <th>Total</th>
                                <th class="text-center">Estado</th>
                                <th class="text-end pe-4">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pedidos as $p):
                                $dirData = json_decode($p['direccion_json'], true);
                                $tel = $dirData['celular'] ?? $p['tel_user'];
                                $color = getStatusColor($p['estado']);
                                ?>
                                <tr class="row-<?= $p['estado'] ?>">
                                    <td class="ps-4 fw-bold text-dark">#<?= str_pad($p['id'], 5, '0', STR_PAD_LEFT) ?></td>
                                    <td>
                                        <div class="fw-bold text-dark" style="font-size: 1.1rem;">
                                            <?= $p['nombre'] . ' ' . $p['apellido'] ?></div>
                                        <div class="small text-muted"><i class="bi bi-whatsapp text-success"></i> <?= $tel ?>
                                        </div>
                                    </td>
                                    <td class="small text-muted fw-semibold">
                                        <?= date('d/m/Y H:i', strtotime($p['created_at'])) ?></td>
                                    <td class="fw-black text-dark" style="font-size: 1.15rem;">S/
                                        <?= number_format($p['total'], 2) ?></td>

                                    <!-- SELECTOR DE ESTADO CON SWAL -->
                                    <td class="text-center">
                                        <select
                                            class="form-select form-select-sm fw-bold border-2 border-<?= $color ?> text-<?= $color ?> text-uppercase text-center shadow-sm"
                                            style="width: 160px; margin: 0 auto; cursor: pointer; border-radius: 8px; padding: 8px;"
                                            onfocus="this.oldValue = this.value;"
                                            onchange="cambiarEstado(this, <?= $p['id'] ?>)">
                                            <option value="pendiente" <?= $p['estado'] == 'pendiente' ? 'selected' : '' ?>>🟠 Pendiente
                                            </option>
                                            <option value="pagado" <?= $p['estado'] == 'pagado' ? 'selected' : '' ?>>🟢 Pagado</option>
                                            <option value="preparacion" <?= $p['estado'] == 'preparacion' ? 'selected' : '' ?>>📦
                                                Preparando</option>
                                            <option value="enviado" <?= $p['estado'] == 'enviado' ? 'selected' : '' ?>>🔵 Enviado
                                            </option>
                                            <option value="entregado" <?= $p['estado'] == 'entregado' ? 'selected' : '' ?>>⚫ Entregado
                                            </option>
                                            <option value="cancelado" <?= $p['estado'] == 'cancelado' ? 'selected' : '' ?>>🔴 Cancelado
                                            </option>
                                        </select>
                                    </td>

                                    <td class="text-end pe-4">
                                        <button class="btn btn-dark btn-sm rounded-circle shadow-sm me-1"
                                            onclick='verPedido(<?= json_encode($p) ?>)' title="Ver Detalle">
                                            <i class="bi bi-eye-fill"></i>
                                        </button>

                                        <button class="btn btn-outline-dark btn-sm rounded-circle shadow-sm me-1"
                                            onclick='imprimirComprobante(<?= json_encode($p) ?>)' title="Imprimir">
                                            <i class="bi bi-printer"></i>
                                        </button>

                                        <?php $msg = "Hola {$p['nombre']}, te saludamos de MaquimPower. Tu pedido #" . str_pad($p['id'], 5, '0', STR_PAD_LEFT) . " está en proceso."; ?>
                                        <a href="https://wa.me/51<?= $tel ?>?text=<?= urlencode($msg) ?>" target="_blank"
                                            class="btn btn-success btn-sm rounded-circle shadow-sm">
                                            <i class="bi bi-whatsapp"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <!-- EMPTY STATE (CUANDO NO HAY RESULTADOS) -->
                <div class="empty-state">
                    <i class="bi bi-search"></i>
                    <h4>No encontramos pedidos</h4>
                    <p>Intenta cambiar los filtros o busca por otro término.</p>
                    <a href="gestionar_pedidos.php" class="btn btn-outline-dark rounded-pill px-4 mt-2">Limpiar Búsqueda</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- FORMULARIO OCULTO PARA CAMBIAR ESTADO -->
    <form id="statusForm" method="POST" style="display:none;">
        <input type="hidden" name="action" value="update_status">
        <input type="hidden" name="id" id="hidden_id">
        <input type="hidden" name="estado" id="hidden_estado">
    </form>

    <!-- MODAL DETALLE -->
    <div class="modal fade" id="orderModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 overflow-hidden">
                <div class="modal-header bg-black text-white py-3">
                    <h5 class="modal-title fw-bold text-uppercase">Pedido #<span id="m-id"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="row g-0">
                        <div class="col-md-5 bg-light p-4 border-end">
                            <h6 class="fw-bold text-uppercase mb-3 text-muted small">Datos de Envío</h6>
                            <p class="mb-1"><strong id="m-cliente"></strong></p>
                            <p class="small text-muted mb-3">DNI: <span id="m-dni"></span></p>
                            <div class="alert alert-white border shadow-sm p-2 small">
                                <i class="bi bi-geo-alt-fill text-danger"></i> <span id="m-dir"></span>
                                <div class="text-muted fst-italic mt-1" id="m-ref"></div>
                            </div>
                        </div>
                        <div class="col-md-7 p-4 bg-white">
                            <h6 class="fw-bold text-uppercase mb-3 text-muted small">Contenido</h6>
                            <div id="m-items" class="overflow-auto custom-scroll" style="max-height: 250px;"></div>
                            <div class="border-top mt-3 pt-3 d-flex justify-content-between align-items-center">
                                <span class="text-muted fw-bold small">TOTAL</span>
                                <span class="fs-3 fw-black text-dark" id="m-total"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SCRIPTS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // --- 1. LÓGICA DE CAMBIO DE ESTADO CON SWEETALERT ---
        function cambiarEstado(select, id) {
            const nuevoEstado = select.value;
            const textoEstado = select.options[select.selectedIndex].text;

            Swal.fire({
                title: '¿Actualizar Estado?',
                text: `El pedido cambiará a: ${textoEstado}`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#FF4500',
                cancelButtonColor: '#333',
                confirmButtonText: 'Sí, actualizar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Enviar formulario
                    document.getElementById('hidden_id').value = id;
                    document.getElementById('hidden_estado').value = nuevoEstado;
                    document.getElementById('statusForm').submit();
                } else {
                    // Revertir cambio visualmente si cancela
                    select.value = select.oldValue;
                }
            });
        }

        // --- 2. NOTIFICACIONES DE ÉXITO (Toast) ---
        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'updated'): ?>
            const Toast = Swal.mixin({
                toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true,
                background: '#111', color: '#fff'
            });
            Toast.fire({ icon: 'success', title: 'Estado actualizado correctamente' });
        <?php endif; ?>

        // --- 3. MODAL DETALLE ---
        function verPedido(p) {
            document.getElementById('m-id').innerText = String(p.id).padStart(5, '0');
            document.getElementById('m-cliente').innerText = p.nombre + ' ' + p.apellido;
            document.getElementById('m-total').innerText = 'S/ ' + parseFloat(p.total).toFixed(2);

            let dir = JSON.parse(p.direccion_json || '{}');
            document.getElementById('m-dir').innerHTML = `${dir.direccion || ''}<br>${dir.distrito_nom || ''}, ${dir.provincia || ''}`;
            document.getElementById('m-ref').innerText = dir.referencia ? '(' + dir.referencia + ')' : '';
            document.getElementById('m-dni').innerText = dir.dni || '-';

            let items = JSON.parse(p.detalle_json || '[]');
            let html = '';
            items.forEach(i => {
                let img = i.img || '../assets/img/no-photo.png';
                html += `
            <div class="d-flex align-items-center mb-2 pb-2 border-bottom border-light">
                <img src="${img}" class="prod-thumb me-3">
                <div class="lh-1 w-100">
                    <div class="fw-bold small text-dark">${i.nombre}</div>
                    <div class="d-flex justify-content-between small text-muted mt-1">
                        <span>${i.cantidad} un.</span>
                        <span class="fw-bold">S/ ${i.precio}</span>
                    </div>
                </div>
            </div>`;
            });
            document.getElementById('m-items').innerHTML = html;
            new bootstrap.Modal(document.getElementById('orderModal')).show();
        }

        // --- 4. IMPRIMIR ---
        function imprimirComprobante(p) {
            const dir = JSON.parse(p.direccion_json || '{}');
            const items = JSON.parse(p.detalle_json || '[]');

            let rows = items.map(i => `
        <tr>
            <td style="padding:8px; border-bottom:1px solid #eee;">${i.sku || '-'}</td>
            <td style="padding:8px; border-bottom:1px solid #eee;">${i.nombre}</td>
            <td style="padding:8px; border-bottom:1px solid #eee; text-align:center;">${i.cantidad}</td>
            <td style="padding:8px; border-bottom:1px solid #eee; text-align:right;">S/ ${parseFloat(i.precio).toFixed(2)}</td>
            <td style="padding:8px; border-bottom:1px solid #eee; text-align:right;">S/ ${(i.precio * i.cantidad).toFixed(2)}</td>
        </tr>
    `).join('');

            const invoiceHTML = `
        <html><head><title>Orden #${String(p.id).padStart(5, '0')}</title>
        <style>body{font-family:Helvetica,sans-serif;font-size:14px;padding:40px;color:#333}.header{border-bottom:3px solid #FF4500;padding-bottom:20px;margin-bottom:30px;display:flex;justify-content:space-between}.brand h1{margin:0;color:#000;text-transform:uppercase}.meta{text-align:right}table{width:100%;border-collapse:collapse}th{text-align:left;background:#111;color:white;padding:10px;text-transform:uppercase;font-size:12px}.footer{margin-top:50px;text-align:center;font-size:12px;color:#999;border-top:1px solid #eee;padding-top:20px}</style>
        </head><body>
            <div class="header">
                <div class="brand"><h1>MaquimPower</h1><div>MAQUIMSA E.I.R.L - RUC 20606853182</div></div>
                <div class="meta"><h2>ORDEN #${String(p.id).padStart(5, '0')}</h2><div>${p.created_at}</div></div>
            </div>
            <p><strong>Cliente:</strong> ${p.nombre} ${p.apellido}<br><strong>DNI:</strong> ${dir.dni || '-'}<br><strong>Dirección:</strong> ${dir.direccion || ''} - ${dir.distrito_nom || ''}</p>
            <table><thead><tr><th>SKU</th><th>Producto</th><th>Cant</th><th>P.Unit</th><th>Total</th></tr></thead><tbody>${rows}</tbody></table>
            <h2 style="text-align:right; margin-top:20px;">TOTAL: S/ ${parseFloat(p.total).toFixed(2)}</h2>
            <div class="footer">www.maquimpower.com</div>
            <script>window.print();<\/script>
        </body></html>
    `;
            const win = window.open('', '_blank');
            win.document.write(invoiceHTML);
            win.document.close();
        }
    </script>

</body>

</html>