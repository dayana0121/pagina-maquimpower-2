<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

$slug = isset($_GET['slug']) ? $_GET['slug'] : '';
$stmt = $pdo->prepare("SELECT * FROM blog WHERE slug = ?");
$stmt->execute([$slug]);
$post = $stmt->fetch();

if (!$post) {
    echo "<div class='container py-5 text-center'><h1>Artículo no encontrado</h1><a href='/blog.php' class='btn btn-dark'>Volver</a></div>";
    require_once 'includes/footer.php'; exit;
}

// 3. Procesar Video (Híbrido)
$videoEmbed = '';
$isTikTok = false;

if (!empty($post['video_url'])) {
    $url = $post['video_url'];
    
    // YouTube
    if (strpos($url, 'youtu') !== false) {
        if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $url, $matches)) {
            $videoEmbed = 'https://www.youtube.com/embed/' . $matches[1];
        }
    } 
    // TikTok
    elseif (strpos($url, 'tiktok.com') !== false) {
        $isTikTok = true;
        // Embed oficial de TikTok
        $videoEmbed = '<blockquote class="tiktok-embed" cite="'.$url.'" data-video-id="" style="max-width: 605px;min-width: 325px;"> <section> <a target="_blank" href="'.$url.'">Ver en TikTok</a> </section> </blockquote> <script async src="https://www.tiktok.com/embed.js"></script>';
    }
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/blog.php" class="text-muted text-decoration-none">Blog</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Artículo</li>
                </ol>
            </nav>

            <!-- Encabezado -->
            <h1 class="fw-black display-5 mb-3 lh-sm"><?php echo htmlspecialchars($post['titulo']); ?></h1>
            <div class="d-flex align-items-center gap-3 text-muted mb-5 border-bottom pb-4">
                <span><i class="bi bi-calendar3 me-1 text-primary"></i> <?php echo date('d M, Y', strtotime($post['fecha'])); ?></span>
                <span><i class="bi bi-person-circle me-1 text-primary"></i> <?php echo htmlspecialchars($post['autor']); ?></span>
            </div>

            <!-- Video o Imagen Principal -->
            <div class="mb-5 text-center">
                <?php if ($videoEmbed): ?>
                    <?php if($isTikTok): ?>
                        <!-- TikTok -->
                        <div class="d-flex justify-content-center"><?php echo $videoEmbed; ?></div>
                    <?php else: ?>
                        <!-- YouTube -->
                        <div class="ratio ratio-16x9 rounded-4 shadow overflow-hidden">
                            <iframe src="<?php echo $videoEmbed; ?>" title="YouTube video" allowfullscreen></iframe>
                        </div>
                    <?php endif; ?>
                <?php elseif (!empty($post['imagen_url'])): ?>
                    <img src="<?php echo $post['imagen_url']; ?>" class="w-100 rounded-4 shadow object-fit-cover" style="max-height: 450px;">
                <?php endif; ?>
            </div>

            <!-- Contenido -->
            <div class="blog-content fs-5 text-dark" style="line-height: 1.8; text-align: justify;">
                <?php echo nl2br(htmlspecialchars($post['contenido'])); ?>
            </div>

            <!-- Botón Compartir -->
            <div class="mt-5 pt-4 border-top">
                <h6 class="fw-bold mb-3 text-uppercase text-muted small">Compartir Artículo:</h6>
                <div class="d-flex gap-2">
                    <button class="btn btn-success rounded-pill px-4" onclick="window.open('https://api.whatsapp.com/send?text=Mira este artículo: ' + window.location.href)">
                        <i class="bi bi-whatsapp me-2"></i> WhatsApp
                    </button>
                    <button class="btn btn-outline-dark rounded-pill px-4" onclick="navigator.clipboard.writeText(window.location.href); Swal.fire({icon:'success', title:'Link copiado', toast:true, position:'top-end', showConfirmButton:false, timer:2000});">
                        <i class="bi bi-link-45deg me-2"></i> Copiar Link
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>