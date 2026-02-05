"""
Rate limiting utilities for protecting login endpoints
"""
from django.core.cache import cache
from django.contrib import messages
from django.shortcuts import redirect
from functools import wraps
import logging

logger = logging.getLogger('django.security')

# Configuration
LOGIN_ATTEMPT_LIMIT = 5  # Max attempts
LOGIN_ATTEMPT_WINDOW = 300  # 5 minutes in seconds


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
            cache_key = f'login_attempts_{client_ip}'
            
            # Obtener intentos actuales
            attempts = cache.get(cache_key, 0)
            
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
            
            # Incrementar contador
            cache.set(cache_key, attempts + 1, LOGIN_ATTEMPT_WINDOW)
        
        return view_func(request, *args, **kwargs)
    
    return wrapper


def check_login_success(request):
    """
    Limpiar intentos fallidos después de login exitoso
    """
    client_ip = get_client_ip(request)
    cache_key = f'login_attempts_{client_ip}'
    cache.delete(cache_key)
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
