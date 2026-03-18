<?php require_once 'includes/header.php'; ?>

<!-- BANNER HERO -->
<div class="position-relative bg-dark py-5 overflow-hidden">
    <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(135deg, #1a1a1a 0%, #000 100%); opacity: 0.9;"></div>
    <div class="container position-relative z-2 text-center text-white">
        <h6 class="text-primary fw-bold text-uppercase ls-2 mb-2">Soporte Técnico & Ventas</h6>
        <h1 class="display-4 fw-black">CONTÁCTANOS</h1>
        <p class="text-white-50">Estamos listos para asesorarte con ingeniería de precisión.</p>
    </div>
</div>

<div class="container contacto py-5">
    <div class="row g-5">

        <!-- INFORMACIÓN (Izquierda) -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-black text-white py-3 border-bottom border-secondary">
                    <h5 class="m-0 fw-bold"><i class="bi bi-geo-alt-fill text-primary me-2"></i> SEDE PRINCIPAL</h5>
                </div>
                <div class="card-body p-4">
                    <!-- Dirección -->
                    <div class="d-flex mb-4">
                        <div class="flex-shrink-0">
                            <i class="bi bi-pin-map-fill fs-3 text-dark"></i>
                        </div>
                        <div class="ms-3">
                            <h6 class="fw-bold mb-1">Lima, Perú</h6>
                            <p class="text-muted mb-0 small">Los Melocotones, Los Olivos 15307 Perú</p>
                            <small class="text-success fw-bold"><i class="bi bi-clock"></i> Lun-Sab: 8am - 6pm</small>
                        </div>
                    </div>

                    <!-- Teléfonos -->
                    <div class="d-flex mb-4">
                        <div class="flex-shrink-0">
                            <i class="bi bi-whatsapp fs-3 text-success"></i>
                        </div>
                        <div class="ms-3">
                            <h6 class="fw-bold mb-1">Ventas & Soporte</h6>
                            <a href="https://wa.me/51902010281" target="_blank" class="text-decoration-none text-dark fs-5 fw-black hover-orange">
                                902 010 281
                            </a>
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <i class="bi bi-envelope-at-fill fs-3 text-primary"></i>
                        </div>
                        <div class="ms-3">
                            <h6 class="fw-bold mb-1">Correo Corporativo</h6>
                            <p class="text-muted mb-0 hover-orange">ventas@maquimpower.com</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mapa -->
            <div class="rounded-4 overflow-hidden shadow-lg border border-2 border-white">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3903.3089495553436!2d-77.0828897!3d-11.953098599999999!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x9105d1bacd01ab21%3A0xb880a7b8074e1d31!2sMaquimpower!5e0!3m2!1ses!2spe!4v1768791539446!5m2!1ses!2spe" width="100%" height="250" style="border:0; filter: grayscale(20%);" allowfullscreen="" loading="lazy"></iframe>
            </div>
        </div>

        <!-- FORMULARIO (Derecha - Estilo Industrial) -->
        <div class="col-lg-7">
            <div class="checkout-card h-100">
                <h3 class="fw-black mb-4">ENVÍANOS UN MENSAJE</h3>

                <form id="contactForm">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-floating-custom">
                                <input type="text" class="form-control-modern" placeholder=" " required>
                                <label class="form-label-modern">NOMBRE COMPLETO</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating-custom">
                                <input type="email" class="form-control-modern" placeholder=" " required>
                                <label class="form-label-modern">CORREO ELECTRÓNICO</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating-custom">
                                <input type="tel" class="form-control-modern" placeholder=" " required>
                                <label class="form-label-modern">CELULAR / WHATSAPP</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating-custom">
                                <textarea class="form-control-modern" placeholder=" " style="height: 150px;" required></textarea>
                                <label class="form-label-modern">¿CÓMO PODEMOS AYUDARTE?</label>
                            </div>
                        </div>
                        <div class="col-12 text-end">
                            <button type="button" class="btn-pay-glow w-50" onclick="enviarContacto()">
                                ENVIAR MENSAJE <i class="bi bi-send-fill ms-2"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<script>
    function enviarContacto() {
        Swal.fire({
            icon: 'success',
            title: '¡MENSAJE ENVIADO!',
            text: 'Un asesor comercial te contactará en breve.',
            confirmButtonColor: '#FF4500',
            confirmButtonText: 'ENTENDIDO',
            background: '#fff',
            customClass: {
                title: 'popup-title',
                text: 'popup-text',
                popup: 'popup-contacto',
                confirmButton: 'btn-confirm-contacto'
            }
        });
        document.getElementById('contactForm').reset();
    }
</script>

<style>
    .hover-orange:hover {
        color: var(--primary) !important;
        transition: 0.3s;
    }
</style>

<?php require_once 'includes/footer.php'; ?>