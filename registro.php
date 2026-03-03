<?php require_once 'includes/header.php'; ?>

<!-- SEGURIDAD: Si ya estás dentro, al Home -->
<?php
if (isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='/index.php';</script>";
    exit;
}
?>

<style>
    /* Reutilizamos el estilo del Login para consistencia */
    .auth-bg {
        background: url('https://images.unsplash.com/photo-1504328345606-18bbc8c9d7d1?q=80&w=2070&auto=format&fit=crop') no-repeat center center;
        background-size: cover;
        min-height: 90vh; /* Un poco más alto por ser registro */
        display: flex;
        align-items: center;
        position: relative;
    }
    .auth-bg::before {
        content: '';
        position: absolute; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0, 0, 0, 0.75); /* Un poco más oscuro */
        backdrop-filter: blur(5px);
    }
    
    .auth-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 25px 50px rgba(0,0,0,0.3);
        position: relative;
        z-index: 2;
        overflow: hidden;
        border-top: 5px solid var(--primary);
    }

    .auth-header {
        text-align: center;
        padding: 30px 30px 10px 30px;
    }
</style>

<div class="auth-bg">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                
                <div class="auth-card animate-card">
                    
                    <!-- ENCABEZADO -->
                    <div class="auth-header">
                        <h2 class="fw-black text-uppercase m-0">Únete a Nosotros</h2>
                        <p class="text-muted small">Crea tu cuenta para gestionar pedidos y ofertas.</p>
                    </div>

                    <div class="card-body p-4 pt-2">
                        
                        <!-- FORMULARIO -->
                        <form action="/controllers/auth.php" method="POST">
                            <input type="hidden" name="action" value="register">
                            
                            <!-- FILA 1: NOMBRE Y APELLIDO -->
                            <div class="row g-3 mb-3">
                                <div class="col-6">
                                    <div class="form-floating-custom mb-0">
                                        <input type="text" name="nombre" class="form-control-modern" placeholder=" " required>
                                        <label class="form-label-modern">NOMBRE</label>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-floating-custom mb-0">
                                        <input type="text" name="apellido" class="form-control-modern" placeholder=" " required>
                                        <label class="form-label-modern">APELLIDO</label>
                                    </div>
                                </div>
                            </div>
                            <div class="row g-3 mb-3">
                                <div class="col-7">
                                    <div class="form-floating-custom mb-0">
                                        <input type="tel" name="telefono" class="form-control-modern" placeholder=" " pattern="[0-9+ ]+" title="Solo números" required>
                                        <label class="form-label-modern"><i class="bi bi-whatsapp me-1"></i> TELÉFONO</label>
                                    </div>
                                </div>

                            <!-- EMAIL -->
                            <div class="form-floating-custom mb-3">
                                <input type="email" name="email" class="form-control-modern" placeholder=" " required>
                                <label class="form-label-modern"><i class="bi bi-envelope me-1"></i> CORREO ELECTRÓNICO</label>
                            </div>
                            
                            
                            <!-- CONTRASEÑA -->
                            <div class="form-floating-custom mb-3">
                                <input type="password" name="password" class="form-control-modern" placeholder=" " minlength="6" required>
                                <label class="form-label-modern"><i class="bi bi-lock me-1"></i> CONTRASEÑA (MIN 6)</label>
                            </div>

                           <!-- CHECKBOX LEGAL (OBLIGATORIO) -->
                        <div class="mp-check-group mt-3">
                            <input type="checkbox" name="accept_legal" id="legal" class="mp-check-input" required>
                            <label for="legal" class="mp-check-label">
                                He leído y acepto la <a href="/politicas-de-privacidad.php" target="_blank">Política de Privacidad</a> y los <a href="/terminos.php" target="_blank">Términos y Condiciones</a> de MaquimPower.
                            </label>
                        </div>

                        <!-- CHECKBOX MARKETING (OPCIONAL) -->
                        <div class="mp-check-group mb-4">
                            <input type="checkbox" name="marketing" id="mkt" value="1" class="mp-check-input" checked>
                            <label for="mkt" class="mp-check-label">
                                Autorizo el envío de promociones exclusivas, cupones y novedades a mi correo. (Puedes cancelar cuando quieras).
                            </label>
                        </div>

                        <!-- BOTÓN POWER -->
                        <button type="submit" class="btn-pay-glow shadow-sm">
                            CREAR CUENTA <i class="bi bi-person-plus-fill ms-2"></i>
                        </button>
                        </form>
                    </div>

                    <!-- FOOTER TARJETA -->
                    <div class="bg-light p-3 text-center border-top">
                        <span class="small text-muted">¿Ya tienes cuenta?</span><br>
                        <a href="/login.php" class="fw-black text-dark text-decoration-none text-uppercase ls-1">
                            Iniciar Sesión
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>