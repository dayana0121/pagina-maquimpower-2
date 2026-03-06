<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

if (!isset($_SESSION['user_id'])) { header("Location: /pagina/login.php"); exit; }

// Obtener pedidos del usuario
$stmt = $pdo->prepare("SELECT * FROM pedidos WHERE usuario_id = ? ORDER BY id DESC");
$stmt->execute([$_SESSION['user_id']]);
$pedidos = $stmt->fetchAll();
?>

<div class="container py-5">
    <div class="row">
        <!-- Sidebar Perfil (Simplificado) -->
        <div class="col-lg-3 mb-4">
            <div class="card border-0 shadow-sm rounded-4 text-center p-4 bg-dark text-white">
                <div class="mb-3 d-flex justify-content-center">
                    <div class="bg-warning text-dark fw-bold rounded-circle d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; font-size: 2rem;">
                        <?= strtoupper(substr($_SESSION['user_name'], 0, 1)) ?>
                    </div>
                </div>
                <h5 class="fw-bold"><?= $_SESSION['user_name'] ?></h5>
                <p class="small text-white-50">Cliente MaquimPower</p>
                <div class="d-grid gap-2 mt-3">
                    <a href="perfil.php" class="btn btn-primary fw-bold">Mis Pedidos</a>
                    <a href="/pagina/controllers/auth.php?action=logout" class="btn btn-outline-light btn-sm">Cerrar Sesión</a>
                </div>
            </div>
        </div>

        <!-- Historial -->
        <div class="col-lg-9">
            <h3 class="fw-black text-uppercase mb-4">Historial de Compras</h3>
            
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Pedido</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                                <th>Total</th>
                                <th class="text-end pe-4">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($pedidos as $p): ?>
                            <tr>
                                <td class="ps-4 fw-bold">#<?= str_pad($p['id'], 5, '0', STR_PAD_LEFT) ?></td>
                                <td><?= date('d/m/Y', strtotime($p['created_at'])) ?></td>
                                <td>
                                    <?php 
                                        $bg = match($p['estado']) { 'pendiente'=>'warning', 'pagado'=>'success', 'enviado'=>'primary', default=>'secondary' };
                                        echo "<span class='badge bg-$bg text-uppercase'>{$p['estado']}</span>";
                                    ?>
                                </td>
                                <td class="fw-bold">S/ <?= number_format($p['total'], 2) ?></td>
                                <td class="text-end pe-4">
                                    <!-- BOTÓN CORREGIDO -->
                                    <button class="btn btn-outline-dark btn-sm rounded-pill px-3" 
                                            onclick='verDetalle(<?= json_encode($p) ?>)'>
                                        Detalles
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL DETALLE CLIENTE -->
<div class="modal fade" id="modalDetalleUser" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold">Pedido #<span id="ud-id"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <h6 class="fw-bold text-muted small mb-3 text-uppercase">Productos</h6>
                <div id="ud-items" class="bg-white p-3 rounded shadow-sm mb-3"></div>
                
                <div class="d-flex justify-content-between fw-bold fs-5">
                    <span>Total:</span>
                    <span id="ud-total" class="text-primary"></span>
                </div>
                
                <hr>
                <div class="alert alert-light border small text-muted">
                    <i class="bi bi-geo-alt-fill text-danger me-1"></i> 
                    <strong>Dirección de Envío:</strong> <span id="ud-dir"></span>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function verDetalle(p) {
    document.getElementById('ud-id').innerText = String(p.id).padStart(5,'0');
    document.getElementById('ud-total').innerText = 'S/ ' + parseFloat(p.total).toFixed(2);
    
    // Dirección
    let dir = JSON.parse(p.direccion_json || '{}');
    document.getElementById('ud-dir').innerText = `${dir.direccion || 'Sin dirección'}, ${dir.distrito_nom || ''}`;

    // Items
    let items = JSON.parse(p.detalle_json || '[]');
    let html = '';
    items.forEach(i => {
        let img = i.img || 'assets/img/no-photo.png';
        html += `
            <div class="d-flex align-items-center mb-2 pb-2 border-bottom border-light">
                <img src="${img}" width="40" height="40" class="rounded me-3 border bg-white object-fit-contain">
                <div class="lh-1 w-100">
                    <div class="fw-bold small text-dark">${i.nombre}</div>
                    <div class="d-flex justify-content-between small text-muted mt-1">
                        <span>${i.cantidad} un.</span>
                        <span>S/ ${i.precio}</span>
                    </div>
                </div>
            </div>`;
    });
    document.getElementById('ud-items').innerHTML = html;
    
    new bootstrap.Modal(document.getElementById('modalDetalleUser')).show();
}
</script>

<?php require_once 'includes/footer.php'; ?>