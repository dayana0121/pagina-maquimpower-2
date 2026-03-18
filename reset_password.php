<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

$token = $_GET['token'] ?? '';
$mensaje = '';
$tipoMsg = '';

// 1. Validar Token
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE reset_token = ? AND reset_expires > NOW()");
$stmt->execute([$token]);
$usuario = $stmt->fetch();

if (!$usuario) {
    $mensaje = "El enlace es inválido o ha expirado.";
    $tipoMsg = "danger";
}

// 2. Procesar Cambio
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $usuario) {
    $pass = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if ($pass === $confirm) {
        $hash = password_hash($pass, PASSWORD_BCRYPT);
        // Actualizar clave y borrar token
        $upd = $pdo->prepare("UPDATE usuarios SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
        $upd->execute([$hash, $usuario['id']]);

        $mensaje = "¡Contraseña actualizada! Ya puedes iniciar sesión.";
        $tipoMsg = "success";
        // Ocultar formulario
        $usuario = false;
    } else {
        $mensaje = "Las contraseñas no coinciden.";
        $tipoMsg = "warning";
    }
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-header bg-dark text-white text-center py-4">
                    <h4 class="fw-bold m-0">NUEVA CONTRASEÑA</h4>
                </div>
                <div class="card-body p-5">

                    <?php if ($mensaje): ?>
                        <div class="alert alert-<?php echo $tipoMsg; ?> text-center mb-4">
                            <?php echo $mensaje; ?>
                        </div>
                        <?php if ($tipoMsg == 'success'): ?>
                            <a href="login.php" class="btn btn-dark w-100 fw-bold">IR AL LOGIN</a>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if ($usuario): ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="fw-bold small">Nueva Contraseña</label>
                                <input type="password" name="password" class="form-control form-control-lg" minlength="6" required>
                            </div>

                            <div class="mb-4">
                                <label class="fw-bold small">Confirmar Contraseña</label>
                                <input type="password" name="confirm_password" class="form-control form-control-lg" minlength="6" required>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 btn-lg fw-bold">GUARDAR CAMBIOS</button>
                        </form>
                    <?php elseif ($tipoMsg == 'danger'): ?>
                        <a href="recuperar.php" class="btn btn-outline-dark w-100">SOLICITAR NUEVO LINK</a>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>