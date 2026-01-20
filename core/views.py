from django.shortcuts import render, redirect
from django.contrib.auth import authenticate, login, logout
from django.contrib.auth.models import User
from django.contrib import messages
from django.contrib.auth.decorators import login_required
from core.models import Categoria, Producto, Pedido  # Todos los modelos necesarios

# 🏠 Página principal
def index(request):
    categorias = Categoria.objects.all()
    productos = Producto.objects.all()

    # Filtrar productos por categoría si se pasa parámetro
    categoria_id = request.GET.get('categoria')
    if categoria_id:
        productos = productos.filter(categoria_id=categoria_id)

    return render(request, 'index.html', {
        'categorias': categorias,
        'productos': productos,
    })

# 👤 Panel del cliente (requiere login)
@login_required
def admin_cliente_view(request):
    pedidos = Pedido.objects.filter(user=request.user).order_by('-creado')
    return render(request, 'admin_cliente.html', {'pedidos': pedidos})

# 🔑 Login
def login_view(request):
    if request.method == "POST":
        username = request.POST.get('username')
        password = request.POST.get('password')
        user = authenticate(request, username=username, password=password)
        if user:
            login(request, user)
            return redirect('index')
        else:
            messages.error(request, "Usuario o contraseña incorrectos")
            return redirect('index')
    return redirect('index')

# 📝 Registro de cliente
def register_view(request):
    if request.method == "POST":
        username = request.POST.get('username')
        email = request.POST.get('email')
        password = request.POST.get('password')
        password2 = request.POST.get('password2')

        # Validaciones
        if password != password2:
            messages.error(request, "Las contraseñas no coinciden")
            return redirect('index')

        if User.objects.filter(username=username).exists():
            messages.error(request, "El nombre de usuario ya existe")
            return redirect('index')

        # Crear usuario
        user = User.objects.create_user(username=username, email=email, password=password)
        user.is_staff = False  # Cliente normal
        user.save()

        login(request, user)  # Loguear automáticamente
        return redirect('index')
    return redirect('index')

# 🚪 Logout
def logout_view(request):
    logout(request)
    return redirect('index')
