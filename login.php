<?php require_once 'includes/header.php'; ?>

<!-- SEGURIDAD: Si ya estás dentro, al Home -->
<?php
if (isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='/index.php';</script>";
    exit;
}
?>

<div class="auth-bg-l">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">

                <div class="auth-card-l animate-card">

                    <!-- ENCABEZADO -->
                    <div class="auth-header">
                        <h2 class="fw-black text-uppercase m-0">Bienvenido</h2>
                        <p class="text-muted small">Ingresa a tu cuenta MaquimPower</p>
                    </div>

                    <div class="card-body p-4 pt-2">

                        <!-- MENSAJES DE ERROR PHP -->
                        <?php if (isset($_GET['error'])): ?>
                            <div class="alert alert-danger d-flex align-items-center small p-2 mb-3">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <div>Correo o contraseña incorrectos.</div>
                            </div>
                        <?php endif; ?>

                        <form action="controllers/auth.php" method="POST">
                            <input type="hidden" name="action" value="login">

                            <!-- INPUTS MODERNOS -->
                            <div class="form-floating-custom mb-3">
                                <input type="email" name="email" class="form-control-modern" placeholder=" " required>
                                <label class="form-label-modern"><i class="bi bi-envelope me-1"></i> CORREO
                                    ELECTRÓNICO</label>
                            </div>

                            <div class="form-floating-custom mb-3 password-wrapper">
                                <input type="password" id="passwordInput" name="password"
                                    class="form-control-modern" placeholder=" " minlength="6" required>
                                <label class="form-label-modern">
                                    <i class="bi bi-lock me-1"></i> CONTRASEÑA (MIN 6)
                                </label>
                                <!-- Botón ojito -->
                                <button type="button" class="toggle-password">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>

                            <!-- BOTÓN POWER -->
                            <button type="submit" class="btn-pay-glow shadow-sm">
                                INGRESAR AHORA <i class="bi bi-arrow-right-short"></i>
                            </button>

                            <!-- OPCIONES EXTRA -->
                            <div class="d-flex justify-content-between align-items-center mt-3 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="remember" name="remember">
                                    <label class="form-check-label small text-muted" for="remember">Recordarme</label>
                                </div>
                                <a href="/recuperar.php" class="small fw-bold text-primary text-decoration-none">
                                    ¿Olvidaste tu clave?
                                </a>
                            </div>
                        </form>
                    </div>

                    <!-- FOOTER TARJETA -->
                    <div class="bg-light p-3 text-center border-top">
                        <span class="small text-muted">¿Aún no tienes cuenta?</span><br>
                        <a href="/registro.php" class="fw-black text-dark text-decoration-none text-uppercase ls-1">
                            Crear Cuenta Nueva
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- SCRIPT PARA ALERTAS JS (El que hicimos antes) -->
<?php if (isset($_GET['msg']) && $_GET['msg'] == 'login_required'): ?>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            Notify.info('ACCESO REQUERIDO', 'Para procesar tu compra, inicia sesión o regístrate.');
        });
    </script>
<?php endif; ?>

<!--SCRIPT PARA MOSTRAR CONTRASEÑA-->
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const togglePassword = document.querySelector('.toggle-password');
        const passwordInput = document.getElementById('passwordInput');

        togglePassword.addEventListener('click', function() {
            const type = passwordInput.type === 'password' ? 'text' : 'password';
            passwordInput.type = type;

            // Cambiar icono
            const icon = this.querySelector('i');
            icon.classList.toggle('bi-eye');
            icon.classList.toggle('bi-eye-slash');
        });
    });
</script>

<?php require_once 'includes/footer.php'; ?>