<?php
ob_start();
session_start();

// Conexión
require_once dirname(__DIR__) . '/includes/db.php';

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

// --- LOGIN ---
if ($action == 'login') {
    $email = trim($_POST['email']);
    $pass = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($pass, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['nombre'];
        $_SESSION['user_role'] = $user['rol'];

        // Redirección
        if ($user['rol'] == 'admin') {
            header("Location: /admin/dashboard.php");
        } else {
            header("Location: /index.php");
        }
        exit;
    } else {
        header("Location: /login.php?error=credenciales");
        exit;
    }
}

// --- REGISTRO ---
elseif ($action == 'register') {
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $email = trim($_POST['email']);
    $pass = $_POST['password'];
    $marketing = isset($_POST['marketing']) ? 1 : 0;

    // Verificar si existe
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        header("Location: /registro.php?error=existe");
        exit;
    }

    // Crear
    $hash = password_hash($pass, PASSWORD_BCRYPT);
    $sql = "INSERT INTO usuarios (nombre, apellido, email, password, rol, marketing_opt_in) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    if ($stmt->execute([$nombre, $apellido, $email, $hash, 'cliente', $marketing])) {
        $_SESSION['user_id'] = $pdo->lastInsertId();
        $_SESSION['user_name'] = $nombre;
        $_SESSION['user_role'] = 'cliente';

        // ENVIAR CORREO DE BIENVENIDA Y CAPTURAR RESULTADO
        require_once dirname(__DIR__) . '/includes/mailer.php';
        $mailEnviado = enviarCorreoBienvenida($email, $nombre);

        // REDIRECCIÓN CON MENSAJE DE ESTADO
        if ($mailEnviado) {
            header("Location: /index.php?registro=exito&mail=ok");
        } else {
            header("Location: /index.php?registro=exito&mail=error");
        }
        exit;
    } else {
        header("Location: /registro.php?error=db");
        exit;
    }
}

// --- LOGOUT ---
elseif ($action == 'logout') {
    session_destroy();
    header("Location: /index.php");
    exit;
} else {
    header("Location: /login.php");
    exit;
}
?>