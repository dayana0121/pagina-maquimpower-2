<?php
require_once 'check_auth.php';
require_once '../includes/db.php';

// --- LÓGICA DE ÁRBOL PARA VISTA DE JERARQUÍAS ---
function obtenerArbol($pdo) {
    $stmt = $pdo->query("SELECT * FROM categorias ORDER BY padre_id ASC, nombre ASC");
    $categorias = $stmt->fetchAll();
    $tree = [];
    $referencias = [];

    foreach ($categorias as $c) {
        $c['children'] = [];
        $referencias[$c['id']] = $c;
        if ($c['padre_id'] == null) {
            $tree[$c['id']] = &$referencias[$c['id']];
        } else {
            $referencias[$c['padre_id']]['children'][] = &$referencias[$c['id']];
        }
    }
    return $tree;
}
$tree = obtenerArbol($pdo);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Estructura de Categorías | MaquimAdmin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root { --primary: #FF4500; --dark-bg: #1a1a1a; --body-bg: #f4f6f9; }
        body { background-color: var(--body-bg); font-family: 'Inter', sans-serif; }
        
        .admin-header { 
            background: var(--dark-bg); color: white; padding: 25px 0; 
            margin-bottom: 30px; border-bottom: 4px solid var(--primary); 
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .card-custom {
            border: none; border-radius: 12px; box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            background: white; overflow: hidden;
        }

        .card-header-custom {
            background: #222; color: white; padding: 15px 20px; 
            font-weight: 700; text-transform: uppercase; font-size: 0.85rem;
            border-left: 5px solid var(--primary);
        }

        .form-control, .form-select { border: 2px solid #eee; padding: 10px 15px; border-radius: 8px; }
        .form-control:focus { border-color: var(--primary); box-shadow: none; }
        label { font-size: 0.7rem; font-weight: 800; color: #666; text-transform: uppercase; margin-bottom: 5px; }

        .btn-save {
            background: var(--primary); color: white; border: none; padding: 12px;
            border-radius: 50px; font-weight: 800; text-transform: uppercase;
            width: 100%; transition: 0.3s; box-shadow: 0 5px 15px rgba(255, 69, 0, 0.2);
        }
        .btn-save:hover { transform: translateY(-2px); background: #e03e00; }

        /* Estilo para el Árbol de Jerarquías */
        .tree-container { list-style: none; padding-left: 0; }
        .tree-item { padding: 10px; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center; justify-content: space-between; }
        .level-0 { background: #f8f9fa; font-weight: 800; color: var(--dark-bg); border-left: 3px solid var(--primary); }
        .level-1 { padding-left: 30px; color: #444; font-weight: 600; }
        .level-2 { padding-left: 60px; color: #777; font-size: 0.9rem; }
    </style>
</head>
<body>

<div class="admin-header">
    <div class="container d-flex justify-content-between align-items-center">
        <h2 class="m-0 fw-black"><i class="bi bi-diagram-3-fill text-primary me-2"></i> ESTRUCTURA WEB</h2>
        <a href="dashboard.php" class="btn btn-outline-light rounded-pill px-4 fw-bold">
            <i class="bi bi-arrow-left me-2"></i> Regresar al Panel
        </a>
    </div>
</div>

<div class="container">
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card-custom sticky-top" style="top: 20px;">
                <div class="card-header-custom">NUEVA CATEGORÍA</div>
                <div class="card-body p-4">
                    <form action="procesar_categoria.php" method="POST">
                        <div class="mb-3">
                            <label>Nombre de Categoría</label>
                            <input type="text" name="nombre" class="form-control" placeholder="Ej: Aspiradoras Industriales" required>
                        </div>
                        <div class="mb-4">
                            <label>Dependencia (Padre)</label>
                            <select name="padre_id" class="form-select">
                                <option value="">-- ES CATEGORÍA MADRE --</option>
                                <?php foreach($tree as $m): ?>
                                    <option value="<?= $m['id'] ?>" class="fw-bold text-primary">📂 <?= $m['nombre'] ?></option>
                                    <?php foreach($m['children'] as $h): ?>
                                        <option value="<?= $h['id'] ?>">&nbsp;&nbsp;&nbsp;↳ <?= $h['nombre'] ?></option>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn-save">CREAR NIVEL</button>
                    </form>
                </div>
            </div>
        </div>

       <div class="col-lg-8">
    <div class="card-custom">
        <div class="card-header-custom bg-white text-dark border-bottom d-flex justify-content-between">
            <span>MAPA ESTRUCTURAL</span>
            <small class="text-muted text-none fw-normal">Categoría > Subcategoría > Ítem</small>
        </div>
        <div class="p-0">
            <div class="tree-container">
                <?php foreach($tree as $madre): ?>
                    <div class="tree-item level-0">
                        <span><i class="bi bi-folder-fill text-warning me-2"></i> <?= $madre['nombre'] ?></span>
                        <div class="btn-group">
                            <button onclick="editCat(<?= $madre['id'] ?>, '<?= $madre['nombre'] ?>', '<?= $madre['padre_id'] ?>')" class="btn btn-sm btn-edit me-1"><i class="bi bi-pencil"></i></button>
                            <button onclick="confirmDelete(<?= $madre['id'] ?>)" class="btn btn-sm btn-delete"><i class="bi bi-trash"></i></button>
                        </div>
                    </div>
                    
                    <?php foreach($madre['children'] as $hijo): ?>
                        <div class="tree-item level-1">
                            <span><i class="bi bi-arrow-return-right me-2 text-muted"></i> <?= $hijo['nombre'] ?></span>
                            <div class="btn-group">
                                <button onclick="editCat(<?= $hijo['id'] ?>, '<?= $hijo['nombre'] ?>', '<?= $hijo['padre_id'] ?>')" class="btn btn-sm btn-edit me-1"><i class="bi bi-pencil"></i></button>
                                <button onclick="confirmDelete(<?= $hijo['id'] ?>)" class="btn btn-sm btn-delete"><i class="bi bi-trash"></i></button>
                            </div>
                        </div>

                        <?php foreach($hijo['children'] as $nieto): ?>
                            <div class="tree-item level-2">
                                <span><i class="bi bi-dot text-primary"></i> <?= $nieto['nombre'] ?></span>
                                <div class="btn-group">
                                    <button onclick="editCat(<?= $nieto['id'] ?>, '<?= $nieto['nombre'] ?>', '<?= $nieto['padre_id'] ?>')" class="btn btn-sm btn-edit me-1"><i class="bi bi-pencil"></i></button>
                                    <button onclick="confirmDelete(<?= $nieto['id'] ?>)" class="btn btn-sm btn-delete"><i class="bi bi-trash"></i></button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-dark text-white rounded-top-4">
                <h5 class="modal-title fw-bold">EDITAR CATEGORÍA</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="procesar_categoria.php" method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="mb-3">
                        <label>Nombre</label>
                        <input type="text" name="nombre" id="edit_nombre" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Dependencia (Padre)</label>
                        <select name="padre_id" id="edit_padre_id" class="form-select">
                            <option value="">-- ES CATEGORÍA MADRE --</option>
                            <?php foreach($all_cats as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= $c['nombre'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" class="btn-save py-2">GUARDAR CAMBIOS</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function editCat(id, nombre, padreId) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_nombre').value = nombre;
    document.getElementById('edit_padre_id').value = padreId;
    new bootstrap.Modal(document.getElementById('editModal')).show();
}

function confirmDelete(id) {
    Swal.fire({
        title: '¿Eliminar categoría?',
        text: "¡Cuidado! Si tiene subcategorías o productos asignados, podrías causar errores visuales.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#FF4500',
        cancelButtonColor: '#333',
        confirmButtonText: 'SÍ, ELIMINAR',
        cancelButtonText: 'CANCELAR'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `procesar_categoria.php?delete_id=${id}`;
        }
    })
}
</script>
    </div>
</div>
</body>
</html>