// Manejo simple de carrito en localStorage
export function addToCart(id){
  let cart = JSON.parse(localStorage.getItem('cart')||'{}');
  cart[id] = (cart[id]||0)+1;
  localStorage.setItem('cart', JSON.stringify(cart));
}

export function getCart(){
  return JSON.parse(localStorage.getItem('cart')||'{}');
}
