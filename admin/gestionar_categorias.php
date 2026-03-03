<?php
require_once 'check_auth.php';
require_once '../includes/db.php';

// --- ELIMINAR ---
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM blog WHERE id = ?")->execute([$_GET['delete']]);
    header("Location: gestionar_blog.php"); exit;
}

// --- GUARDAR ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $titulo = $_POST['titulo'];
    // Slug
    $slugBase = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $titulo)));
    $slug = $slugBase;
    $i = 1;
    while(true) {
        $checkParams = [$slug];
        $checkSql = "SELECT id FROM blog WHERE slug = ?";
        if (!empty($_POST['id'])) { $checkSql .= " AND id != ?"; $checkParams[] = $_POST['id']; }
        $stmtCheck = $pdo->prepare($checkSql);
        $stmtCheck->execute($checkParams);
        if ($stmtCheck->fetch()) { $slug = $slugBase . '-' . $i; $i++; } else { break; }
    }
    
    $contenido = $_POST['contenido'];
    $video = $_POST['video_url'];
    $autor = $_POST['autor'];
    $fecha = !empty($_POST['fecha']) ? $_POST['fecha'] : date('Y-m-d');

    // Imagen
    $imgFinal = $_POST['img_actual'] ?? 'https://via.placeholder.com/800x400';
    if (!empty($_FILES['imagen_file']['name'])) {
        $path = "../assets/img/blog_" . time() . ".jpg";
        if(move_uploaded_file($_FILES['imagen_file']['tmp_name'], $path)) { $imgFinal = "/assets/img/" . basename($path); }
    } elseif (!empty($_POST['imagen_link'])) {
        $imgFinal = $_POST['imagen_link'];
    }

    if (!empty($_POST['id'])) {
        $sql = "UPDATE blog SET titulo=?, slug=?, contenido=?, video_url=?, autor=?, fecha=?, imagen_url=? WHERE id=?";
        $pdo->prepare($sql)->execute([$titulo, $slug, $contenido, $video, $autor, $fecha, $imgFinal, $_POST['id']]);
    } else {
        $sql = "INSERT INTO blog (titulo, slug, contenido, video_url, autor, fecha, imagen_url) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $pdo->prepare($sql)->execute([$titulo, $slug, $contenido, $video, $autor, $fecha, $imgFinal]);
    }
    header("Location: gestionar_blog.php"); exit;
}

