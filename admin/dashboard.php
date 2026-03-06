<?php
require_once 'check_auth.php';
require_once '../includes/db.php';
// Construimos la URL actual con todos sus filtros (page, cat, q)
$urlActual = 'dashboard.php';
if (!empty($_SERVER['QUERY_STRING'])) {
    $urlActual .= '?' . $_SERVER['QUERY_STRING'];
}
// La guardamos en la sesión para usarla después
$_SESSION['dashboard_url_volver'] = $urlActual;
// -------------------------------------------
// --- CONFIGURACIÓN DE FILTROS ---
$catFilter = isset($_GET['cat']) ? $_GET['cat'] : '';
$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$limit = 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// CONSTRUIR CONSULTA DINÁMICA
$sqlBase = "FROM productos WHERE 1=1";
$params = [];

// 1. Filtro por Categoría
if ($catFilter) {
    $sqlBase .= " AND categoria = ?"; // Busqueda exacta por nombre de categoría
    $params[] = $catFilter;
}

// 2. Filtro por Búsqueda (Nombre o SKU)
if ($search) {
    $sqlBase .= " AND (nombre LIKE ? OR sku LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// 3. Contar total
$totalStmt = $pdo->prepare("SELECT COUNT(*) $sqlBase");
$totalStmt->execute($params);
$totalRows = $totalStmt->fetchColumn();
$totalPages = ceil($totalRows / $limit);

// 4. Obtener datos
$sqlData = "SELECT * $sqlBase ORDER BY id DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sqlData);
$stmt->execute($params);
$productos = $stmt->fetchAll();

// 5. OBTENER LISTA DE CATEGORÍAS PARA EL FILTRO (Solo Nombres únicos)
$stmtCats = $pdo->query("SELECT DISTINCT nombre FROM categorias ORDER BY nombre ASC");
$listaCategorias = $stmtCats->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin | MaquimPower</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
<style>
        /* --- ESTILO CYBER-INDUSTRIAL --- */
        :root {
            --dark-sidebar: #1a1a1a;
            --dark-bg: #f0f2f5;
            --primary: #FF4500;
            --primary-glow: rgba(255, 69, 0, 0.4);
            --card-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }

        body { 
            background-color: var(--dark-bg); 
            font-family: 'Inter', system-ui, sans-serif;
        }

        /* SIDEBAR (Barra Lateral) */
        .admin-sidebar { 
            min-height: 100vh; 
            background: var(--dark-sidebar); 
            color: #ccc;
            box-shadow: 5px 0 30px rgba(0,0,0,0.1);
            z-index: 100;
        }
        .admin-sidebar h4 {
            font-weight: 900;
            letter-spacing: -1px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .nav-link {
            color: #aaa;
            transition: all 0.3s;
            border-radius: 8px;
            margin-bottom: 5px;
            padding: 12px 20px;
            font-weight: 500;
        }
        .nav-link:hover {
            color: white;
            background: rgba(255,255,255,0.05);
            transform: translateX(5px);
        }
        .nav-link.active {
            background: var(--primary);
            color: white;
            box-shadow: 0 5px 15px var(--primary-glow);
        }

        /* CONTENIDO PRINCIPAL */
        .admin-content { padding: 40px; }
        
        .card {
            border: none;
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
            animation: slideUp 0.6s cubic-bezier(0.2, 0.8, 0.2, 1);
        }

        /* TABLA MODERNA */
        .table thead {
            background: #2c3e50;
            color: white;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 1px;
        }
        .table th { border: none; padding: 15px 20px; }
        .table td { 
            padding: 15px 20px; 
            vertical-align: middle; 
            border-bottom: 1px solid #f0f0f0;
        }
        .table tr { transition: 0.2s; }
        .table tr:hover { background-color: #fff8f5; } /* Hover Naranja muy suave */

        .img-thumb {
            width: 60px; height: 60px;
            border-radius: 8px;
            object-fit: contain;
            background: #fff;
            border: 1px solid #eee;
            transition: transform 0.3s;
        }
        tr:hover .img-thumb { transform: scale(1.2); }

        /* BOTONES DE ACCIÓN */
        .btn-action {
            width: 35px; height: 35px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: 0.3s;
            border: none;
        }
        .btn-edit { background: #e3f2fd; color: #1e88e5; }
        .btn-edit:hover { background: #1e88e5; color: white; transform: rotate(15deg); }
        
        .btn-delete { background: #ffebee; color: #e53935; }
        .btn-delete:hover { background: #e53935; color: white; transform: scale(1.1); }

        /* ANIMACIONES */
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* BADGES */
        .badge {
            padding: 8px 12px;
            border-radius: 50px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        .bg-success { background-color: #00c851 !important; }
        .bg-warning { background-color: #ffbb33 !important; color: #333; }
        .bg-danger { background-color: #ff4444 !important; }
    </style>
</head>
<body>

<div class="d-flex">
    <!-- SIDEBAR -->
    <div class="admin-sidebar p-3 d-none d-md-block" style="width: 250px;">
       <h4 class="mb-4 fw-black" style="color:var(--primary)">MAQUIM<span class="text-white">ADMIN</span></h4>
        <ul class="nav flex-column gap-2">
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link active text-white bg-primary rounded">
                    <i class="bi bi-box-seam me-2"></i> Productos
                </a>
            </li>
            <li class="nav-item">
                <a href="categorias_manager.php" class="nav-link text-white-50 hover-light">
                    <i class="bi bi-diagram-3 me-2"></i> Estructura Cat.
                </a>
            </li>
            <li class="nav-item">
                <a href="gestionar_academia.php" class="nav-link text-white-50 hover-light">
                    <i class="bi bi-mortarboard me-2"></i> Academia
                </a>
            </li>
            <li class="nav-item">
                <a href="gestionar_blog.php" class="nav-link text-white-50 hover-light">
                    <i class="bi bi-newspaper me-2"></i> Blog
                </a>
            </li>
              <li class="nav-item">
                <a href="gestionar_pedidos.php" class="nav-link text-white-50 hover-light">
                    <i class="bi bi-cart-check me-2"></i> Pedidos
                </a>
            </li>
            <li class="nav-item">
                <a href="gestionar_usuarios.php" class="nav-link text-white-50 hover-light">
                    <i class="bi bi-people me-2"></i> Usuarios
                </a>
            </li>
            <li class="nav-item mt-5">
                <a href="/pagina/" class="nav-link text-white-50">
                    <i class="bi bi-box-arrow-left me-2"></i> Volver a la Tienda
                </a>
            </li>
            <li class="nav-item">
                <a href="/pagina/controllers/auth.php?action=logout" class="nav-link text-danger">
                    <i class="bi bi-power me-2"></i> Cerrar Sesión
                </a>
            </li>
          <a href="exportar_data.php" class="btn btn-dark"><i class="bi bi-file-earmark-spreadsheet"></i> Exportar Data Maestra</a>
        </ul>
    </div>

    <!-- CONTENIDO PRINCIPAL -->
 <div class="flex-grow-1">
        <div class="admin-content">
            
            <!-- HEADER SUPERIOR -->
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
                <h2 class="m-0 fw-bold">Inventario Global</h2>
                <div class="d-flex gap-2">
                    <a href="editar_producto.php" class="btn btn-success fw-bold px-4 rounded-pill shadow-sm">
                        <i class="bi bi-plus-lg"></i> Nuevo Producto
                    </a>
                </div>
            </div>

            <!-- BARRA DE HERRAMIENTAS (BUSCADOR + FILTRO) -->
            <div class="card mb-4 p-3 border-0">
                <form class="d-flex gap-2 align-items-center flex-wrap" method="GET">
                    
                    <!-- SELECTOR DE CATEGORÍA -->
                    <div class="flex-grow-1" style="min-width: 200px;">
                        <select name="cat" class="form-select fw-bold text-secondary" onchange="this.form.submit()">
                            <option value="">📂 TODAS LAS CATEGORÍAS</option>
                            <?php foreach($listaCategorias as $nomCat): ?>
                                <option value="<?= $nomCat ?>" <?= ($catFilter == $nomCat) ? 'selected' : '' ?>>
                                    <?= $nomCat ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- INPUT BUSCADOR -->
                    <div class="input-group flex-grow-1" style="min-width: 250px;">
                        <input type="text" name="q" class="form-control" placeholder="Buscar por Nombre o SKU..." value="<?= htmlspecialchars($search); ?>">
                        <button class="btn btn-dark" type="submit"><i class="bi bi-search"></i></button>
                    </div>

                    <!-- BOTÓN LIMPIAR (Solo si hay filtros) -->
                    <?php if($search || $catFilter): ?>
                        <a href="dashboard.php" class="btn btn-outline-danger" title="Limpiar Filtros"><i class="bi bi-x-lg"></i></a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th class="ps-4">Imagen</th>
                                    <th>Nombre / SKU</th>
                                    <th>Categoría</th>
                                    <th>Precio</th>
                                    <th>Stock</th>
                                    <th class="text-end pe-4">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($productos as $p): 
                                    $img = !empty($p['imagen_url']) ? $p['imagen_url'] : '/assets/img/no-photo.png';
                                ?>
                                <tr>
                                    <td class="ps-4">
                                        <img src="<?php echo $img; ?>" class="img-thumb rounded">
                                    </td>
                                    <td>
                                        <div class="fw-bold text-truncate" style="max-width: 250px;"><?php echo htmlspecialchars($p['nombre']); ?></div>
                                        <small class="text-muted"><?php echo $p['sku']; ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary"><?php echo explode('>', $p['categoria'] ?? '')[0]; ?></span>
                                    </td>
                                    <td class="fw-bold">S/ <?php echo number_format($p['precio'], 2); ?></td>
                                    <td>
                                        <?php if($p['stock_actual'] > 5): ?>
                                            <span class="badge bg-success"><?php echo $p['stock_actual']; ?></span>
                                        <?php elseif($p['stock_actual'] > 0): ?>
                                            <span class="badge bg-warning text-dark"><?php echo $p['stock_actual']; ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Agotado</span>
                                        <?php endif; ?>
                                    </td>
                                   <td class="text-end pe-4">
                                        <a href="editar_producto.php?id=<?php echo $p['id']; ?>" class="btn-action btn-edit me-2" title="Editar">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>
                                        <button onclick="eliminar(<?php echo $p['id']; ?>)" class="btn-action btn-delete" title="Eliminar">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            
                        </table>
                    </div>

                </div>
                <!-- PAGINACIÓN -->
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <!-- Botón Anterior -->
                    <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                       <a class="page-link" href="?page=<?php echo $page - 1; ?>&cat=<?php echo urlencode($catFilter); ?>&q=<?php echo urlencode($search); ?>">Anterior</a>
                    </li>
                    
                    <!-- Info Página -->
                    <li class="page-item disabled">
                        <span class="page-link text-dark">Página <?php echo $page; ?> de <?php echo $totalPages; ?></span>
                    </li>

                    <!-- Botón Siguiente -->
                    <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                      <a class="page-link" href="?page=<?php echo $page + 1; ?>&cat=<?php echo urlencode($catFilter); ?>&q=<?php echo urlencode($search); ?>">Siguiente</a>
                    </li>
                </ul>
            </nav>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function eliminar(id) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "Esto borrará el producto de la tienda.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, borrar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `eliminar_producto.php?id=${id}`;
        }
    })
}
</script>

</body>
</html>