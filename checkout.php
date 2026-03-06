<?php
// 1. INICIAR SESIÓN Y DB
require_once 'includes/db.php';
if (session_status() === PHP_SESSION_NONE)
    session_start();

// 2. SEGURIDAD
if (!isset($_SESSION['user_id'])) {
    header("Location: /pagina/login.php?redirect=checkout");
    exit;
}

// 3. CARGAR HEADER
require_once 'includes/header.php';
?>

<style>
    /* --- ESTILOS CORPORATIVOS --- */
    body {
        background-color: #f4f6f9;
    }

    /* TARJETAS */
    .checkout-card {
        background: white;
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
        border: 1px solid #eee;
        margin-bottom: 25px;
    }

    /* TÍTULOS DE SECCIÓN */
    .section-header {
        display: flex;
        align-items: center;
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 2px solid #f8f9fa;
    }

    .section-header i {
        font-size: 1.4rem;
        color: var(--primary);
        margin-right: 12px;
    }

    .section-header h5 {
        font-weight: 900;
        margin: 0;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #111;
    }

    /* INPUTS MODERNOS (BLOCK STYLE) */
    .form-label-corp {
        font-size: 0.75rem;
        font-weight: 800;
        color: #666;
        text-transform: uppercase;
        margin-bottom: 8px;
        display: block;
    }

    .form-control-corp {
        border: 2px solid #eee;
        border-radius: 8px;
        padding: 12px 15px;
        font-weight: 600;
        font-size: 0.95rem;
        color: #333;
        background-color: #fff;
        transition: 0.3s;
        width: 100%;
    }

    .form-control-corp:focus {
        border-color: var(--primary);
        outline: none;
        box-shadow: 0 0 0 4px rgba(255, 69, 0, 0.1);
    }

    .form-control-corp[readonly] {
        background-color: #f2f2f2;
        color: #888;
        cursor: not-allowed;
    }

    /* STEPPER */
    .stepper-wrapper {
        padding: 40px 0;
    }

    .stepper {
        display: flex;
        justify-content: center;
        position: relative;
        max-width: 600px;
        margin: 0 auto;
    }

    .stepper::before {
        content: '';
        position: absolute;
        top: 20px;
        left: 15%;
        right: 15%;
        height: 3px;
        background: #e0e0e0;
        z-index: 0;
    }

    .step {
        width: 33%;
        text-align: center;
        position: relative;
        z-index: 1;
    }

    .step-circle {
        width: 45px;
        height: 45px;
        background: #fff;
        border: 3px solid #e0e0e0;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 10px;
        font-weight: bold;
        color: #ccc;
        transition: 0.3s;
    }

    .step.active .step-circle {
        border-color: var(--primary);
        background: var(--primary);
        color: white;
        box-shadow: 0 0 0 5px rgba(255, 69, 0, 0.15);
    }

    .step.completed .step-circle {
        border-color: #198754;
        background: #198754;
        color: white;
    }

    .step-text {
        font-size: 0.75rem;
        font-weight: 800;
        text-transform: uppercase;
        color: #999;
    }

    .step.active .step-text {
        color: var(--primary);
    }

    /* BOTÓN PAGAR */
    .btn-pay-corp {
        width: 100%;
        background: #111;
        color: white;
        padding: 18px;
        border-radius: 10px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1px;
        border: none;
        transition: 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .btn-pay-corp:not(:disabled):hover {
        background: var(--primary);
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(255, 69, 0, 0.3);
    }

    .btn-pay-corp:disabled {
        background: #ccc;
        cursor: not-allowed;
        opacity: 0.8;
    }

    /* RESUMEN FLOTANTE */
    .sticky-summary {
        position: sticky;
        top: 110px;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
        font-size: 0.9rem;
    }

    .total-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-top: 2px dashed #eee;
        margin-top: 15px;
        padding-top: 15px;
    }
</style>

