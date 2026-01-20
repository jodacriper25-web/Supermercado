from django.shortcuts import render, redirect
from django.contrib.auth import login
from django.contrib import messages
from .forms import RegistroForm

def registro(request):
    if request.method == 'POST':
        form = RegistroForm(request.POST)
        if form.is_valid():
            # Crear usuario pero no hacer commit inmediato
            user = form.save(commit=False)
            # Hashear la contraseña
            user.set_password(form.cleaned_data['password'])
            user.is_staff = False  # Asegurarse que sea cliente normal
            user.save()
            
            # Loguear automáticamente
            login(request, user)
            messages.success(request, "Registro exitoso. Bienvenido!")
            return redirect('index')
        else:
            # Si el formulario no es válido, mostrar errores
            for field, errors in form.errors.items():
                for error in errors:
                    messages.error(request, f"{field}: {error}")
            return redirect('index')
    
    # Si es GET, solo redirigir al index
    return redirect('index')
