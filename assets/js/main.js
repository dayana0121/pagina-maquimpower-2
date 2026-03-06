if (typeof window.Notify === 'undefined') {
    window.Notify = {
        show: (icon, title, text) => {
            Swal.fire({
                icon: icon,
                title: title,
                text: text,
                confirmButtonText: 'OK',
                confirmButtonColor: icon === 'error' ? '#d33' : '#FF4500'
            });
        },
        error: (msg) => Notify.show('error', 'Oops...', msg),
        info: (title, msg) => Notify.show('info', title, msg),
        toast: (title) => {
            const Toast = Swal.mixin({
                toast: true,
                position: 'bottom-center',
                showConfirmButton: false,
                timer: 2000,
                icon: false,
                customClass: {
                    popup: 'bg-dark text-white rounded-pill px-4 py-2 shadow-lg mb-5 border border-secondary'
                }
            });

            Toast.fire({
                html: `<div class="d-flex align-items-center gap-2">
                        <i class="bi bi-check-circle-fill text-success fs-5"></i>
                        <span class="fw-bold">${title}</span>
                      </div>`
            });
        }
    };
}

// --- 2. INICIALIZACIÓN AL CARGAR EL DOM ---
document.addEventListener('DOMContentLoaded', () => {
    actualizarBadge();
    initMegaMenu();
    initLiveSearch();
    initSliders();
    initTikTokCarousel();
});

// --- 3. LÓGICA DE CARRITO ---
function addToCart(id, sku, nombre, precio, img, maxStock) {
    let qtyInput = document.getElementById('cantidad');
    let cantidadDeseada = qtyInput ? parseInt(qtyInput.value) : 1;

    if (isNaN(cantidadDeseada) || cantidadDeseada < 1) {
        return Notify.error("Ingresa una cantidad válida.");
    }

    let carrito = JSON.parse(localStorage.getItem('maquim_cart')) || [];
    let existente = carrito.find(item => item.id === id);
    let cantidadFinal = (existente ? existente.cantidad : 0) + cantidadDeseada;

    if (cantidadFinal > maxStock) {
        return Notify.error(`Solo nos quedan ${maxStock} unidades.`);
    }

    if (existente) {
        existente.cantidad += cantidadDeseada;
    } else {
        carrito.push({ id, sku, nombre, precio, img, cantidad: cantidadDeseada, maxStock });
    }

    localStorage.setItem('maquim_cart', JSON.stringify(carrito));
    actualizarBadge();
    
    // Vibración (Móvil)
    if (navigator.vibrate) navigator.vibrate(50);
    
    Notify.toast(`Agregado: ${nombre}`);
}

function actualizarBadge() {
    let carrito = JSON.parse(localStorage.getItem('maquim_cart')) || [];
    let totalItems = carrito.reduce((acc, item) => acc + item.cantidad, 0);
    let totalDinero = carrito.reduce((acc, item) => acc + (item.precio * item.cantidad), 0);

    document.querySelectorAll('.cart-badge, .badge.bg-danger').forEach(b => {
        b.innerText = totalItems;
        b.classList.add('animate__pulse');
    });

    let label = document.getElementById('header-total');
    if(label) label.innerText = "S/ " + totalDinero.toLocaleString('es-PE', {minimumFractionDigits: 2});
}

// --- 4. MEGA MENÚ (Hover JS) ---
function initMegaMenu() {
    const items = document.querySelectorAll('.nav-item-mega');
    items.forEach(item => {
        const menu = item.querySelector('.mega-menu');
        if (!menu) return;
        item.addEventListener('mouseenter', () => {
            menu.style.display = 'block';
            setTimeout(() => { menu.style.opacity = '1'; menu.style.transform = 'translateY(0)'; }, 10);
        });
        item.addEventListener('mouseleave', () => {
            menu.style.opacity = '0';
            menu.style.transform = 'translateY(10px)';
            setTimeout(() => { menu.style.display = 'none'; }, 300);
        });
    });
}

