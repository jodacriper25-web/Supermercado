def get_cart(request):
    return request.session.get('cart', {})

def add_to_cart(request, product_id):
    cart = get_cart(request)
    cart[str(product_id)] = cart.get(str(product_id), 0) + 1
    request.session['cart'] = cart

def clear_cart(request):
    request.session['cart'] = {}
