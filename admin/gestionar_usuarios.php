<?php
require_once 'check_auth.php';
require_once '../includes/db.php';

$msg = '';
$err = '';

// --- ACCIONES CRUD ---

// 1. CREAR / EDITAR USUARIO
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {

    if ($_POST['action'] == 'save_user') {
        $id_user = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
        $nombre = trim($_POST['nombre']);
        $apellido = trim($_POST['apellido']);
        $email = trim($_POST['email']);
        $rol = $_POST['rol'];
        $pass = $_POST['password'];

        // Validar Email (Simple)
        if ($id_user == 0) {
            // Check si existe email
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $err = "El correo ya está registrado.";
            } else {
                // INSERT
                $hash = password_hash($pass, PASSWORD_BCRYPT);
                $sql = "INSERT INTO usuarios (nombre, apellido, email, password, rol, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
                $pdo->prepare($sql)->execute([$nombre, $apellido, $email, $hash, $rol]);
                $msg = "Usuario creado correctamente.";
            }
        } else {
            // UPDATE
            // Si password no está vacío, actualizarlo
            if (!empty($pass)) {
                $hash = password_hash($pass, PASSWORD_BCRYPT);
                $sql = "UPDATE usuarios SET nombre=?, apellido=?, email=?, rol=?, password=? WHERE id=?";
                $pdo->prepare($sql)->execute([$nombre, $apellido, $email, $rol, $hash, $id_user]);
            } else {
                $sql = "UPDATE usuarios SET nombre=?, apellido=?, email=?, rol=? WHERE id=?";
                $pdo->prepare($sql)->execute([$nombre, $apellido, $email, $rol, $id_user]);
            }
            $msg = "Usuario actualizado correctamente.";
        }
    }
}

// 2. ELIMINAR
if (isset($_GET['delete'])) {
    $id_del = intval($_GET['delete']);
    // Evitar auto-borrado
    if ($id_del != $_SESSION['user_id']) {
        $pdo->prepare("DELETE FROM usuarios WHERE id=?")->execute([$id_del]);
        header("Location: gestionar_usuarios.php?msg=deleted");
        exit;
    } else {
        $err = "No puedes eliminar tu propia cuenta.";
    }
}

if (isset($_GET['msg']) && $_GET['msg'] == 'deleted') {
    $msg = "Usuario eliminado.";
}

// OBTENER USUARIOS
// OBTENER USUARIOS CON FILTROS
$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$rolFilter = isset($_GET['rol']) ? trim($_GET['rol']) : '';

$whereClauses = ["1=1"];
$params = [];

if ($search) {
    $whereClauses[] = "(nombre LIKE :q OR email LIKE :q)";
    $params[':q'] = "%$search%";
}

if ($rolFilter == 'abandonado') {
    // Usuarios que tienen pedidos PENDIENTES pero NINGUNO pagado/entregado
    $whereClauses[] = "id IN (SELECT usuario_id FROM pedidos WHERE estado = 'pendiente') 
                       AND id NOT IN (SELECT usuario_id FROM pedidos WHERE estado IN ('pagado', 'enviado', 'entregado'))";
} elseif ($rolFilter) {
    $whereClauses[] = "rol = :rol";
    $params[':rol'] = $rolFilter;
}

$whereSql = implode(" AND ", $whereClauses);

$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE $whereSql ORDER BY id DESC");
$stmt->execute($params);
$usuarios = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <title>Gestión de Usuarios | MaquimAdmin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>

