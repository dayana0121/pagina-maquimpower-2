<?php
require_once 'check_auth.php';
require_once '../includes/db.php';

// --- LÓGICA (Backend) ---
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM academia WHERE id = ?")->execute([$_GET['delete']]);
    header("Location: gestionar_academia.php"); exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $empresa = $_POST['empresa'];
    $doc = $_POST['documento'];
    $email = $_POST['email'];
    $pdf = $_POST['pdf_url'];
    $tipo = $_POST['tipo'];
    
    $fotoSql = "";
    if (!empty($_FILES['foto']['name'])) {
        $path = "../assets/img/profile_" . time() . ".jpg";
        move_uploaded_file($_FILES['foto']['tmp_name'], $path);
        $fotoSql = ", imagen_url = '/assets/img/" . basename($path) . "'";
        if(empty($_POST['id'])) $fotoSql = "/assets/img/" . basename($path);
    }

    if (!empty($_POST['id'])) {
        $sql = "UPDATE academia SET nombre=?, descripcion=?, documento=?, email=?, pdf_url=?, tipo=? $fotoSql WHERE id=?";
        $pdo->prepare($sql)->execute([$nombre, $empresa, $doc, $email, $pdf, $tipo, $_POST['id']]);
    } else {
        $fotoFinal = $fotoSql ? $fotoSql : '/assets/img/no-photo.png';
        $sql = "INSERT INTO academia (nombre, descripcion, documento, email, pdf_url, tipo, imagen_url) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $pdo->prepare($sql)->execute([$nombre, $empresa, $doc, $email, $pdf, $tipo, $fotoFinal]);
    }
    header("Location: gestionar_academia.php"); exit;
}

