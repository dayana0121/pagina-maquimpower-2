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
// Los sliders PRINCIPALES se inicializan en footer.php con jQuery $(document).ready()
// Esta función es un respaldo seguro que NO re-inicializa si footer.php ya lo hizo.

function initSliders() {
    // Los sliders se inicializan en footer.php para evitar conflictos de doble-init.
    // Esta función solo existe para que la llamada en DOMContentLoaded no falle.
    if (typeof $ === 'undefined' || !$.fn.slick) {
        console.warn('⚠️ jQuery o Slick no disponible aún (se inicializará desde footer)');
        return;
    }
    console.log('ℹ️ initSliders() llamado desde main.js — sliders se gestionan desde footer.php');
}

// --- 7. TIKTOK SLIDER ---
function initTikTokCarousel() {
    // TikTok carousel se inicializa en footer.php.
    // Esta función solo existe para que la llamada en DOMContentLoaded no falle.
    console.log('ℹ️ initTikTokCarousel() llamado desde main.js — se gestiona desde footer.php');
}