<div class="container" style="min-height: 100vh;">

    <!-- STEPPER -->
    <div class="stepper-wrapper">
        <div class="stepper">
            <div class="step completed">
                <div class="step-circle"><i class="bi bi-check-lg"></i></div>
                <div class="step-text">Carrito</div>
            </div>
            <div class="step active">
                <div class="step-circle">2</div>
                <div class="step-text">Datos y Envío</div>
            </div>
            <div class="step">
                <div class="step-circle">3</div>
                <div class="step-text">Confirmación</div>
            </div>
        </div>
    </div>

    <div class="row g-4"> <!-- ROW PRINCIPAL: Aquí se divide la pantalla -->

        <!-- =======================
             COLUMNA IZQUIERDA (70%)
             ======================= -->
        <div class="col-lg-7">

            <!-- 1. DATOS PERSONALES -->
            <div class="checkout-card animate__animated animate__fadeInUp">
                <div class="section-header">
                    <i class="bi bi-person-vcard-fill"></i>
                    <h5>Tus Datos</h5>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label-corp">Nombre Completo</label>
                        <input type="text" class="form-control-corp" value="<?php echo $_SESSION['user_name']; ?>"
                            readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-corp">DNI / RUC *</label>
                        <input type="text" id="dni_factura" class="form-control-corp" placeholder="Para tu comprobante"
                            required>
                    </div>
                    <div class="col-12">
                        <label class="form-label-corp">Celular de Contacto *</label>
                        <input type="tel" id="tel_factura" class="form-control-corp"
                            placeholder="Para coordinar la entrega (WhatsApp)" required>
                    </div>
                </div>
            </div>

            <!-- 2. DIRECCIÓN -->
            <div class="checkout-card animate__animated animate__fadeInUp" style="animation-delay: 0.1s;">
                <div class="section-header">
                    <i class="bi bi-geo-alt-fill"></i>
                    <h5>Dirección de Envío</h5>
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label-corp">Departamento</label>
                        <select class="form-control-corp form-select" id="dep" onchange="cargarProvincias(this.value)">
                            <option value="">Seleccione...</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label-corp">Provincia</label>
                        <select class="form-control-corp form-select" id="prov" disabled
                            onchange="cargarDistritos(this.value)">
                            <option value="">-</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label-corp">Distrito</label>
                        <select class="form-control-corp form-select" id="dist" disabled onchange="calcularEnvio()">
                            <option value="">-</option>
                        </select>
                    </div>
                    <div class="col-12 mt-3">
                        <label class="form-label-corp">Dirección Exacta *</label>
                        <input type="text" id="direccion_exacta" class="form-control-corp"
                            placeholder="Av. Calle, Número, Urb." required>
                    </div>
                    <div class="col-12">
                        <label class="form-label-corp">Referencia (Opcional)</label>
                        <input type="text" id="referencia" class="form-control-corp"
                            placeholder="Color de casa, frente a parque, etc.">
                    </div>
                </div>
            </div>

            <!-- 3. PAGO -->
            <div class="checkout-card animate__animated animate__fadeInUp" style="animation-delay: 0.2s;">
                <div class="section-header">
                    <i class="bi bi-shield-check"></i>
                    <h5>Método de Pago</h5>
                </div>

                <ul class="nav nav-pills mb-4 nav-justified gap-3" id="pills-tab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active border py-3 fw-bold shadow-sm" id="pills-card-tab"
                            data-bs-toggle="pill" data-bs-target="#pills-card" type="button"
                            onclick="setMetodo('tarjeta')">
                            <i class="bi bi-credit-card me-2"></i> Tarjeta / Yape
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link border py-3 fw-bold shadow-sm" id="pills-trans-tab"
                            data-bs-toggle="pill" data-bs-target="#pills-trans" type="button"
                            onclick="setMetodo('transferencia')">
                            <i class="bi bi-bank me-2"></i> Transferencia
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="pills-tabContent">
                    <!-- PANEL TARJETA -->
                    <div class="tab-pane fade show active" id="pills-card">
                        <div id="payment-form-container"
                            class="bg-light p-4 rounded-4 text-center border border-dashed">
                            <img src="/pagina/assets/img/niubiz-logo.png" alt="Niubiz" style="max-height: 40px;"
                                class="mb-3 opacity-75">
                            <h6 class="fw-bold">Pasarela Segura Niubiz</h6>
                            <p class="small text-muted mb-3">Aceptamos todas las tarjetas de crédito y débito.</p>
                            <div class="alert alert-info py-2 small mb-0">
                                <i class="bi bi-info-circle-fill me-2"></i> Completa tu dirección para habilitar el
                                pago.
                            </div>
                        </div>
                    </div>

                    <!-- PANEL TRANSFERENCIA -->
                    <div class="tab-pane fade" id="pills-trans">
                        <div class="bg-white border rounded-4 p-4 shadow-sm">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                                    <i class="bi bi-info-circle-fill text-primary fs-4"></i>
                                </div>
                                <h6 class="fw-bold m-0">Instrucciones de Transferencia</h6>
                            </div>

                            <p class="small text-muted mb-4">
                                Tu pedido se registrará como <b>PENDIENTE</b>. Deberás realizar el pago y enviar el
                                comprobante para procesar el envío.
                            </p>

                            <!-- BANCOS -->
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <div class="p-3 border rounded-3 bg-light">
                                        <div class="fw-black text-success small mb-2">INTERBANK</div>
                                        <div class="small fw-bold">200-3005193670</div>
                                        <div class="small text-muted" style="font-size: 0.7rem;">CCI:
                                            003-200-003005193670-33</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 border rounded-3 bg-light">
                                        <div class="fw-black text-primary small mb-2">BCP</div>
                                        <div class="small fw-bold">1917298883088</div>
                                        <div class="small text-muted" style="font-size: 0.7rem;">CCI:
                                            00219100729888308850</div>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-warning py-3 mb-0 rounded-3">
                                <div class="d-flex">
                                    <i class="bi bi-whatsapp fs-4 me-3"></i>
                                    <div>
                                        <div class="fw-bold small">VALIDACIÓN POR WHATSAPP</div>
                                        <div class="small mt-1">Envía tu voucher al <b>902 010 281</b> indicando tu
                                            número de orden.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div> <!-- CIERRE COLUMNA IZQUIERDA -->

        <!-- =======================
             COLUMNA DERECHA (30%)
             ======================= -->
        <div class="col-lg-5">
            <div class="checkout-card sticky-summary" style="border-top: 5px solid var(--primary);">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-black m-0">RESUMEN DEL PEDIDO</h5>
                    <span class="badge bg-light text-dark border">CONFIRMACIÓN</span>
                </div>

                <!-- LISTA DE ITEMS -->
                <div id="resumen-items" class="mb-4 pe-2 custom-scroll" style="max-height: 300px; overflow-y: auto;">
                    <!-- Se llena con JS -->
                    <div class="text-center py-3 text-muted small">Cargando carrito...</div>
                </div>

                <!-- TOTALES -->
                <div class="bg-light p-3 rounded-3 mb-4 border">
                    <div class="summary-row">
                        <span class="text-muted fw-bold">Subtotal</span>
                        <span id="resumen-subtotal" class="fw-bold text-dark">S/ 0.00</span>
                    </div>
                    <div class="summary-row">
                        <span class="text-muted fw-bold">Envío</span>
                        <span id="resumen-envio" class="fw-bold text-primary">--</span>
                    </div>
                    <div class="total-row">
                        <span class="fs-5 fw-black text-dark">TOTAL</span>
                        <span id="resumen-total" class="fs-2 fw-black text-primary">S/ 0.00</span>
                    </div>
                </div>

                <!-- BOTÓN ACCIÓN -->
                <button class="btn-pay-corp" id="btn-pagar" disabled>
                    CONFIRMAR COMPRA <i class="bi bi-arrow-right-circle-fill ms-2 fs-5"></i>
                </button>

                <div class="text-center mt-3 small text-muted opacity-75">
                    <i class="bi bi-lock-fill text-success"></i> Pago encriptado con seguridad SSL
                </div>
            </div>
        </div> <!-- CIERRE COLUMNA DERECHA -->

    </div> <!-- CIERRE ROW PRINCIPAL -->