// --- 5. BUSCADOR EN VIVO ---
function initLiveSearch() {
    const inputs = document.querySelectorAll('.search-modern input');
    inputs.forEach(input => {
        if(input.dataset.active) return;
        input.dataset.active = "true";

        let resultsDiv = document.createElement('div');
        resultsDiv.className = 'live-search-results shadow-lg rounded border';
        resultsDiv.style.display = 'none';
        input.parentNode.style.position = 'relative';
        input.parentNode.appendChild(resultsDiv);

        input.addEventListener('input', function() {
            let q = this.value;
            if (q.length < 2) { resultsDiv.style.display = 'none'; return; }

            fetch('/pagina/api/live_search.php?q=' + encodeURIComponent(q))
                .then(res => res.json())
                .then(data => {
                    if (data && data.length > 0) {
                        let html = '<ul class="list-group list-group-flush text-start">';
                        data.forEach(p => {
                            let img = p.imagen_url || '/pagina/assets/img/no-photo.png';
                            html += `<li class="list-group-item list-group-item-action p-2">
                                <a href="/pagina/producto/${p.slug}" class="d-flex align-items-center text-decoration-none text-dark">
                                    <img src="${img}" style="width:40px;height:40px;object-fit:contain;margin-right:10px">
                                    <div><div class="fw-bold small">${p.nombre}</div><small class="text-primary fw-bold">S/ ${parseFloat(p.precio).toFixed(2)}</small></div>
                                </a></li>`;
                        });
                        html += '</ul>';
                        resultsDiv.innerHTML = html;
                        resultsDiv.style.display = 'block';
                    } else { resultsDiv.style.display = 'none'; }
                });
        });

        document.addEventListener('click', (e) => {
            if (!input.contains(e.target) && !resultsDiv.contains(e.target)) resultsDiv.style.display = 'none';
        });
    });
}

// --- 6. CARRUSELES (SLICK) - PRODUCTOS Y VIDEOS ---
// Reemplaza la función initSliders() COMPLETA:

function initSliders() {
    // Esperar a que jQuery esté completamente listo
    if (typeof $ === 'undefined') {
        console.warn('jQuery no está cargado');
        return;
    }

    // Dar más tiempo para que el DOM esté 100% listo
    setTimeout(() => {
        // ===== CARRUSEL DE PRODUCTOS =====
        const $productCarousel = $('.product-carousel');
        if ($productCarousel.length > 0 && $.fn.slick) {
            // Verificar que tenga elementos
            if ($productCarousel.find('.p-1').length > 0) {
                try {
                    // Destruir si ya existe
                    if ($productCarousel.hasClass('slick-initialized')) {
                        $productCarousel.slick('unslick');
                    }

                    $productCarousel.slick({
                        slidesToShow: 4,
                        slidesToScroll: 1,
                        autoplay: true,
                        autoplaySpeed: 3000,
                        prevArrow: $('.prev-prod'),
                        nextArrow: $('.next-prod'),
                        infinite: true,
                        dots: false,
                        responsive: [
                            {
                                breakpoint: 1024,
                                settings: { slidesToShow: 3 }
                            },
                            {
                                breakpoint: 768,
                                settings: { slidesToShow: 2, arrows: false }
                            },
                            {
                                breakpoint: 480,
                                settings: { slidesToShow: 1, arrows: false }
                            }
                        ]
                    });
                    console.log('✅ Product Carousel inicializado correctamente');
                } catch(e) {
                    console.error('❌ Error inicializando product-carousel:', e.message);
                }
            } else {
                console.warn('⚠️ No hay elementos en product-carousel');
            }
        } else {
            console.warn('⚠️ product-carousel no encontrado o Slick no disponible');
        }

        // ===== CARRUSEL DE VIDEOS =====
        const $videoCarousel = $('.video-carousel');
        if ($videoCarousel.length > 0 && $.fn.slick) {
            // Verificar que tenga elementos
            if ($videoCarousel.find('.p-3').length > 0) {
                try {
                    // Destruir si ya existe
                    if ($videoCarousel.hasClass('slick-initialized')) {
                        $videoCarousel.slick('unslick');
                    }

                    $videoCarousel.slick({
                        slidesToShow: 3,
                        slidesToScroll: 1,
                        centerMode: true,
                        arrows: false,
                        infinite: true,
                        dots: false,
                        autoplay: false,
                        responsive: [
                            {
                                breakpoint: 1024,
                                settings: { slidesToShow: 2 }
                            },
                            {
                                breakpoint: 768,
                                settings: {
                                    slidesToShow: 1,
                                    centerMode: true,
                                    centerPadding: '40px'
                                }
                            }
                        ]
                    });
                    console.log('✅ Video Carousel inicializado correctamente');
                } catch(e) {
                    console.error('❌ Error inicializando video-carousel:', e.message);
                }
            } else {
                console.warn('⚠️ No hay elementos en video-carousel');
            }
        } else {
            console.warn('⚠️ video-carousel no encontrado o Slick no disponible');
        }

    }, 500); // Aumentar delay a 500ms para asegurar que el DOM esté listo
}

