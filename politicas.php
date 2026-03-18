<?php require_once 'includes/header.php'; ?>

<style>
    /* ESTILO LEGAL CORPORATIVO */
    .legal-header { 
        background: linear-gradient(135deg, #111 0%, #222 100%); 
        color: white; padding: 60px 0; margin-bottom: 40px; 
        border-bottom: 5px solid var(--primary);
    }
    
    .legal-sidebar { position: sticky; top: 120px; }
    
    .legal-nav .nav-link { 
        color: #555; font-weight: 600; padding: 15px 20px; 
        border-left: 4px solid transparent; transition: 0.3s;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .legal-nav .nav-link:hover, .legal-nav .nav-link.active { 
        color: var(--primary); 
        border-left-color: var(--primary); 
        background: white; 
        box-shadow: 5px 0 15px rgba(0,0,0,0.05);
        padding-left: 25px;
    }
    
    .policy-section { 
        background: white; padding: 40px; border-radius: 12px; 
        box-shadow: 0 5px 20px rgba(0,0,0,0.03); margin-bottom: 30px; 
        border: 1px solid #eee;
        scroll-margin-top: 100px; /* Para que el header no tape el título al hacer clic */
    }
    
    .policy-title { 
        font-weight: 900; color: #111; text-transform: uppercase; 
        border-bottom: 2px solid #f0f0f0; padding-bottom: 15px; margin-bottom: 25px; 
        font-size: 1.5rem; letter-spacing: -0.5px;
    }
    
    .policy-content h5 { 
        color: var(--primary); font-weight: 700; margin-top: 30px; margin-bottom: 15px; 
        font-size: 1.1rem; text-transform: uppercase; letter-spacing: 0.5px;
    }
    
    .policy-content p, .policy-content li { 
        color: #444; line-height: 1.8; font-size: 0.95rem; text-align: justify; 
    }
    .policy-content ul { padding-left: 20px; margin-bottom: 20px; }
    .policy-content li { margin-bottom: 8px; }
</style>

<!-- HEADER HERO -->
<div class="legal-header text-center">
    <div class="container">
        <h6 class="text-primary fw-bold ls-2 text-uppercase mb-2">Transparencia y Confianza</h6>
        <h1 class="display-4 fw-black">CENTRO DE INFORMACIÓN LEGAL</h1>
        <p class="text-white-50 m-0" style="max-width: 600px; margin: 0 auto;">
            Conoce tus derechos y nuestras políticas de servicio.
        </p>
    </div>
</div>

<div class="container pb-5">
    <div class="row g-5">
        
        <!-- SIDEBAR DE NAVEGACIÓN -->
        <div class="col-lg-3 d-none d-lg-block">
            <div class="legal-nav bg-light rounded-4 overflow-hidden p-3">
                <h5 class="fw-black px-3 mb-3 text-uppercase">Navegación</h5>
                <nav class="nav flex-column">
                    <a class="nav-link" href="politicas.php/#privacidad">1. Privacidad de Datos</a>
                    <a class="nav-link" href="politicas.php/#terminos">2. Términos y Condiciones</a>
                    <a class="nav-link" href="politicas.php/#cookies">3. Política de Cookies</a>
                    <a class="nav-link" href="politicas.php/#aviso">4. Aviso Legal</a>
                    <a class="nav-link" href="politicas.php/#cambios">5. Cambios y Devoluciones</a>
                    <a class="nav-link" href="politicas.php/#garantia">6. Garantía</a>
                    <a class="nav-link" href="politicas.php/#envios">7. Envíos</a>
                    <a class="nav-link" href="politicas.php/#pagos">8. Pagos y Comprobantes</a>
                </nav>
            </div>  
        </div>

        <!-- CONTENIDO -->
        <div class="col-lg-9">
            
            <!-- 1. PRIVACIDAD -->
            <section id="privacidad" class="policy-section">
                <h2 class="policy-title"><i class="bi bi-shield-lock-fill text-primary me-2"></i> Política de Privacidad</h2>
                <div class="policy-content">
                    <h5>1. Identidad del responsable</h5>
                    <p><strong>Corporacion Maquimsa E.I.R.L.</strong>, identificada con RUC N.º 20606853182 ("Maquimpower"), es titular del sitio web y responsable del tratamiento de los datos personales conforme a la Ley N.º 29733.</p>
                    
                    <h5>2. Marco legal</h5>
                    <p>El tratamiento de los datos personales se realiza de acuerdo con:</p>
                    <ul>
                        <li>Ley N.º 29733 – Ley de Protección de Datos Personales.</li>
                        <li>Decreto Supremo N.º 003-2013-JUS – Reglamento de la Ley.</li>
                    </ul>

                    <h5>3. Datos recopilados</h5>
                    <p>Recopilamos: Nombres y apellidos, RUC/DNI, Correo electrónico, Dirección fiscal/entrega y Número telefónico. El usuario declara que la información es veraz y actual.</p>

                    <h5>4. Finalidad</h5>
                    <p>Los datos se usan para: Atender solicitudes, gestionar pedidos, cumplir obligaciones contractuales, enviar promociones (previo consentimiento) y mejorar el servicio.</p>

                    <h5>5. Derechos ARCO</h5>
                    <p>El titular puede ejercer sus derechos de Acceso, Rectificación, Cancelación y Oposición comunicándose a través de nuestros canales oficiales.</p>
                    
                    <h5>6. Seguridad y Transferencia</h5>
                    <p>Adoptamos medidas técnicas para proteger sus datos. No compartimos datos con terceros salvo obligación legal o consentimiento expreso.</p>
                </div>
            </section>

            <!-- 2. TÉRMINOS -->
            <section id="terminos" class="policy-section">
                <h2 class="policy-title"><i class="bi bi-file-text-fill text-primary me-2"></i> Términos y Condiciones</h2>
                <div class="policy-content">
                    <h5>1. Aceptación y Uso</h5>
                    <p>El acceso al sitio web implica la aceptación plena de estos términos. Queda prohibido el uso fraudulento, introducción de malware o alteración del sitio.</p>

                    <h5>2. Información de Productos</h5>
                    <p>Nos esforzamos por la precisión, pero las imágenes, descripciones o precios pueden estar sujetos a cambios sin previo aviso.</p>

                    <h5>3. Propiedad Intelectual</h5>
                    <p>Todos los contenidos son propiedad de Corporacion Maquimsa E.I.R.L. Queda prohibida su reproducción total o parcial sin autorización.</p>

                    <h5>4. Responsabilidad</h5>
                    <p>No nos responsabilizamos por interrupciones técnicas, uso indebido del sitio por parte del usuario o contenidos de enlaces externos.</p>
                    
                    <h5>5. Legislación</h5>
                    <p>Estos términos se rigen por las leyes de la República del Perú.</p>
                </div>
            </section>

            <!-- 3. COOKIES -->
           <section id="cookies" class="policy-section">
                <h2 class="policy-title"><i class="bi bi-cookie-fill text-primary me-2"></i> Política de Cookies</h2>
                <div class="policy-content">
                    <p>Utilizamos cookies para mejorar la experiencia de navegación.</p>
                    <ul>
                        <li><strong>Cookies técnicas:</strong> Necesarias para el funcionamiento (carrito, sesión).</li>
                        <li><strong>Cookies de análisis:</strong> Para analizar el comportamiento de navegación.</li>
                        <li><strong>Cookies de preferencias:</strong> Recuerdan configuraciones del usuario.</li>
                    </ul>
                    <p>Puede gestionar o desactivar las cookies desde la configuración de su navegador.</p>
                </div>
            </section>

            <!-- 4. AVISO LEGAL -->
            <section id="aviso" class="policy-section">
                <h2 class="policy-title"><i class="bi bi-building-fill text-primary me-2"></i> Aviso Legal</h2>
                <div class="policy-content">
                    <div class="bg-light p-3 rounded border">
                        <ul class="list-unstyled m-0">
                            <li><strong>Razón Social:</strong> Corporacion Maquimsa E.I.R.L.</li>
                            <li><strong>RUC:</strong> 20606853182</li>
                            <li><strong>Nombre Comercial:</strong> Maquimpower</li>
                            <li><strong>País:</strong> Perú</li>
                        </ul>
                    </div>
                    <p class="mt-3">Maquimpower no garantiza la disponibilidad permanente del sitio web ni se hace responsable por fallos técnicos ajenos a su control.</p>
                </div>
            </section>

            <!-- 5. CAMBIOS Y DEVOLUCIONES -->
            <section id="cambios" class="policy-section">
                <h2 class="policy-title"><i class="bi bi-arrow-repeat text-primary me-2"></i> Cambios y Devoluciones</h2>
                <div class="policy-content">
                    <h5>1. Cambios de Producto</h5>
                    <p>Plazo máximo de <strong>7 días calendario</strong> desde la entrega, condiciones:</p>
                    <ul>
                        <li>Producto nuevo, sin uso ni instalación.</li>
                        <li>Empaque original, accesorios y manuales completos.</li>
                        <li>Presentar comprobante de pago.</li>
                    </ul>
                    <p>Gastos de transporte por cuenta del cliente, salvo error de Maquimpower.</p>

                    <h5>2. Devoluciones</h5>
                    <p>Solo se aceptan por defecto de fábrica o entrega incorrecta. No se aceptan devoluciones por error de compra o incompatibilidad.</p>
                    
                    <h5>3. Procedimiento</h5>
                    <p>Comunicarse a los canales oficiales indicando número de pedido, motivo y evidencia (fotos/video).</p>
                </div>
            </section>

            <!-- 6. GARANTÍA -->
            <section id="garantia" class="policy-section">
                <h2 class="policy-title"><i class="bi bi-award-fill text-primary me-2"></i> Política de Garantía</h2>
                <div class="policy-content">
                    <h5>1. Cobertura</h5>
                    <p>Aplica a productos comercializados por Maquimpower según condiciones del fabricante. El plazo se indica en la ficha o comprobante.</p>

                    <h5>2. Condiciones</h5>
                    <p>Presentar comprobante. El producto no debe presentar signos de mal uso, golpes, ni haber sido manipulado por terceros no autorizados.</p>

                    <h5>3. Exclusiones</h5>
                    <p>No cubre desgaste normal, caídas, mala instalación, ni daños eléctricos por variaciones de voltaje.</p>
                </div>
            </section>

            <!-- 7. ENVÍOS -->
           <section id="envios" class="policy-section">
                <h2 class="policy-title"><i class="bi bi-truck-front-fill text-primary me-2"></i> Política de Envíos</h2>
                <div class="policy-content">
                    <h5>1. Plazos de Despacho</h5>
                    <ul>
                        <li><strong>Lima Metropolitana:</strong> 24 a 72 horas hábiles.</li>
                        <li><strong>Provincias:</strong> Según el operador logístico seleccionado.</li>
                    </ul>

                    <h5>2. Recepción del Pedido</h5>
                    <p>El cliente debe proporcionar datos exactos. Es responsabilidad del cliente revisar el producto al momento de la entrega.</p>

                    <!-- PUNTO 3 AGREGADO -->
                    <h5>3. Costos de envío</h5>
                    <p>El costo de envío se calcula según el destino del pedido.</p>
                    <ul>
                        <li><strong>En Lima Metropolitana:</strong> El envío se cobra en función de la distancia desde nuestro punto de despacho.</li>
                        <li><strong>A provincias:</strong> El costo depende del peso y volumen del producto, según el operador logístico.</li>
                    </ul>
                    <p>El monto final será informado antes de la confirmación del pedido.</p>
                </div>
            </section>

            <!-- 8. PAGOS -->
            <section id="pagos" class="policy-section">
                <h2 class="policy-title"><i class="bi bi-credit-card-2-back-fill text-primary me-2"></i> Medios de Pago y Comprobantes</h2>
                <div class="policy-content">
                    <h5>1. Medios Aceptados</h5>
                    <p>Transferencias bancarias, Depósitos en cuenta, Pagos digitales (Yape, Plin) y otros coordinados.</p>

                    <h5>2. Comprobantes Electrónicos</h5>
                    <p>Emitimos Boleta y Factura Electrónica válidas ante SUNAT. Para facturas, es obligatorio proporcionar RUC y Razón Social válidos.</p>
                </div>
            </section>

        </div>
    </div>
</div>

<script>
    // Script para resaltar la sección activa en el menú lateral
    window.addEventListener('scroll', function() {
        let sections = document.querySelectorAll('.policy-section');
        let navLinks = document.querySelectorAll('.legal-nav .nav-link');
        
        sections.forEach(sec => {
            let top = window.scrollY;
            let offset = sec.offsetTop - 150;
            let height = sec.offsetHeight;
            let id = sec.getAttribute('id');
            
            if(top >= offset && top < offset + height) {
                navLinks.forEach(link => {
                    link.classList.remove('active');
                    if(link.getAttribute('href').includes(id)) {
                        link.classList.add('active');
                    }
                });
            }
        });
    });
</script>

<?php require_once 'includes/footer.php'; ?>