$lista = $pdo->query("SELECT * FROM blog ORDER BY fecha DESC")->fetchAll();
$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM blog WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión Blog | MaquimAdmin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        /* --- ESTILO MAQUIMPOWER UNIFICADO (Idéntico a Academia) --- */
        :root { --primary: #FF4500; --dark-bg: #1a1a1a; --body-bg: #f4f6f9; }
        body { background-color: var(--body-bg); font-family: 'Inter', sans-serif; padding-bottom: 50px; }
        
        .admin-header { 
            background: var(--dark-bg); color: white; padding: 25px 0; 
            margin-bottom: 30px; border-bottom: 4px solid var(--primary); 
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
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

        .form-control, .form-select {
            border: 2px solid #eee; padding: 10px 15px; border-radius: 8px; transition: 0.3s;
        }
        .form-control:focus { border-color: var(--primary); box-shadow: none; }
        label { font-size: 0.7rem; font-weight: 800; color: #666; text-transform: uppercase; margin-bottom: 5px; letter-spacing: 0.5px; }

        .btn-save {
            background: var(--primary); color: white; border: none; padding: 12px;
            border-radius: 50px; font-weight: 800; text-transform: uppercase;
            letter-spacing: 1px; width: 100%; transition: 0.3s;
            box-shadow: 0 5px 15px rgba(255, 69, 0, 0.2);
        }
        .btn-save:hover { transform: translateY(-2px); background: #e03e00; box-shadow: 0 8px 20px rgba(255, 69, 0, 0.4); }

        .table thead { background: #333; color: white; }
        .table th { padding: 15px; font-size: 0.75rem; border: none; text-transform: uppercase; }
        .table td { padding: 15px; vertical-align: middle; border-bottom: 1px solid #f0f0f0; }
        
        .img-thumb { width: 60px; height: 40px; object-fit: cover; border-radius: 4px; border: 1px solid #eee; }
        
        .btn-action { width: 32px; height: 32px; border-radius: 50%; padding: 0; display: inline-flex; align-items: center; justify-content: center; border: none; transition: 0.2s; }
        .btn-edit { background: #fff3cd; color: #856404; }
        .btn-delete { background: #f8d7da; color: #721c24; }
        .btn-action:hover { transform: scale(1.1); filter: brightness(0.95); }
    </style>
</head>
<body>

<div class="admin-header">
    <div class="container d-flex justify-content-between align-items-center">
        <h2 class="m-0 fw-black"><i class="bi bi-newspaper me-2"></i> GESTIÓN BLOG</h2>
        <a href="dashboard.php" class="btn btn-outline-light rounded-pill px-4 fw-bold">Volver al Panel</a>
    </div>
</div>

<div class="container">
    <div class="row g-4">
        
        <!-- FORMULARIO -->
        <div class="col-lg-5">
            <div class="card-custom sticky-top" style="top: 20px; z-index:1;">
                <div class="card-header-custom">
                    <span><?php echo $edit ? 'EDITANDO ARTÍCULO' : 'NUEVO ARTÍCULO'; ?></span>
                    <?php if($edit): ?> <a href="gestionar_blog.php" class="text-white-50 text-decoration-none small"><i class="bi bi-x"></i> Cancelar</a> <?php endif; ?>
                </div>
                <div class="card-body p-4">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?php echo $edit['id'] ?? ''; ?>">
                        
                        <div class="mb-3">
                            <label>Título</label>
                            <input type="text" name="titulo" class="form-control" value="<?php echo $edit['titulo'] ?? ''; ?>" required>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label>Fecha</label>
                                <input type="date" name="fecha" class="form-control" value="<?php echo $edit['fecha'] ?? date('Y-m-d'); ?>">
                            </div>
                            <div class="col-6">
                                <label>Autor</label>
                                <input type="text" name="autor" class="form-control" value="<?php echo $edit['autor'] ?? 'MaquimPower'; ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label>Video YouTube</label>
                            <input type="url" name="video_url" class="form-control" value="<?php echo $edit['video_url'] ?? ''; ?>" placeholder="https://...">
                        </div>

                        <!-- IMAGEN HÍBRIDA -->
                        <div class="mb-3">
                            <label>Imagen Portada</label>
                            <?php if(!empty($edit['imagen_url'])): ?>
                                <div class="mb-2 bg-light p-2 rounded d-flex align-items-center">
                                    <img src="<?php echo $edit['imagen_url']; ?>" width="40" class="me-2 rounded">
                                    <small class="text-muted">Actual</small>
                                    <input type="hidden" name="img_actual" value="<?php echo $edit['imagen_url']; ?>">
                                </div>
                            <?php endif; ?>
                            
                            <input type="file" name="imagen_file" class="form-control form-control-sm mb-2">
                            <input type="url" name="imagen_link" class="form-control form-control-sm" placeholder="O enlace externo...">
                        </div>

                        <div class="mb-4">
                            <label>Contenido</label>
                            <textarea name="contenido" class="form-control" rows="6" required><?php echo $edit['contenido'] ?? ''; ?></textarea>
                        </div>

                        <button type="submit" class="btn-save">
                            <i class="bi bi-send-fill me-2"></i> PUBLICAR
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- LISTADO -->
        <div class="col-lg-7">
            <div class="card-custom">
                <div class="card-header-custom bg-white text-dark border-bottom">
                    <span class="text-dark">HISTORIAL DE BLOG</span>
                    <span class="badge bg-dark rounded-pill"><?php echo count($lista); ?></span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover m-0">
                        <thead>
                            <tr>
                                <th>IMG</th>
                                <th>TÍTULO</th>
                                <th>FECHA</th>
                                <th class="text-end">ACCIÓN</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($lista as $l): ?>
                            <tr>
                                <td><img src="<?php echo $l['imagen_url']; ?>" class="img-thumb"></td>
                                <td>
                                    <div class="fw-bold text-truncate" style="max-width: 200px;"><?php echo $l['titulo']; ?></div>
                                    <small class="text-muted"><?php echo $l['autor']; ?></small>
                                </td>
                                <td class="small fw-bold text-secondary"><?php echo date('d M', strtotime($l['fecha'])); ?></td>
                                <td class="text-end">
                                    <a href="?edit=<?php echo $l['id']; ?>" class="btn-action btn-edit me-1"><i class="bi bi-pencil-fill"></i></a>
                                    <a href="?delete=<?php echo $l['id']; ?>" class="btn-action btn-delete" onclick="return confirm('¿Borrar?');"><i class="bi bi-trash-fill"></i></a>
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