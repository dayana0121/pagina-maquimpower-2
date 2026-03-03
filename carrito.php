<?php require_once 'includes/header.php'; ?>

<style>
    /* FONDO INMERSIVO */
    .cart-bg {
        background: #f8f9fa;
        min-height: 90vh;
        padding-top: 50px;
        padding-bottom: 80px;
        position: relative;
        overflow: hidden;
    }

    /* CANVAS PARTÍCULAS */
    #particleCanvas {
        position: absolute; top: 0; left: 0; width: 100%; height: 100%;
        pointer-events: none; z-index: 0;
    }
    
    .container { position: relative; z-index: 1; }
    
    /* TARJETAS CRISTAL */
    .cart-item-card {
        background: rgba(255, 255, 255, 0.95);
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        transition: 0.3s;
        backdrop-filter: blur(5px);
    }
    .cart-item-card:hover {
        border-color: var(--primary);
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    }

    /* RESUMEN DARK */
    .summary-card {
        background: #111; color: white;
        border-radius: 12px; padding: 30px;
        border-top: 5px solid var(--primary);
        box-shadow: 0 20px 50px rgba(0,0,0,0.3);
        position: sticky; top: 100px;
    }
    .qty-input { background: #f4f4f4; border: none; font-weight: 800; color: #333; }
    
    /* ESTILO ALERTAS MAQUIMPOWER */
    .swal2-popup.mp-info-popup {
        background: #1a1a1a !important;
        border: 1px solid #333;
        border-top: 4px solid #FF4500; /* Naranja */
        color: #fff;
    }
    .swal2-title { color: #fff !important; text-transform: uppercase; font-size: 1.2rem; }
    .swal2-html-container { color: #ccc !important; }
    .swal2-confirm {
        background: #FF4500 !important;
        box-shadow: 0 5px 15px rgba(255, 69, 0, 0.4) !important;
        font-weight: 800 !important;
        border-radius: 50px !important;
        text-transform: uppercase;
    }
</style>

<div class="cart-bg">
    <canvas id="particleCanvas"></canvas>

    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-5">
            <div>
                <h6 class="text-primary fw-bold ls-2 mb-1 text-uppercase"><i class="bi bi-bag-check-fill me-2"></i> Revisión de Pedido</h6>
                <h1 class="display-5 fw-black m-0 text-dark text-uppercase">Mi Carrito</h1>
            </div>
            <a href="/" class="btn btn-outline-dark rounded-pill fw-bold px-4 d-none d-md-inline-block border-2">
                <i class="bi bi-arrow-left me-2"></i> SEGUIR COMPRANDO
            </a>
        </div>

        <div class="row g-5">
            <div class="col-lg-8">
                <div id="cart-container"></div>
                
                <div id="cart-empty" class="text-center py-5 d-none bg-white rounded-4 border border-dashed shadow-sm">
                    <div class="mb-3"><span class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;"><i class="bi bi-cart-x fs-1 text-muted opacity-50"></i></span></div>
                    <h3 class="fw-bold text-dark">Tu carrito está vacío</h3>
                    <a href="/categoria.php?c=ofertas" class="btn btn-primary rounded-pill px-5 fw-bold mt-3 shadow-sm">VER CATÁLOGO</a>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="summary-card">
                    <h4 class="fw-black text-uppercase mb-4 text-white">Resumen</h4>
                    <div class="d-flex justify-content-between mb-3 text-secondary"><span>Subtotal</span><span id="cart-subtotal" class="fw-bold text-white">S/ 0.00</span></div>
                    <div class="d-flex justify-content-between mb-3 text-secondary"><span>IGV</span><span class="text-white">Incluido</span></div>
                    <hr class="border-secondary">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <span class="text-uppercase fw-bold text-white-50">Total</span>
                        <span id="cart-total" class="fw-black text-primary fs-2">S/ 0.00</span>
                    </div>
                    <a href="/checkout.php" class="btn btn-light w-100 fw-bold py-3 rounded-pill shadow-none" id="btn-checkout">PROCESAR COMPRA <i class="bi bi-arrow-right ms-2"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// --- 1. ANIMACIÓN OLA DE PARTÍCULAS ---
const canvas = document.getElementById('particleCanvas');
const ctx = canvas.getContext('2d');
let particles = [];
const mouse = { x: null, y: null, radius: 150 };

function resizeCanvas() { canvas.width = canvas.offsetWidth; canvas.height = canvas.offsetHeight; initParticles(); }
window.addEventListener('resize', resizeCanvas);
window.addEventListener('mousemove', (e) => {
    const rect = canvas.getBoundingClientRect();
    mouse.x = e.clientX - rect.left; mouse.y = e.clientY - rect.top;
});
window.addEventListener('mouseleave', () => { mouse.x = undefined; mouse.y = undefined; });

class Particle {
    constructor() {
        this.x = Math.random() * canvas.width; this.y = Math.random() * canvas.height;
        this.size = Math.random() * 2 + 0.5; this.baseX = this.x; this.baseY = this.y;
        this.density = (Math.random() * 10) + 2; this.angle = Math.random() * 360;
    }
    draw() {
        ctx.fillStyle = 'rgba(255, 69, 0, 0.12)'; ctx.beginPath();
        ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2); ctx.fill();
    }
    update() {
        this.angle += 0.015;
        let waveX = Math.cos(this.angle) * 8; let waveY = Math.sin(this.angle) * 8;
        let dx = mouse.x - this.x; let dy = mouse.y - this.y;
        let distance = Math.sqrt(dx * dx + dy * dy);
        if (mouse.x && distance < mouse.radius) {
            let force = (mouse.radius - distance) / mouse.radius;
            this.x -= (dx / distance) * force * this.density * 3;
            this.y -= (dy / distance) * force * this.density * 3;
        } else {
            let targetX = this.baseX + waveX; let targetY = this.baseY + waveY;
            if (this.x !== targetX) this.x -= (this.x - targetX) / 25;
            if (this.y !== targetY) this.y -= (this.y - targetY) / 25;
        }
    }
}
function initParticles() {
    particles = [];
    for (let i = 0; i < (canvas.width * canvas.height) / 10000; i++) particles.push(new Particle());
}
function animateParticles() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    particles.forEach(p => { p.draw(); p.update(); });
    requestAnimationFrame(animateParticles);
}
resizeCanvas(); animateParticles();

// --- 2. LÓGICA CARRITO ---
document.addEventListener('DOMContentLoaded', renderizarCarrito);

function renderizarCarrito() {
    let carrito = JSON.parse(localStorage.getItem('maquim_cart')) || [];
    let container = document.getElementById('cart-container');
    let emptyMsg = document.getElementById('cart-empty');
    let total = 0;

    if(carrito.length === 0) {
        container.innerHTML = ''; emptyMsg.classList.remove('d-none');
        document.getElementById('btn-checkout').classList.add('disabled');
        document.getElementById('cart-subtotal').innerText = 'S/ 0.00';
        document.getElementById('cart-total').innerText = 'S/ 0.00';
        return;
    } else {
        emptyMsg.classList.add('d-none'); document.getElementById('btn-checkout').classList.remove('disabled');
    }

    let html = '';
    carrito.forEach((item, index) => {
        let subtotal = item.precio * item.cantidad;
        total += subtotal;
        html += `
            <div class="cart-item-card mb-3 p-3">
                <div class="row g-0 align-items-center">
                    <div class="col-3 col-md-2 text-center"><img src="${item.img}" class="img-fluid rounded" style="max-height: 70px; object-fit: contain;"></div>
                    <div class="col-9 col-md-10 ps-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="fw-bold text-dark mb-1 text-truncate" style="max-width: 200px;">${item.nombre}</h6>
                                <span class="badge bg-light text-dark border rounded-1" style="font-size:0.65rem;">SKU: ${item.sku}</span>
                            </div>
                            <button class="btn btn-link text-danger p-0 opacity-50" onclick="eliminarItem(${index})"><i class="bi bi-trash3-fill fs-5"></i></button>
                        </div>
                        <div class="d-flex justify-content-between align-items-end mt-3">
                            <div class="input-group input-group-sm" style="width: 110px;">
                                <button class="btn btn-outline-secondary" onclick="cambiarCantidad(${index}, ${item.cantidad - 1})"><i class="bi bi-dash"></i></button>
                                <input type="text" class="form-control text-center qty-input" value="${item.cantidad}" readonly>
                                <button class="btn btn-outline-secondary" onclick="cambiarCantidad(${index}, ${item.cantidad + 1})"><i class="bi bi-plus"></i></button>
                            </div>
                            <div class="text-end">
                                <small class="d-block text-muted" style="font-size:0.7rem">Unit: S/ ${item.precio.toFixed(2)}</small>
                                <span class="fw-black text-dark fs-5">S/ ${subtotal.toFixed(2)}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>`;
    });
    container.innerHTML = html;
    let totalF = "S/ " + total.toLocaleString('es-PE', {minimumFractionDigits: 2});
    document.getElementById('cart-subtotal').innerText = totalF;
    document.getElementById('cart-total').innerText = totalF;
}

// --- 3. LÓGICA DE CANTIDAD (VENTANA INFO IMPORTANTE) ---
function cambiarCantidad(i, n) {
    let c = JSON.parse(localStorage.getItem('maquim_cart'));
    if (n < 1) return;

    // Recuperamos el Stock Máximo real. Si no existe, usamos 9999 temporalmente
    let max = c[i].maxStock !== undefined ? c[i].maxStock : 9999; 

    if (n > max) {
        // VENTANA DE INFO IMPORTANTE (CENTRADA Y ELEGANTE)
        Swal.fire({
            title: 'INFORMACIÓN DE STOCK',
            html: `<p>Actualmente disponemos de <b>${max} unidades</b> en nuestro almacén central.</p>
                   <small class="text-muted">Si necesitas un volumen mayor, contacta a ventas corporativas.</small>`,
            icon: 'info', // Icono informativo azul/neutro
            confirmButtonText: 'ENTENDIDO',
            customClass: { popup: 'mp-info-popup' } // Clase personalizada CSS
        });
        return;
    }

    c[i].cantidad = n;
    localStorage.setItem('maquim_cart', JSON.stringify(c));
    renderizarCarrito();
    actualizarBadge();
}

function eliminarItem(i) {
    Swal.fire({
        title: '¿QUITAR PRODUCTO?',
        text: "Se eliminará de tu lista.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'SÍ, QUITAR',
        cancelButtonText: 'CANCELAR',
        customClass: { popup: 'mp-info-popup' }
    }).then((result) => {
        if (result.isConfirmed) {
            let c = JSON.parse(localStorage.getItem('maquim_cart'));
            c.splice(i, 1);
            localStorage.setItem('maquim_cart', JSON.stringify(c));
            renderizarCarrito();
            actualizarBadge();
        }
    });
}

function actualizarBadge() {
    let c = JSON.parse(localStorage.getItem('maquim_cart')) || [];
    let count = c.reduce((sum, item) => sum + item.cantidad, 0);
    document.querySelectorAll('.badge.bg-danger').forEach(el => el.innerText = count);
}
// Pega esto dentro de tu <script> en cart.php

function cambiarCantidad(i, n) {
    let c = JSON.parse(localStorage.getItem('maquim_cart'));
    
    // Validación mínima
    if(n < 1) return;
    
    // IMPORTANTE: Leemos el stock máximo guardado. 
    // Si por error no existe, ponemos 9999 para no bloquear la venta.
    let max = (c[i].maxStock !== undefined && c[i].maxStock !== null) ? c[i].maxStock : 9999; 

    // Si intenta superar el stock real
    if(n > max) { 
        Swal.fire({
            title: 'STOCK CORPORATIVO',
            html: `
                <div style="text-align: left; padding: 0 10px;">
                    <p style="color: #ccc; margin-bottom: 10px;">
                        Actualmente disponemos de <b style="color: #FF4500; font-size: 1.2em;">${max} unidades</b> 
                        en nuestro almacén central para entrega inmediata.
                    </p>
                    <hr style="border-color: #333;">
                    <small style="color: #888;">
                        <i class="bi bi-info-circle"></i> Para pedidos de mayor volumen, 
                        por favor contacta a nuestra área de ventas corporativas.
                    </small>
                </div>
            `,
            icon: 'info',
            background: '#111', // Fondo Negro
            color: '#fff',      // Texto Blanco
            confirmButtonText: 'ENTENDIDO',
            confirmButtonColor: '#333', // Botón discreto
            buttonsStyling: true,
            showCloseButton: true,
            customClass: {
                popup: 'border-left-orange', // Necesitas el CSS de abajo
                confirmButton: 'btn-confirm-custom'
            }
        }); 
        return; 
    }
    
    // Si todo está bien, guardamos
    c[i].cantidad = n;
    localStorage.setItem('maquim_cart', JSON.stringify(c));
    renderizarCarrito();
    
    // Actualizamos el globito rojo del header si existe la función
    if (typeof actualizarBadge === 'function') actualizarBadge();
}
</script>

<?php require_once 'includes/footer.php'; ?>