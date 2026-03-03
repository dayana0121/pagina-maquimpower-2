<?php require_once 'includes/header.php'; ?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-body p-5 text-center">
                    <h3 class="fw-bold mb-3">¿Olvidaste tu contraseña?</h3>
                    <p class="text-muted mb-4">Ingresa tu correo y te enviaremos un enlace para restablecerla.</p>
                    
                    <form action="controllers/auth_recuperar.php" method="POST">
                        <div class="form-floating mb-3">
                            <input type="email" name="email" class="form-control" placeholder="name@example.com" required>
                            <label>Correo Electrónico</label>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-3 fw-bold rounded-pill">
                            ENVIAR ENLACE DE RECUPERACIÓN
                        </button>
                    </form>
                    <a href="login.php" class="d-block mt-4 text-muted small">Volver al Login</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>