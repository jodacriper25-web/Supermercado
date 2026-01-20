function addToCart(productId, productName) {
    // Aquí podrías disparar una petición AJAX a tu backend de Django
    let countEl = document.getElementById('cart-count');
    let current = parseInt(countEl.innerText);
    countEl.innerText = current + 1;

    // Animación de feedback
    countEl.classList.add('animate-pop');
    setTimeout(() => countEl.classList.remove('animate-pop'), 300);

    // Toast o Alerta profesional
    console.log(`Logística: Añadido ${productName} (ID: ${productId})`);
}