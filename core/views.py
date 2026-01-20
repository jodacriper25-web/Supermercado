from django.shortcuts import render, redirect
from django.contrib.auth.models import User
from django.contrib.auth import authenticate, login, logout
from django.contrib import messages
from core.models import Categoria, Producto

# Página principal
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

# Registro de cliente
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
        user.is_staff = False
        user.save()

        # Loguear automáticamente
        login(request, user)
        messages.success(request, "Registro exitoso. ¡Bienvenido!")
        return redirect('index')

    return redirect('index')

# Login
def login_view(request):
    if request.method == 'POST':
        username = request.POST['username']
        password = request.POST['password']
        user = authenticate(request, username=username, password=password)
        if user is not None:
            login(request, user)
            return redirect('index')
        else:
            messages.error(request, "Usuario o contraseña incorrectos")
            return redirect('index')
    return redirect('index')

# Logout
def logout_view(request):
    logout(request)
    messages.success(request, "Has cerrado sesión correctamente")
    return redirect('index')