$lista = $pdo->query("SELECT * FROM academia ORDER BY id DESC")->fetchAll();
$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM academia WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión Academia | MaquimAdmin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        /* --- ESTILO MAQUIMPOWER UNIFICADO --- */
        :root { --primary: #FF4500; --dark-bg: #1a1a1a; --body-bg: #f4f6f9; }
        
        body { background-color: var(--body-bg); font-family: 'Inter', sans-serif; padding-bottom: 50px; }
        
        /* Header Premium */
        .admin-header { 
            background: var(--dark-bg); color: white; padding: 25px 0; 
            margin-bottom: 30px; border-bottom: 4px solid var(--primary); 
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        /* Tarjetas Flotantes */
        .card-custom {
            border: none; border-radius: 12px; 
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            background: white; overflow: hidden;
        }
        .card-header-custom {
            background: #222; color: white; padding: 15px 20px; 
            font-weight: 700; text-transform: uppercase; font-size: 0.85rem;
            display: flex; justify-content: space-between; align-items: center;
            border-left: 5px solid var(--primary);
        }

        /* Inputs Modernos */
        .form-control, .form-select {
            border: 2px solid #eee; padding: 10px 15px; border-radius: 8px; transition: 0.3s;
        }
        .form-control:focus, .form-select:focus { border-color: var(--primary); box-shadow: none; }
        label { font-size: 0.7rem; font-weight: 800; color: #666; text-transform: uppercase; margin-bottom: 5px; letter-spacing: 0.5px; }

        /* Botón Guardar Naranja */
        .btn-save {
            background: var(--primary); color: white; border: none; padding: 12px;
            border-radius: 50px; font-weight: 800; text-transform: uppercase;
            letter-spacing: 1px; width: 100%; transition: 0.3s;
            box-shadow: 0 5px 15px rgba(255, 69, 0, 0.2);
        }
        .btn-save:hover { transform: translateY(-2px); background: #e03e00; box-shadow: 0 8px 20px rgba(255, 69, 0, 0.4); }

        /* Tabla Limpia */
        .table thead { background: #333; color: white; }
        .table th { padding: 15px; font-size: 0.75rem; border: none; text-transform: uppercase; }
        .table td { padding: 15px; vertical-align: middle; border-bottom: 1px solid #f0f0f0; }
        
        /* Avatares y Acciones */
        .img-avatar { width: 45px; height: 45px; object-fit: cover; border-radius: 50%; border: 2px solid #eee; }
        .btn-action { width: 32px; height: 32px; border-radius: 50%; padding: 0; display: inline-flex; align-items: center; justify-content: center; border: none; transition: 0.2s; }
        .btn-edit { background: #fff3cd; color: #856404; }
        .btn-delete { background: #f8d7da; color: #721c24; }
        .btn-action:hover { transform: scale(1.1); filter: brightness(0.95); }
    </style>
</head>
<body>

<div class="admin-header">
    <div class="container d-flex justify-content-between align-items-center">
        <h2 class="m-0 fw-black"><i class="bi bi-mortarboard-fill text-warning me-2"></i> GESTIÓN ACADEMIA</h2>
        <a href="dashboard.php" class="btn btn-outline-light rounded-pill px-4 fw-bold">Volver al Panel</a>
    </div>
</div>

<div class="container">
    <div class="row g-4">
        
        <!-- FORMULARIO (IZQUIERDA) -->
        <div class="col-lg-4">
            <div class="card-custom sticky-top" style="top: 20px; z-index:1;">
                <div class="card-header-custom">
                    <span><?php echo $edit ? 'EDITAR PERFIL' : 'NUEVO ESTUDIANTE'; ?></span>
                    <?php if($edit): ?> <a href="gestionar_academia.php" class="text-white-50 text-decoration-none small"><i class="bi bi-x"></i> Cancelar</a> <?php endif; ?>
                </div>
                <div class="card-body p-4">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?php echo $edit['id'] ?? ''; ?>">
                        
                        <div class="mb-3">
                            <label>Nombre Completo</label>
                            <input type="text" name="nombre" class="form-control" value="<?php echo $edit['nombre'] ?? ''; ?>" required placeholder="Ej: Dayana Burguillos">
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label>Tipo</label>
                                <select name="tipo" class="form-select">
                                    <option value="Certificación" <?php echo ($edit['tipo']??'')=='Certificación'?'selected':''; ?>>Certificación</option>
                                    <option value="Curso" <?php echo ($edit['tipo']??'')=='Curso'?'selected':''; ?>>Curso</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label>Documento</label>
                                <input type="text" name="documento" class="form-control" value="<?php echo $edit['documento'] ?? ''; ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label>Empresa / Cargo</label>
                            <input type="text" name="empresa" class="form-control" value="<?php echo $edit['descripcion'] ?? ''; ?>">
                        </div>

                        <div class="mb-3">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" value="<?php echo $edit['email'] ?? ''; ?>">
                        </div>

                        <div class="mb-3">
                            <label>PDF (Certificado)</label>
                            <input type="url" name="pdf_url" class="form-control" value="<?php echo $edit['pdf_url'] ?? ''; ?>" placeholder="https://...">
                        </div>

                        <div class="mb-4">
                            <label>Foto de Perfil</label>
                            <div class="d-flex align-items-center gap-2">
                                <?php if(!empty($edit['imagen_url'])): ?>
                                    <img src="<?php echo $edit['imagen_url']; ?>" class="img-avatar">
                                <?php endif; ?>
                                <input type="file" name="foto" class="form-control form-control-sm">
                            </div>
                        </div>

                        <button type="submit" class="btn-save">
                            <i class="bi bi-check-lg me-2"></i> GUARDAR PERFIL
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- LISTADO (DERECHA) -->
        <div class="col-lg-8">
            <div class="card-custom">
                <div class="card-header-custom bg-white text-dark border-bottom">
                    <span class="text-dark">LISTADO DE ESTUDIANTES</span>
                    <span class="badge bg-dark rounded-pill"><?php echo count($lista); ?></span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover m-0">
                        <thead>
                            <tr>
                                <th>Foto</th>
                                <th>Nombre</th>
                                <th>Tipo</th>
                                <th>Empresa</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($lista as $l): ?>
                            <tr>
                                <td><img src="<?php echo $l['imagen_url']; ?>" class="img-avatar"></td>
                                <td class="fw-bold"><?php echo $l['nombre']; ?></td>
                                <td><span class="badge bg-light text-dark border fw-normal"><?php echo $l['tipo']; ?></span></td>
                                <td class="small text-muted"><?php echo $l['descripcion']; ?></td>
                                <td class="text-end">
                                    <a href="?edit=<?php echo $l['id']; ?>" class="btn-action btn-edit me-1"><i class="bi bi-pencil-fill"></i></a>
                                    <a href="?delete=<?php echo $l['id']; ?>" class="btn-action btn-delete" onclick="return confirm('¿Eliminar?');"><i class="bi bi-trash-fill"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

</body>
</html>