// --- 7. TIKTOK SLIDER (NUEVA FUNCIÓN SEPARADA) ---
// Agregar esta función NUEVA después de initSliders():

// --- 8. INICIALIZAR CARRUSEL DE TIKTOK ---
// En la función initTikTokCarousel(), CAMBIAR:

function initTikTokCarousel() {
    if (typeof $ === 'undefined' || !$.fn.slick) {
        console.warn('jQuery o Slick no está disponible');
        return;
    }

    setTimeout(() => {
        const $tiktokCarousel = $('.tiktok-carousel');
        
        if ($tiktokCarousel.length > 0) {
            // Agregar click handlers a los videos
            document.querySelectorAll('.tiktok-video-wrapper').forEach(video => {
                video.addEventListener('click', function() {
                    const url = this.dataset.url;
                    if (url) {
                        window.open(url, '_blank', 'width=600,height=800');
                    }
                });
            });

            // Solo inicializar carrusel si hay videos
            if ($tiktokCarousel.find('.p-2').length > 0) {  // ✅ Verificar que tenga elementos
                if (!$tiktokCarousel.hasClass('slick-initialized')) {
                    try {
                        $tiktokCarousel.slick({
                            slidesToShow: 3,
                            slidesToScroll: 1,
                            centerMode: true,
                            centerPadding: '60px',
                            arrows: true,
                            dots: false,
                            infinite: true,
                            autoplay: false,
                            prevArrow: '<button class="btn btn-light rounded-circle prev-tiktok shadow-sm" style="z-index:10;"><i class="bi bi-chevron-left"></i></button>',
                            nextArrow: '<button class="btn btn-light rounded-circle next-tiktok shadow-sm" style="z-index:10;"><i class="bi bi-chevron-right"></i></button>',
                            responsive: [
                                {
                                    breakpoint: 1024,
                                    settings: {
                                        slidesToShow: 2,
                                        centerPadding: '40px'
                                    }
                                },
                                {
                                    breakpoint: 768,
                                    settings: {
                                        slidesToShow: 1,
                                        centerMode: true,
                                        centerPadding: '30px',
                                        arrows: false
                                    }
                                }
                            ]
                        });
                        console.log('✅ TikTok Carousel inicializado correctamente');
                    } catch(e) {
                        console.error('❌ Error inicializando tiktok-carousel:', e.message);
                    }
                }
            } else {
                console.warn('⚠️ No hay videos de TikTok para mostrar');
            }
        } else {
            console.warn('⚠️ Elemento .tiktok-carousel no encontrado');
        }
    }, 600);
}

// Llamar la función en DOMContentLoaded
document.addEventListener('DOMContentLoaded', () => {
    actualizarBadge();
    initMegaMenu();
    initLiveSearch();
    initSliders();
    initTikTokCarousel();  // ← AGREGAR ESTA LÍNEA
});