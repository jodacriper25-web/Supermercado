"""
Rate limiting utilities for protecting login endpoints
"""
from django.contrib import messages
from django.shortcuts import redirect
from functools import wraps
import logging
import time

logger = logging.getLogger('django.security')

# Configuration
LOGIN_ATTEMPT_LIMIT = 5  # Max attempts
LOGIN_ATTEMPT_WINDOW = 300  # 5 minutes in seconds

# In-memory storage for rate limiting (works in development without Redis/Memcached)
login_attempts_cache = {}


def get_client_ip(request):
    """Obtener IP del cliente"""
    x_forwarded_for = request.META.get('HTTP_X_FORWARDED_FOR')
    if x_forwarded_for:
        ip = x_forwarded_for.split(',')[0]
    else:
        ip = request.META.get('REMOTE_ADDR')
    return ip


def rate_limit_login(view_func):
    """
    Decorador para rate limiting en endpoint de login
    Limita a 5 intentos en 5 minutos por IP
    """
    @wraps(view_func)
    def wrapper(request, *args, **kwargs):
        if request.method == 'POST':
            client_ip = get_client_ip(request)
            current_time = time.time()
            
            # Limpiar intentos antiguos (más viejos que la ventana)
            window_start = current_time - LOGIN_ATTEMPT_WINDOW
            if client_ip in login_attempts_cache:
                login_attempts_cache[client_ip] = [
                    t for t in login_attempts_cache[client_ip] if t > window_start
                ]
            
            attempts = len(login_attempts_cache.get(client_ip, []))
            
            if attempts >= LOGIN_ATTEMPT_LIMIT:
                # Log security incident
                logger.warning(
                    f'Rate limit exceeded for IP {client_ip}. '
                    f'Attempts: {attempts + 1} / {LOGIN_ATTEMPT_LIMIT}'
                )
                
                messages.error(
                    request,
                    f'Demasiados intentos de login fallidos. '
                    f'Intenta de nuevo en {LOGIN_ATTEMPT_WINDOW // 60} minutos.'
                )
                return redirect(request.path)
            
            # Registrar este intento
            if client_ip not in login_attempts_cache:
                login_attempts_cache[client_ip] = []
            login_attempts_cache[client_ip].append(current_time)
        
        return view_func(request, *args, **kwargs)
    
    return wrapper


def check_login_success(request):
    """
    Limpiar intentos fallidos después de login exitoso
    """
    client_ip = get_client_ip(request)
    if client_ip in login_attempts_cache:
        del login_attempts_cache[client_ip]
    logger.info(f'Successful login from IP {client_ip}')


def log_login_attempt(request, username, success=False):
    """
    Log login attempts for security audit trail
    """
    client_ip = get_client_ip(request)
    status = 'SUCCESS' if success else 'FAILED'
    
    logger.warning(
        f'Login attempt [{status}] - Username: {username}, IP: {client_ip}'
    )
