// Carrito de compras usando localStorage

const CART_KEY = 'freshwix_cart';

// Obtener carrito del localStorage
function getCart() {
    const cart = localStorage.getItem(CART_KEY);
    return cart ? JSON.parse(cart) : {};
}

// Guardar carrito en localStorage
function saveCart(cart) {
    localStorage.setItem(CART_KEY, JSON.stringify(cart));
    updateCartCount();
}

// Actualizar contador del carrito
function updateCartCount() {
    const cart = getCart();
    const count = Object.values(cart).reduce((sum, qty) => sum + qty, 0);
    const countEl = document.getElementById('cart-count');
    if (countEl) {
        countEl.innerText = count;
    }
}

// Agregar producto al carrito
function addToCart(productId, productName) {
    const cart = getCart();
    cart[productId] = (cart[productId] || 0) + 1;
    saveCart(cart);
    
    // Feedback visual
    showToast(`${productName} agregado al carrito`, 'success');
}

// Eliminar producto del carrito
function removeFromCart(productId) {
    const cart = getCart();
    delete cart[productId];
    saveCart(cart);
    // Recargar para actualizar vista
    location.reload();
}

// Vaciar carrito
function clearCart() {
    localStorage.removeItem(CART_KEY);
    updateCartCount();
}

// Proceder al checkout
function proceedToCheckout() {
    const cart = getCart();
    const count = Object.keys(cart).length;
    
    if (count === 0) {
        showToast('Tu carrito está vacío', 'warning');
        return;
    }
    
    // Redirigir a la página de checkout
    window.location.href = '/checkout/';
}

// Mostrar toast de notificación
function showToast(message, type = 'info') {
    // Crear elemento toast si no existe
    let toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        toastContainer.className = 'position-fixed bottom-0 end-0 p-3';
        toastContainer.style.zIndex = '9999';
        document.body.appendChild(toastContainer);
    }
    
    const bgClass = type === 'success' ? 'bg-success' : type === 'warning' ? 'bg-warning' : 'bg-info';
    const icon = type === 'success' ? 'bi-check-circle' : type === 'warning' ? 'bi-exclamation-triangle' : 'bi-info-circle';
    
    const toastId = 'toast-' + Date.now();
    const toastHtml = `
        <div id="${toastId}" class="toast ${bgClass} text-white" role="alert">
            <div class="toast-header ${bgClass} text-white">
                <i class="bi ${icon} me-2"></i>
                <strong class="me-auto">FreshWix</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        </div>
    `;
    
    toastContainer.insertAdjacentHTML('insertAdjacentHTML', toastHtml);
    const toastEl = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastEl, { delay: 3000 });
    toast.show();
    
    // Eliminar después de ocultar
    toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
}

// Inicializar contador al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    updateCartCount();
});
