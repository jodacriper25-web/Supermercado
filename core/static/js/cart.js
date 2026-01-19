let cartCount = 0;

function addToCart(productId) {
    cartCount++;
    document.getElementById("cart-count").innerText = cartCount;

    const btn = event.target;
    btn.innerHTML = '<i class="bi bi-check-lg me-2"></i>Añadido';
    btn.disabled = true;

    setTimeout(() => {
        btn.innerHTML = '<i class="bi bi-plus-lg me-2"></i>Añadir al carrito';
        btn.disabled = false;
    }, 1500);
}
