<?php
require_once 'includes/db.php';
require_once 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Hero Slider</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f5f5f5; font-family: monospace; }
        .debug-section { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; border-left: 5px solid #FF4500; }
        .debug-item { padding: 10px; background: #f9f9f9; margin: 5px 0; border-radius: 4px; }
        .warning { color: #d9534f; font-weight: bold; }
        .success { color: #5cb85c; font-weight: bold; }
        .info { color: #5bc0de; font-weight: bold; }
        .hero-slider { 
            border: 2px solid red; 
            width: 100%; 
            height: 400px; 
            background: #000;
            position: relative;
            overflow: hidden;
            margin: 20px 0;
        }
        .hero-slide {
            position: absolute;
            inset: 0;
            opacity: 0;
            transition: opacity 1s;
            width: 100%;
            height: 100%;
        }
        .hero-slide.active {
            opacity: 1;
            z-index: 2;
        }
        .hero-banner {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        .hero-section-dark {
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }
        code { background: #f0f0f0; padding: 2px 6px; border-radius: 3px; }
    </style>
</head>
<body>
<div class="container mt-5 mb-5">
    <h1 class="mb-4">🔍 Debug: Hero Slider</h1>

    <!-- SECTION 1: IMAGE FILES VERIFICATION -->
    <div class="debug-section">
        <h3>📁 Verificación de Archivos de Imagen</h3>
        <?php
        $baseUrl = 'http://' . $_SERVER['HTTP_HOST'];
        $images = [
            'web' => [
                'Banner 1 Hidrolavadoras (PC).webp',
                'Banner 2 Aspiradora (PC).webp',
                'Banner 3 Implementa tu carwash (PC).webp',
                'Banner 4 Proyecto detailing tapices y salon (PC).webp'
            ],
            'mobile' => [
                'Banner 1 Hidrolavadoras (Mobile).webp',
                'Banner 2 Aspiradora (Mobile).webp',
                'Banner 3 Implementa tu carwash (Mobile).webp',
                'Banner 4 Proyecto detailing tapices y salon (Mobile).webp'
            ]
        ];

        foreach ($images as $type => $banners) {
            echo "<h5 class='mt-3'>Imágenes <strong>$type</strong>:</h5>";
            $dir = __DIR__ . '/assets/img/' . $type;
            
            foreach ($banners as $banner) {
                $path = $dir . '/' . $banner;
                $exists = file_exists($path);
                $status = $exists ? '<span class="success">✓ Existe</span>' : '<span class="warning">✗ NO EXISTE</span>';
                $size = $exists ? filesize($path) : 0;
                echo '<div class="debug-item">';
                echo $status . ' | <code>/assets/img/' . $type . '/' . $banner . '</code>';
                if ($exists) {
                    echo ' | Tamaño: ' . round($size / 1024 / 1024, 2) . ' MB';
                }
                echo '</div>';
            }
        }
        ?>
    </div>

    <!-- SECTION 2: BASE URL & PATHS -->
    <div class="debug-section">
        <h3>🌐 Configuración de URLs</h3>
        <div class="debug-item"><span class="info">Base URL:</span> <code><?= $baseUrl ?></code></div>
        <div class="debug-item"><span class="info">HTTP HOST:</span> <code><?= $_SERVER['HTTP_HOST'] ?></code></div>
        <div class="debug-item"><span class="info">PHP Script:</span> <code><?= __FILE__ ?></code></div>
    </div>

    <!-- SECTION 3: DOM PREVIEW - HERO SLIDER -->
    <div class="debug-section">
        <h3>🎬 Vista Previa del Hero Slider</h3>
        <p><small>Prueba visual sin JavaScript - verifica que se cargan las imágenes</small></p>
        
        <div class="hero-slider">
            <!-- SLIDE 1: ORIGINAL HERO -->
            <div class="hero-slide active">
                <div class="hero-section-dark">
                    <div style="text-align: center;">
                        <h2>Slide 1: HERO (Texto)</h2>
                        <p>Este es el slide original con texto</p>
                    </div>
                </div>
            </div>

            <!-- SLIDE 2: BANNER 1 -->
            <div class="hero-slide">
                <img src="<?= $baseUrl ?>/assets/img/web/Banner 1 Hidrolavadoras (PC).webp" 
                     class="hero-banner" 
                     alt="Banner 1" 
                     onerror="console.error('Error cargando Banner 1')">
            </div>

            <!-- SLIDE 3: BANNER 2 -->
            <div class="hero-slide">
                <img src="<?= $baseUrl ?>/assets/img/web/Banner 2 Aspiradora (PC).webp" 
                     class="hero-banner" 
                     alt="Banner 2"
                     onerror="console.error('Error cargando Banner 2')">
            </div>

            <!-- SLIDE 4: BANNER 3 -->
            <div class="hero-slide">
                <img src="<?= $baseUrl ?>/assets/img/web/Banner 3 Implementa tu carwash (PC).webp" 
                     class="hero-banner" 
                     alt="Banner 3"
                     onerror="console.error('Error cargando Banner 3')">
            </div>

            <!-- SLIDE 5: BANNER 4 -->
            <div class="hero-slide">
                <img src="<?= $baseUrl ?>/assets/img/web/Banner 4 Proyecto detailing tapices y salon (PC).webp" 
                     class="hero-banner" 
                     alt="Banner 4"
                     onerror="console.error('Error cargando Banner 4')">
            </div>
        </div>

        <p class="mt-3"><small>El slider arriba debería mostrar diferentes slides cada 2 segundos. Si las imágenes no cargan, verás un fondo negro.</small></p>
    </div>

    <!-- SECTION 4: IMAGE URLS GENERATED -->
    <div class="debug-section">
        <h3>🔗 URLs Generadas (Copiadas del HTML)</h3>
        <p><small>Estas son las URLs que se generan en el HTML del index.php</small></p>
        
        <h5 class="mt-3">Desktop Images:</h5>
        <?php
        $webImages = [
            'Banner 1 Hidrolavadoras (PC).webp',
            'Banner 2 Aspiradora (PC).webp',
            'Banner 3 Implementa tu carwash (PC).webp',
            'Banner 4 Proyecto detailing tapices y salon (PC).webp'
        ];
        foreach ($webImages as $img) {
            echo '<div class="debug-item"><code>' . $baseUrl . '/assets/img/web/' . $img . '</code></div>';
        }
        ?>

        <h5 class="mt-3">Mobile Images:</h5>
        <?php
        $mobileImages = [
            'Banner 1 Hidrolavadoras (Mobile).webp',
            'Banner 2 Aspiradora (Mobile).webp',
            'Banner 3 Implementa tu carwash (Mobile).webp',
            'Banner 4 Proyecto detailing tapices y salon (Mobile).webp'
        ];
        foreach ($mobileImages as $img) {
            echo '<div class="debug-item"><code>' . $baseUrl . '/assets/img/mobile/' . $img . '</code></div>';
        }
        ?>
    </div>

    <!-- SECTION 5: CSS INSPECTION -->
    <div class="debug-section">
        <h3>🎨 Inspección CSS</h3>
        <p>Abre la consola del navegador (F12) y copia este código para inspeccionar el CSS:</p>
        <pre style="background: #f0f0f0; padding: 10px; border-radius: 4px; overflow-x: auto;">
// Inspeccionar el slider
const slider = document.querySelector('.hero-slider');
console.log('Slider:', slider);
console.log('Slider height:', window.getComputedStyle(slider).height);
console.log('Slider overflow:', window.getComputedStyle(slider).overflow);

// Inspeccionar slides
const slides = document.querySelectorAll('.hero-slide');
console.log('Total slides:', slides.length);
slides.forEach((slide, i) => {
    const computed = window.getComputedStyle(slide);
    console.log(`Slide ${i}: opacity=${computed.opacity}, visibility=${computed.visibility}, display=${computed.display}, z-index=${computed.zIndex}`);
});

// Inspeccionar imágenes
const images = document.querySelectorAll('.hero-banner');
console.log('Total images:', images.length);
images.forEach((img, i) => {
    console.log(`Image ${i}: src=${img.src}, complete=${img.complete}, naturalWidth=${img.naturalWidth}`);
});
        </pre>
    </div>

    <!-- SECTION 6: CONSOLE LOGS MONITOR -->
    <div class="debug-section">
        <h3>📊 Monitor de Consola (en tiempo real)</h3>
        <div id="console-output" style="background: #000; color: #0f0; padding: 15px; border-radius: 4px; font-size: 12px; max-height: 300px; overflow-y: auto; font-family: 'Courier New', monospace;">
            <div style="color: #888;">[Abre la consola (F12) para ver logs...]</div>
        </div>
    </div>

    <!-- SECTION 7: QUICK TESTS -->
    <div class="debug-section">
        <h3>⚡ Tests Rápidos</h3>
        <button class="btn btn-primary" onclick="testSlider()">Test Slider DOM</button>
        <button class="btn btn-success" onclick="testImages()">Test Imágenes</button>
        <button class="btn btn-warning" onclick="testCSS()">Test CSS</button>
        <button class="btn btn-danger" onclick="testRotation()">Test Rotación</button>
        <div id="test-output" class="debug-item mt-3" style="display: none;"></div>
    </div>

</div>

<script>
function log(msg) {
    console.log(msg);
    const output = document.getElementById('console-output');
    const timestamp = new Date().toLocaleTimeString();
    output.innerHTML += `<div>[${timestamp}] ${msg}</div>`;
    output.scrollTop = output.scrollHeight;
}

function testSlider() {
    const testDiv = document.getElementById('test-output');
    testDiv.style.display = 'block';
    testDiv.innerHTML = '';

    const slider = document.querySelector('.hero-slider');
    const slides = document.querySelectorAll('.hero-slide');

    log(`✅ TEST SLIDER INICIADO`);
    
    if (!slider) {
        log(`❌ Slider NO encontrado`);
        testDiv.innerHTML += '<p class="warning">Slider NO encontrado en el DOM</p>';
        return;
    }
    
    log(`✅ Slider encontrado`);
    log(`  - Height: ${window.getComputedStyle(slider).height}`);
    log(`  - Width: ${window.getComputedStyle(slider).width}`);
    log(`  - Background: ${window.getComputedStyle(slider).backgroundColor}`);
    
    if (slides.length === 0) {
        log(`❌ NO hay slides`);
        testDiv.innerHTML += '<p class="warning">No se encontraron slides en el slider</p>';
        return;
    }

    log(`✅ ${slides.length} slides encontrados`);
    slides.forEach((slide, i) => {
        const computed = window.getComputedStyle(slide);
        const isActive = slide.classList.contains('active');
        log(`  Slide ${i}: active=${isActive}, opacity=${computed.opacity}, visibility=${computed.visibility}`);
    });

    testDiv.innerHTML += `<p class="success">✅ Slider test completado - Ver consola (F12)</p>`;
}

function testImages() {
    const testDiv = document.getElementById('test-output');
    testDiv.style.display = 'block';
    testDiv.innerHTML = '';

    const images = document.querySelectorAll('.hero-banner');
    log(`✅ TEST IMÁGENES INICIADO`);
    
    if (images.length === 0) {
        log(`❌ NO hay imágenes encontradas`);
        testDiv.innerHTML += '<p class="warning">No se encontraron imágenes en el slider</p>';
        return;
    }

    log(`✅ ${images.length} imágenes encontradas`);
    
    images.forEach((img, i) => {
        const loaded = img.complete && img.naturalWidth > 0;
        const status = loaded ? '✅' : (img.complete ? '❌' : '⏳');
        log(`  ${status} Image ${i}: ${img.src.split('/').pop()} (${img.naturalWidth}x${img.naturalHeight})`);
    });

    testDiv.innerHTML += `<p class="success">✅ Images test completado - Ver consola (F12)</p>`;
}

function testCSS() {
    const testDiv = document.getElementById('test-output');
    testDiv.style.display = 'block';
    testDiv.innerHTML = '';

    log(`✅ TEST CSS INICIADO`);
    
    const slider = document.querySelector('.hero-slider');
    if (slider) {
        const styles = window.getComputedStyle(slider);
        log(`Slider CSS:`);
        log(`  - display: ${styles.display}`);
        log(`  - position: ${styles.position}`);
        log(`  - height: ${styles.height}`);
        log(`  - width: ${styles.width}`);
        log(`  - overflow: ${styles.overflow}`);
        log(`  - background: ${styles.backgroundColor}`);
    }

    const activeSlide = document.querySelector('.hero-slide.active');
    if (activeSlide) {
        const styles = window.getComputedStyle(activeSlide);
        log(`Active Slide CSS:`);
        log(`  - display: ${styles.display}`);
        log(`  - opacity: ${styles.opacity}`);
        log(`  - visibility: ${styles.visibility}`);
        log(`  - z-index: ${styles.zIndex}`);
    }

    testDiv.innerHTML += `<p class="success">✅ CSS test completado - Ver consola (F12)</p>`;
}

function testRotation() {
    const testDiv = document.getElementById('test-output');
    testDiv.style.display = 'block';
    testDiv.innerHTML = '';

    log(`✅ TEST ROTACIÓN (2 seg por slide)`);
    
    const slides = document.querySelectorAll('.hero-slide');
    if (slides.length === 0) {
        log(`❌ NO hay slides para rotar`);
        testDiv.innerHTML += '<p class="warning">No hay slides para probar</p>';
        return;
    }

    let index = 0;
    const rotationTest = setInterval(() => {
        // Remove active from current
        slides.forEach(s => s.classList.remove('active'));
        
        // Add to next
        slides[index].classList.add('active');
        log(`  ➜ Slide ${index + 1} activado`);
        
        index = (index + 1) % slides.length;
        
        // Stop after 3 cycles
        if (index === 0) {
            clearInterval(rotationTest);
            log(`✅ Rotación test completado`);
        }
    }, 2000);

    testDiv.innerHTML += `<p class="info">⏱️ Rotación en progreso (2 seg por slide)...</p>`;
}

// Auto run slider test on page load
document.addEventListener('DOMContentLoaded', function() {
    log('🚀 Página cargada');
    log('Ejecutando test automático en 1 segundo...');
    setTimeout(() => {
        testSlider();
        testImages();
        testCSS();
    }, 1000);
});
</script>

</body>
</html>
