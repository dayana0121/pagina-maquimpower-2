<?php
$pageTitle = "Quiénes Somos | MaquimPower";
require_once 'includes/header.php';
?>

<div class="main-content">
    
    <!-- HERO -->
    <section class="hero-nosotros text-white py-5 mb-5">
        <div class="container position-relative z-1 text-center py-5">
            <h1 class="display-3 fw-black text-uppercase reveal">Impulsando tu <br><span class="text-primary">Negocio</span></h1>
            <p class="lead opacity-75 reveal" style="animation-delay: 0.2s;">Maquinaria profesional con potencia peruana.</p>
        </div>
    </section>

    <div class="container nosotros">
        
        <!-- INTRODUCCIÓN (TEXTO ACTUALIZADO) -->
        <div class="row align-items-center g-5 mb-5">
            <div class="col-lg-6 reveal">
                <h2 class="fw-black text-uppercase mb-4">Quiénes <span class="text-primary">Somos</span></h2>
                <p class="text-muted">En Maquimpower, somos una empresa peruana especializada en la venta de maquinaria y equipos profesionales para lavado de autos y limpieza industrial. Desde nuestros inicios, nos hemos comprometido a ofrecer soluciones integrales que combinan tecnología, durabilidad y eficiencia para cubrir las necesidades de talleres, centros CarWash, negocios de limpieza y empresas en todo el país.</p>
                <p class="text-muted">Contamos con un portafolio de productos que incluye hidrolavadoras de alta presión, aspiradoras industriales, shampooneras, compresores, generadores, repuestos y más, todos seleccionados bajo rigurosos estándares de calidad. Nuestro enfoque técnico y nuestra atención personalizada nos han permitido consolidarnos como un referente confiable en el sector.</p>
                <p class="text-muted">Brindamos asesoría especializada antes y después de la venta, asegurando que cada cliente obtenga la mejor solución según su tipo de operación. En Maquimpower, la eficiencia, el servicio y la confianza son pilares de nuestro trabajo diario.</p>
            </div>
            <div class="col-lg-6 reveal" style="animation-delay: 0.3s;">
                <div class="position-relative">
                    <img src="assets/img/logo_mp/Logo_3.jpg" class="img-fluid rounded-4 shadow-lg border-5 border-white border" alt="Maquimpower">
                    <div class="position-absolute bottom-0 end-0 bg-primary text-white p-4 rounded-4 shadow-lg d-none d-md-block" style="transform: translate(20px, 20px);">
                        <h4 class="fw-bold mb-0">+1000</h4>
                        <small>Clientes Satisfechos</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- MISIÓN Y VISIÓN (TEXTO ACTUALIZADO) -->
        <div class="row g-4 mb-5">
            <!-- VISIÓN (CON NEÓN NARANJA) -->
            <div class="col-md-6 reveal">
                <div class="p-5 bg-white shadow-sm rounded-4 border-neon h-100">
                    <h3 class="fw-bold text-uppercase mb-3 text-dark">Visión</h3>
                    <p class="text-muted m-0">Ser reconocidos como la empresa líder en Perú en la distribución de maquinaria profesional para limpieza y lavado de autos, impulsando la transformación del sector mediante soluciones tecnológicas, sostenibles y altamente eficientes que mejoren la productividad de nuestros clientes.</p>
                </div>
            </div>
            <!-- MISIÓN -->
            <div class="col-md-6 reveal" style="animation-delay: 0.2s;">
                <div class="p-5 bg-dark text-white shadow-sm rounded-4 h-100 position-relative overflow-hidden">
                    <div class="position-absolute top-0 end-0 opacity-10 p-3">
                        <i class="bi bi-rocket-takeoff-fill display-1"></i>
                    </div>
                    <h3 class="fw-bold text-uppercase mb-3 text-primary">Misión</h3>
                    <p class="opacity-75 m-0 mb-3">En Maquimpower, trabajamos para proveer soluciones de limpieza profesional que optimicen el rendimiento y los resultados de nuestros clientes. Ofrecemos equipos de última tecnología, atención personalizada y un servicio postventa eficiente, ayudando a talleres, empresas y centros de lavado a lograr una limpieza impecable, segura y sostenible.</p>
                    <p class="opacity-75 m-0">Nos comprometemos a ser aliados estratégicos de cada negocio, aportando no solo productos de calidad, sino también confianza, asesoría y soporte técnico especializado.</p>
                </div>
            </div>
        </div>

        <!-- VALORES (TEXTOS ACTUALIZADOS Y ALINEACIÓN) -->
        <section class="py-5">
            <div class="text-center mb-5 reveal">
                <h2 class="fw-black text-uppercase">Nuestros <span class="text-primary">Valores</span></h2>
                <div class="mx-auto bg-primary mt-2" style="width: 60px; height: 4px;"></div>
            </div>

            <!-- justify-content-center para que la fila de abajo (3 items) quede centrada -->
            <div class="row g-4 justify-content-center">
                <?php 
                $valores = [
                    ['Compromiso', 'bi-shield-check', 'Nos dedicamos a cumplir con responsabilidad cada promesa hecha a nuestros clientes y aliados.'],
                    ['Honestidad', 'bi-info-circle', 'Trabajamos con transparencia en cada venta, recomendación y servicio que brindamos.'],
                    ['Innovación', 'bi-cpu', 'Apostamos por la mejora continua, incorporando equipos modernos y soluciones adaptadas a las nuevas exigencias.'],
                    ['Excelencia', 'bi-award', 'Buscamos siempre superar las expectativas a través de productos confiables y atención de calidad.'],
                    ['Servicio al cliente', 'bi-person-heart', 'Cada cliente es una prioridad. Escuchamos sus necesidades y ofrecemos soluciones efectivas y personalizadas.'],
                    ['Sostenibilidad', 'bi-tree', 'Promovemos el uso responsable de recursos y tecnologías que minimicen el impacto ambiental.'],
                    ['Trabajo en equipo', 'bi-people', 'Fomentamos un ambiente de colaboración dentro y fuera de la empresa para lograr objetivos comunes.']
                ];
                $delay = 0;
                foreach($valores as $v): $delay += 0.1; ?>
                <div class="col-md-4 col-lg-3 reveal" style="animation-delay: <?= $delay ?>s;">
                    <div class="card card-valor h-100 p-4 shadow-sm text-center">
                        <div class="icon-box mx-auto"><i class="bi <?= $v[1] ?>"></i></div>
                        <h5 class="fw-bold"><?= $v[0] ?></h5>
                        <p class="small text-muted mb-0"><?= $v[2] ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- CTA FINAL (TEXTO ACTUALIZADO Y ICONOS REDONDOS) -->
        <section class="py-5 mt-5">
            <div class="container">
                <div class="bg-dark rounded-5 overflow-hidden shadow-lg position-relative border border-secondary border-opacity-25 reveal">
                    <div class="position-absolute top-0 end-0 p-5 opacity-10 d-none d-lg-block">
                        <i class="bi bi-gear-wide-connected text-primary" style="font-size: 12rem; transform: rotate(15deg);"></i>
                    </div>

                    <div class="row g-0 align-items-stretch position-relative z-1">
                        <div class="col-lg-7 p-4 p-md-5">
                            <h2 class="display-6 fw-black text-white text-uppercase mb-3">¿Listo para potenciar <br><span class="text-primary">tu negocio?</span></h2>
                            <p class="text-white-50 mb-4">En Maquimpower, más que vender maquinaria, impulsamos negocios. Ya sea que busques hidrolavadoras, aspiradoras industriales, shampooneras o accesorios, estamos aquí para ayudarte a crecer con tecnología confiable y envío rápido a todo el Perú.</p>
                            
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <div class="d-flex align-items-center gap-3 p-3 rounded-4 bg-white bg-opacity-5 border border-white border-opacity-10">
                                        <div class="feature-icon-circle bg-primary text-white shadow-sm">
                                            <i class="bi bi-truck"></i>
                                        </div>
                                        <div>
                                            <h6 class="text-black fw-bold mb-0 small">Envíos Nacionales</h6>
                                            <small class="text-black-50">Logística rápida y segura</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="d-flex align-items-center gap-3 p-3 rounded-4 bg-white bg-opacity-5 border border-white border-opacity-10">
                                        <div class="feature-icon-circle bg-success text-white shadow-sm">
                                            <i class="bi bi-headset"></i>
                                        </div>
                                        <div>
                                            <h6 class="text-black fw-bold mb-0 small">Soporte Experto</h6>
                                            <small class="text-black-50">Asesoría especializada</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-5 bg-primary p-5 d-flex flex-column justify-content-center align-items-center text-center">
                            <div class="bg-white p-3 rounded-4 shadow-sm mb-4 d-none d-lg-block">
                                <img src="assets/img/logo_mp/MaquimPower_Logotipo_Agosto.png" width="160" alt="Logo">
                            </div>
                            <h4 class="text-dark fw-black mb-4">MÁS QUE MAQUINARIA, <br>IMPULSAMOS TU ÉXITO</h4>
                            <button onclick="contactarAsesoria()" class="btn btn-dark btn-lg rounded-pill px-5 py-3 fw-bold w-100 shadow-lg border-0 hover-scale">
                                <i class="bi bi-whatsapp me-2"></i> ¡ASESORÍA GRATIS!
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<!-- SCRIPT ALERTAS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function contactarAsesoria() {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true,
            background: '#000',
            color: '#fff',
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        Toast.fire({
            icon: 'success',
            title: 'Abriendo WhatsApp...'
        });

        setTimeout(() => {
            window.open('https://wa.me/51902010281?text=Hola%20MaquimPower,%20le%C3%AD%20su%20web%20y%20quisiera%20asesor%C3%ADa.', '_blank');
        }, 1000);
    }
</script>

<?php require_once 'includes/footer.php'; ?>