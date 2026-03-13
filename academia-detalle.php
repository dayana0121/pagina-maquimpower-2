<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM academia WHERE id = ?");
$stmt->execute([$id]);
$perfil = $stmt->fetch();

if (!$perfil) {
    echo "<div class='container py-5 text-center'><h1>Perfil no encontrado</h1><a href='academia.php' class='btn btn-dark'>Volver</a></div>";
    require_once 'includes/footer.php'; exit;
}

$img = !empty($perfil['imagen_url']) ? $perfil['imagen_url'] : '/assets/img/no-photo.png';
$logo = !empty($perfil['logo_empresa_url']) ? $perfil['logo_empresa_url'] : '';
?>

<!-- BANNER EMPRESA -->
<div class="position-relative bg-white border-bottom" style="height: 250px;">
    <div class="container h-100 d-flex align-items-center justify-content-center">
        <?php if($logo): ?>
            <img src="<?php echo $logo; ?>" style="max-height: 150px; max-width: 80%;">
        <?php else: ?>
            <h1 class="text-muted fw-black opacity-25 display-1 text-center"><?php echo htmlspecialchars($perfil['descripcion']); ?></h1>
        <?php endif; ?>
    </div>
    
    <!-- FOTO DE PERFIL FLOTANTE -->
    <div class="position-absolute start-0 w-100 text-center" style="bottom: -75px;">
        <img src="<?php echo $img; ?>" class="rounded-circle border border-5 border-white shadow bg-white" style="width: 150px; height: 150px; object-fit: cover;">
    </div>
</div>

<div class="container" style="padding-top: 100px; padding-bottom: 80px;">
    <div class="row g-5">
        
        <!-- COLUMNA IZQUIERDA: DATOS -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <h5 class="fw-bold text-primary mb-4 border-bottom pb-2">INFORMACIÓN PROFESIONAL</h5>
                
                <div class="mb-3">
                    <label class="small text-muted fw-bold text-uppercase">Nombre Completo</label>
                    <div class="fw-bold text-dark fs-5"><?php echo htmlspecialchars($perfil['nombre']); ?></div>
                </div>
                
                <div class="mb-3">
                    <label class="small text-muted fw-bold text-uppercase">Documento</label>
                    <div><?php echo htmlspecialchars($perfil['documento'] ?? 'No registrado'); ?></div>
                </div>

                <div class="mb-3">
                    <label class="small text-muted fw-bold text-uppercase">Empresa / Cargo</label>
                    <div class="fw-bold"><?php echo htmlspecialchars($perfil['descripcion']); ?></div>
                </div>

                <div class="mb-4">
                    <label class="small text-muted fw-bold text-uppercase mb-2">Contacto Directo</label>
                    
                    <!-- WhatsApp Interactivo -->
                    <?php if(!empty($perfil['telefono'])): ?>
                    <div class="mb-2">
                        <a href="https://wa.me/51<?php echo preg_replace('/[^0-9]/', '', $perfil['telefono']); ?>" target="_blank" class="text-decoration-none text-dark d-flex align-items-center bg-light p-2 rounded hover-shadow transition">
                            <i class="bi bi-whatsapp text-success fs-4 me-3"></i> 
                            <span class="fw-bold"><?php echo htmlspecialchars($perfil['telefono']); ?></span>
                        </a>
                    </div>
                    <?php endif; ?>

                    <!-- Email Interactivo -->
                    <?php if(!empty($perfil['email'])): ?>
                    <div>
                        <a href="mailto:<?php echo $perfil['email']; ?>" class="text-decoration-none text-dark d-flex align-items-center bg-light p-2 rounded hover-shadow transition">
                            <i class="bi bi-envelope text-primary fs-4 me-3"></i> 
                            <span><?php echo htmlspecialchars($perfil['email']); ?></span>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- REDES SOCIALES (Solo si existen) -->
                <?php if(!empty($perfil['facebook']) || !empty($perfil['instagram']) || !empty($perfil['tiktok'])): ?>
                <div class="d-flex gap-2 justify-content-center">
                    <?php if(!empty($perfil['facebook'])): ?><a href="<?php echo $perfil['facebook']; ?>" target="_blank" class="btn btn-primary btn-sm rounded-circle" style="width:35px;height:35px;"><i class="bi bi-facebook"></i></a><?php endif; ?>
                    <?php if(!empty($perfil['instagram'])): ?><a href="<?php echo $perfil['instagram']; ?>" target="_blank" class="btn btn-danger btn-sm rounded-circle" style="width:35px;height:35px; background:#E1306C; border:none;"><i class="bi bi-instagram"></i></a><?php endif; ?>
                    <?php if(!empty($perfil['tiktok'])): ?><a href="<?php echo $perfil['tiktok']; ?>" target="_blank" class="btn btn-dark btn-sm rounded-circle" style="width:35px;height:35px;"><i class="bi bi-tiktok"></i></a><?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- COLUMNA DERECHA: CERTIFICADOS -->
        <div class="col-lg-7">
            <div class="bg-light rounded-4 p-5 text-center border border-dashed h-100 d-flex flex-column justify-content-center">
                <i class="bi bi-patch-check-fill text-warning display-1 mb-3"></i>
                <h3 class="fw-black text-uppercase">Certificación Oficial</h3>
                <p class="text-muted mb-4 fs-5"><?php echo htmlspecialchars($perfil['tipo']); ?> otorgada por MaquimPower</p>
                
                <?php if(!empty($perfil['pdf_url'])): ?>
                    <a href="<?php echo $perfil['pdf_url']; ?>" target="_blank" class="btn btn-dark btn-lg rounded-pill px-5 fw-bold shadow hover-up">
                        <i class="bi bi-file-earmark-pdf-fill text-danger me-2"></i> VER DOCUMENTO PDF
                    </a>
                <?php else: ?>
                    <button class="btn btn-secondary btn-lg rounded-pill px-5 disabled">Certificado en Trámite</button>
                <?php endif; ?>
                
                <small class="text-muted mt-3 d-block">Este documento valida las competencias técnicas del profesional.</small>
            </div>
        </div>

    </div>
</div>

<?php require_once 'includes/footer.php'; ?>