</div>

<!-- SCRIPTS LÓGICOS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // VARIABLES GLOBALES
    let totalProductos = 0;
    let costoEnvio = 0;
    let metodoSeleccionado = 'tarjeta';

    const tarifasLima = {
        'Los Olivos': 10, 'Comas': 10, 'San Martin de Porres': 15, 'Independencia': 15,
        'Miraflores': 20, 'San Isidro': 20, 'Surco': 25, 'La Molina': 25
    };

    document.addEventListener('DOMContentLoaded', () => {
        cargarCarritoResumen();
        cargarDepartamentos();
    });

    // 1. UBIGEO API
    async function cargarDepartamentos() {
        try {
            const res = await fetch('/pagina/api/ubigeo.php?accion=departamentos');
            const deps = await res.json();
            let html = '<option value="">Seleccione...</option>';
            deps.forEach(d => html += `<option value="${d}">${d}</option>`);
            document.getElementById('dep').innerHTML = html;
        } catch (e) { console.error("Error Ubigeo:", e); }
    }

    async function cargarProvincias(dep) {
        if (!dep) return;
        const res = await fetch(`/api/ubigeo.php?accion=provincias&filtro=${dep}`);
        const provs = await res.json();
        let html = '<option value="">Seleccione...</option>';
        provs.forEach(p => html += `<option value="${p}">${p}</option>`);
        let selProv = document.getElementById('prov');
        selProv.innerHTML = html; selProv.disabled = false;
        document.getElementById('dist').disabled = true;
    }

    async function cargarDistritos(prov) {
        if (!prov) return;
        const res = await fetch(`/api/ubigeo.php?accion=distritos&filtro=${prov}`);
        const dists = await res.json();
        let html = '<option value="">Seleccione...</option>';
        dists.forEach(d => html += `<option value="${d.id_ubigeo}">${d.distrito}</option>`);
        let selDist = document.getElementById('dist');
        selDist.innerHTML = html; selDist.disabled = false;
    }

    // 2. CÁLCULOS
    function setMetodo(m) {
        metodoSeleccionado = m;
        const btn = document.getElementById('btn-pagar');
        if (m === 'transferencia') {
            btn.innerHTML = 'CONFIRMAR Y ENVIAR VOUCHER <i class="bi bi-whatsapp ms-2"></i>';
            btn.classList.add('btn-success');
            btn.classList.remove('btn-dark');
        } else {
            btn.innerHTML = 'PAGAR AHORA <i class="bi bi-credit-card-fill ms-2"></i>';
            btn.classList.add('btn-dark');
            btn.classList.remove('btn-success');
        }
    }

    function calculateTotal() {
        // This is a helper if needed
    }

    function calcularEnvio() {
        let dep = document.getElementById('dep').value;
        let distSelect = document.getElementById('dist');
        let distrito = distSelect.options[distSelect.selectedIndex].text;

        if (dep === 'Lima' || dep === 'Callao') {
            if (tarifasLima[distrito] !== undefined) {
                costoEnvio = tarifasLima[distrito];
                document.getElementById('resumen-envio').innerHTML = `S/ ${costoEnvio.toFixed(2)}`;
            } else {
                costoEnvio = 20;
                document.getElementById('resumen-envio').innerHTML = `S/ 20.00`;
            }
        } else {
            costoEnvio = 0;
            document.getElementById('resumen-envio').innerHTML = '<span class="badge bg-warning text-dark">PAGO EN DESTINO</span>';
        }

        actualizarTotalFinal();

        const btn = document.getElementById('btn-pagar');
        btn.disabled = false;
        btn.classList.add('animate__animated', 'animate__pulse');
        
        // Ensure UI matches current method
        setMetodo(metodoSeleccionado);
    }

    function cargarCarritoResumen() {
        let carrito = JSON.parse(localStorage.getItem('maquim_cart')) || [];
        let html = '';
        totalProductos = 0;

        if (carrito.length === 0) {
            document.getElementById('resumen-items').innerHTML = '<div class="alert alert-warning small">Tu carrito está vacío.</div>';
            return;
        }

        carrito.forEach(item => {
            let subtotal = item.precio * item.cantidad;
            totalProductos += subtotal;
            html += `
            <div class="d-flex align-items-center mb-3 border-bottom pb-2">
                <div class="position-relative me-3">
                    <img src="${item.img}" width="50" height="50" class="rounded border bg-white object-fit-contain p-1">
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-dark text-white border border-light" style="font-size:0.6rem;">${item.cantidad}</span>
                </div>
                <div class="flex-grow-1 overflow-hidden">
                    <h6 class="m-0 small fw-bold text-dark text-truncate" style="font-size:0.8rem;">${item.nombre}</h6>
                    <small class="text-muted" style="font-size:0.7rem;">SKU: ${item.sku}</small>
                </div>
                <div class="fw-bold text-dark ms-2 small">S/ ${subtotal.toFixed(2)}</div>
            </div>
        `;
        });

        document.getElementById('resumen-items').innerHTML = html;
        document.getElementById('resumen-subtotal').innerText = "S/ " + totalProductos.toFixed(2);
        actualizarTotalFinal();
    }

    function actualizarTotalFinal() {
        let granTotal = totalProductos + costoEnvio;
        document.getElementById('resumen-total').innerText = "S/ " + granTotal.toLocaleString('es-PE', { minimumFractionDigits: 2 });
    }

    // 3. PROCESAR
    document.getElementById('btn-pagar').addEventListener('click', function () {
        // Validar Inputs
        const direccion = document.getElementById('direccion_exacta').value;
        const telefono = document.getElementById('tel_factura').value;
        const dni = document.getElementById('dni_factura').value;
        const distritoVal = document.getElementById('dist').value;

        if (!direccion || !telefono || !dni || !distritoVal) {
            Swal.fire({ icon: 'warning', title: 'Faltan Datos', text: 'Por favor completa todos los campos obligatorios (*)', confirmButtonColor: '#333' });
            return;
        }

        const datosEnvio = {
            departamento: document.getElementById('dep').value,
            provincia: document.getElementById('prov').value,
            distrito_id: distritoVal,
            distrito_nom: document.getElementById('dist').options[document.getElementById('dist').selectedIndex].text,
            direccion: direccion,
            referencia: document.getElementById('referencia').value,
            dni: dni,
            celular: telefono
        };

        const metodoPago = metodoSeleccionado;

        Swal.fire({
            title: 'Procesando...', text: 'Generando orden segura.', icon: 'info',
            allowOutsideClick: false, showConfirmButton: false,
            didOpen: () => Swal.showLoading()
        });

        let carrito = JSON.parse(localStorage.getItem('maquim_cart'));

        fetch('/pagina/api/procesar_compra.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                carrito: carrito, total_calculado: totalProductos + costoEnvio,
                costo_envio: costoEnvio, datos_envio: datosEnvio, metodo_pago: metodoPago
            })
        })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    localStorage.removeItem('maquim_cart');
                    Swal.fire({
                        icon: 'success', title: '¡Orden #' + data.order_id + ' Creada!',
                        text: 'Hemos enviado los detalles a tu correo.',
                        confirmButtonText: 'Ver Mi Pedido', confirmButtonColor: '#FF4500'
                    }).then(() => window.location.href = '/perfil.php');
                } else {
                    Swal.fire('Error', data.message || 'Error desconocido.', 'error');
                }
            })
            .catch(err => {
                console.error(err);
                Swal.fire('Error de Conexión', 'Intenta nuevamente.', 'error');
            });
    });
</script>

<?php require_once 'includes/footer.php'; ?>