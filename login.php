<?php require_once 'includes/header.php'; ?>

<!-- SEGURIDAD: Si ya estás dentro, al Home -->
<?php
if (isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='/pagina/index.php';</script>";
    exit;
}
?>

<style>
    /* Estilos exclusivos para Login/Registro */
    .auth-bg {
        background: url('https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?q=80&w=2070&auto=format&fit=crop') no-repeat center center;
        background-size: cover;
        min-height: 85vh;
        display: flex;
        align-items: center;
        position: relative;
    }

    /* Capa oscura sobre la imagen */
    .auth-bg::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        /* Oscuridad al 70% */
        backdrop-filter: blur(5px);
    }

    .auth-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
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
            <div class="col-md-5 col-lg-4">

                <div class="auth-card animate-card">

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

                            <div class="form-floating-custom mb-4">
                                <input type="password" name="password" class="form-control-modern" placeholder=" "
                                    required>
                                <label class="form-label-modern"><i class="bi bi-lock me-1"></i> CONTRASEÑA</label>
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
                                <a href="/pagina/recuperar.php" class="small fw-bold text-primary text-decoration-none">
                                    ¿Olvidaste tu clave?
                                </a>
                            </div>
                        </form>
                    </div>

                    <!-- FOOTER TARJETA -->
                    <div class="bg-light p-3 text-center border-top">
                        <span class="small text-muted">¿Aún no tienes cuenta?</span><br>
                        <a href="/pagina/registro.php" class="fw-black text-dark text-decoration-none text-uppercase ls-1">
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

<?php require_once 'includes/footer.php'; ?>