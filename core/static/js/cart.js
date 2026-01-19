let items = 0;

function addToCart(productId) {
    items++;
    document.getElementById('cart-count').innerText = items;
}