<body>

    <div class="admin-header">
        <div class="container d-flex justify-content-between align-items-center">
            <div>
                <h2 class="m-0 fw-bold"><i class="bi bi-people-fill me-2"></i> GESTIÓN DE USUARIOS</h2>
                <p class="m-0 text-white-50 small">Administra clientes, administradores y permisos</p>
            </div>
            <a href="dashboard.php" class="btn btn-outline-light rounded-pill px-4">Volver</a>
        </div>
    </div>

    <div class="container py-4">

        <!-- ALERTAS -->
        <?php if ($msg): ?>
            <div class="alert alert-success alert-dismissible fade show fw-bold" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> <?php echo $msg; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if ($err): ?>
            <div class="alert alert-danger alert-dismissible fade show fw-bold" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo $err; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- TOOLBAR -->
        <div class="row mb-4 align-items-center">
            <div class="col-md-6">
                <div class="mb-3 d-flex gap-2 flex-wrap">
                    <a href="gestionar_usuarios.php"
                        class="btn btn-sm rounded-pill fw-bold <?php echo $rolFilter == '' ? 'btn-dark' : 'btn-outline-dark'; ?>">TODOS</a>
                    <a href="gestionar_usuarios.php?rol=cliente"
                        class="btn btn-sm rounded-pill fw-bold <?php echo $rolFilter == 'cliente' ? 'btn-success' : 'btn-outline-success'; ?>">CLIENTES</a>
                    <a href="gestionar_usuarios.php?rol=admin"
                        class="btn btn-sm rounded-pill fw-bold <?php echo $rolFilter == 'admin' ? 'btn-danger' : 'btn-outline-danger'; ?>">ADMINISTRADORES</a>
                    <a href="gestionar_usuarios.php?rol=abandonado"
                        class="btn btn-sm rounded-pill fw-bold <?php echo $rolFilter == 'abandonado' ? 'btn-warning' : 'btn-outline-warning'; ?>">
                        <i class="bi bi-cart-x-fill me-1"></i> COMPRAS PENDIENTES
                    </a>
                </div>

                <form class="d-flex bg-white p-1 rounded-pill border shadow-sm" method="GET">
                    <?php if ($rolFilter): ?><input type="hidden" name="rol" value="<?= $rolFilter ?>"><?php endif; ?>
                    <input class="form-control border-0 rounded-pill ps-3" type="search" name="q"
                        placeholder="Buscar por nombre o email..." value="<?php echo htmlspecialchars($search); ?>">
                    <button class="btn btn-dark rounded-pill px-4" type="submit"><i class="bi bi-search"></i></button>
                </form>
            </div>
            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                <button class="btn btn-primary-custom rounded-pill px-4 py-2 shadow-sm" onclick="abrirModalCrear()">
                    <i class="bi bi-person-plus-fill me-2"></i> Nuevo Usuario
                </button>
            </div>
        </div>

        <!-- TABLA -->
        <div class="card card-custom">
            <div class="table-responsive">
                <table class="table table-hover m-0 align-middle table-custom">
                    <thead>
                        <tr>
                            <th class="ps-4">Usuario</th>
                            <th>Rol</th>
                            <th>Email</th>
                            <th>Registro</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($usuarios) > 0): ?>
                            <?php foreach ($usuarios as $u):
                                $initials = strtoupper(substr($u['nombre'], 0, 1) . substr($u['apellido'], 0, 1));
                                $bgBadge = $u['rol'] == 'admin' ? 'bg-danger' : 'bg-success';
                                ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-circle me-3 shadow-sm text-white"
                                                style="background-color: <?php echo $u['rol'] == 'admin' ? '#FF4500' : '#28a745'; ?>">
                                                <?php echo $initials; ?>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark">
                                                    <?php echo htmlspecialchars($u['nombre'] . ' ' . $u['apellido']); ?>
                                                </div>
                                                <div class="small text-muted">ID: #<?php echo $u['id']; ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge rounded-pill <?php echo $bgBadge; ?> bg-opacity-75">
                                            <?php echo strtoupper($u['rol']); ?>
                                        </span>
                                    </td>
                                    <td class="text-secondary fw-semibold">
                                        <?php echo htmlspecialchars($u['email']); ?>
                                    </td>
                                    <td class="text-muted small">
                                        <?php echo date('d M Y', strtotime($u['created_at'])); ?>
                                    </td>
                                    <td class="text-end pe-4">
                                        <?php if ($rolFilter == 'abandonado' && !empty($u['telefono'])): ?>
                                            <?php
                                            $waMsg = "Hola " . $u['nombre'] . ", notamos que dejaste productos en tu carrito en MaquimPower. ¿Podemos ayudarte a completar tu compra? 😊";
                                            $waLink = "https://wa.me/51" . preg_replace('/[^0-9]/', '', $u['telefono']) . "?text=" . urlencode($waMsg);
                                            ?>
                                            <a href="<?= $waLink ?>" target="_blank"
                                                class="btn btn-sm btn-success rounded-pill px-3 fw-bold me-2 shadow-sm">
                                                <i class="bi bi-whatsapp me-1"></i> RECUPERAR
                                            </a>
                                        <?php endif; ?>

                                        <button class="btn btn-sm btn-light border rounded-circle shadow-sm me-1" title="Editar"
                                            onclick='abrirModalEditar(<?php echo json_encode($u); ?>)'>
                                            <i class="bi bi-pencil-fill text-primary"></i>
                                        </button>
                                        <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                            <a href="?delete=<?php echo $u['id']; ?>"
                                                class="btn btn-sm btn-light border rounded-circle shadow-sm" title="Eliminar"
                                                onclick="return confirm('¿Estás seguro de eliminar a este usuario? Esta acción es irreversible.');">
                                                <i class="bi bi-trash-fill text-danger"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">No se encontraron usuarios.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- MODAL USUARIO -->
    <div class="modal fade" id="modalUsuario" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow-lg">
                <div class="modal-header bg-dark text-white rounded-top-4">
                    <h5 class="modal-title fw-bold" id="modalTitle">Nuevo Usuario</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body p-4">
                        <input type="hidden" name="action" value="save_user">
                        <input type="hidden" name="user_id" id="user_id" value="0">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-secondary">NOMBRE</label>
                                <input type="text" name="nombre" id="nombre" class="form-control fw-bold" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-secondary">APELLIDO</label>
                                <input type="text" name="apellido" id="apellido" class="form-control fw-bold" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold text-secondary">EMAIL</label>
                                <input type="email" name="email" id="email" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-secondary">ROL</label>
                                <select name="rol" id="rol" class="form-select">
                                    <option value="cliente">Cliente</option>
                                    <option value="admin">Administrador</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-secondary">CONTRASEÑA</label>
                                <input type="password" name="password" id="password" class="form-control"
                                    placeholder="Dejar vacío si no cambia" autocomplete="new-password">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0 pb-4 px-4">
                        <button type="button" class="btn btn-light rounded-pill px-4"
                            data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary-custom rounded-pill px-5 shadow">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const modalUsuario = new bootstrap.Modal(document.getElementById('modalUsuario'));

        function abrirModalCrear() {
            document.getElementById('modalTitle').innerText = "Nuevo Usuario";
            document.getElementById('user_id').value = "0";
            document.getElementById('nombre').value = "";
            document.getElementById('apellido').value = "";
            document.getElementById('email').value = "";
            document.getElementById('rol').value = "cliente";
            document.getElementById('password').required = true;
            document.getElementById('password').placeholder = "Requerido para nuevos usuarios";
            modalUsuario.show();
        }

        function abrirModalEditar(u) {
            document.getElementById('modalTitle').innerText = "Editar Usuario #" + u.id;
            document.getElementById('user_id').value = u.id;
            document.getElementById('nombre').value = u.nombre;
            document.getElementById('apellido').value = u.apellido;
            document.getElementById('email').value = u.email;
            document.getElementById('rol').value = u.rol;
            document.getElementById('password').required = false;
            document.getElementById('password').placeholder = "Solo si deseas cambiarla";
            modalUsuario.show();
        }
    </script>

</body>

</html>