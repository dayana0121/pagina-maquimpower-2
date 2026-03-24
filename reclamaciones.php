<?php require_once 'includes/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">

            <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-5">
                <div class="card-header bg-dark text-white text-center py-4 border-bottom border-warning border-5">
                    <h3 class="fw-black m-0 text-uppercase">Libro de Reclamaciones Virtual</h3>
                    <small class="text-white-50">Conforme al Código de Protección y Defensa del Consumidor</small>
                </div>

                <div class="card-body p-5">

                    <div class="alert alert-light border d-flex align-items-center mb-5">
                        <div class="display-4 me-3">🏢</div>
                        <div>
                            <h6 class="fw-bold m-0">CORPORACION MAQUIMSA E.I.R.L.</h6>
                            <p class="m-0 text-muted small">RUC: 20606853182 | Av. Industrial 123, Lima</p>
                        </div>
                    </div>

                    <form id="claimForm">

                        <!-- 1. IDENTIFICACIÓN -->
                        <h5 class="fw-bold text-primary mb-4 pb-2 border-bottom">1. Identificación del Consumidor</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Nombre Completo *</label>
                                <input type="text" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Apellidos *</label>
                                <input type="text" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Tipo Doc.</label>
                                <select class="form-select">
                                    <option>DNI</option>
                                    <option>CE</option>
                                    <option>RUC</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Número Doc. *</label>
                                <input type="text" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Celular</label>
                                <input type="tel" class="form-control" placeholder="(51) ...">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Correo Electrónico *</label>
                                <input type="email" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Domicilio</label>
                                <input type="text" class="form-control">
                            </div>
                        </div>

                        <!-- 2. BIEN CONTRATADO -->
                        <h5 class="fw-bold text-primary mb-4 pb-2 border-bottom">2. Identificación del Bien Contratado</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-12">
                                <label class="form-label small fw-bold">Tipo de Bien *</label>
                                <div class="d-flex gap-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="bien" id="prod" checked>
                                        <label class="form-check-label" for="prod">Producto</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="bien" id="serv">
                                        <label class="form-check-label" for="serv">Servicio</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label small fw-bold">Descripción del Bien/Servicio (Nombre del producto)</label>
                                <input type="text" class="form-control" placeholder="Ej: Hidrolavadora Karcher K3..." required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Monto Reclamado (S/)</label>
                                <input type="number" class="form-control" placeholder="0.00" required>
                            </div>
                        </div>

                        <!-- 3. DETALLE -->
                        <h5 class="fw-bold text-primary mb-4 pb-2 border-bottom">3. Detalle de la Reclamación</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <div class="alert alert-info small py-2">
                                    <strong>Reclamo:</strong> Disconformidad con el producto/servicio. <br>
                                    <strong>Queja:</strong> Malestar respecto a la atención al público.
                                </div>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label small fw-bold">Tipo *</label>
                                <select class="form-select mb-3">
                                    <option>Reclamo</option>
                                    <option>Queja</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold">Detalle del Reclamo/Queja *</label>
                                <textarea class="form-control" rows="4" required></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold">Pedido Concreto (Solución esperada) *</label>
                                <textarea class="form-control" rows="2" required></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold">Adjuntar Archivo (Opcional)</label>
                                <input type="file" class="form-control">
                            </div>
                        </div>

                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" required id="declaracion">
                            <label class="form-check-label small" for="declaracion">
                                Acepto el contenido del presente formulario y manifiesto bajo Declaración Jurada la veracidad de lo indicado.
                            </label>
                        </div>

                        <div class="d-grid">
                            <button type="button" id="enviar" class="btn btn-dark btn-lg fw-bold" onclick="enviarReclamo()">
                                ENVIAR HOJA DE RECLAMACIÓN
                            </button>
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    function enviarReclamo() {
        // Aquí iría la lógica de envío real
        Swal.fire({
            icon: 'success',
            title: 'Reclamo Registrado',
            text: 'Se ha enviado una copia a su correo electrónico. Nos pondremos en contacto en el plazo de ley.',
            confirmButtonColor: '#FF4500'
        });
    }
</script>

<?php require_once 'includes/footer.php'; ?>