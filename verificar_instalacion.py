#!/usr/bin/env python
"""
Script de verificación del proyecto Supermercado Yaruquíes
Verifica que todos los archivos y configuraciones necesarias estén en su lugar
"""

import os
import sys
from pathlib import Path

# Colores para la terminal
class Colors:
    GREEN = '\033[92m'
    RED = '\033[91m'
    YELLOW = '\033[93m'
    BLUE = '\033[94m'
    RESET = '\033[0m'

def check_file(path, description):
    """Verifica si un archivo existe"""
    if Path(path).exists():
        print(f"{Colors.GREEN}✓{Colors.RESET} {description}")
        return True
    else:
        print(f"{Colors.RED}✗{Colors.RESET} {description}")
        return False

def check_directory(path, description):
    """Verifica si un directorio existe"""
    if Path(path).is_dir():
        print(f"{Colors.GREEN}✓{Colors.RESET} {description}")
        return True
    else:
        print(f"{Colors.RED}✗{Colors.RESET} {description} - Crear con: mkdir -p {path}")
        return False

def check_import(module_name, pip_name=None):
    """Verifica si un módulo Python está disponible"""
    try:
        __import__(module_name)
        print(f"{Colors.GREEN}✓{Colors.RESET} {module_name} instalado")
        return True
    except ImportError:
        pip_cmd = pip_name or module_name
        print(f"{Colors.RED}✗{Colors.RESET} {module_name} NO instalado - Instalar: pip install {pip_cmd}")
        return False

def main():
    print(f"\n{Colors.BLUE}{'='*60}{Colors.RESET}")
    print(f"{Colors.BLUE}VERIFICACIÓN DEL PROYECTO SUPERMERCADO YARUQUÍES{Colors.RESET}")
    print(f"{Colors.BLUE}{'='*60}{Colors.RESET}\n")
    
    base_path = Path(__file__).parent
    issues = 0
    
    # 1. Verificar archivos críticos
    print(f"{Colors.YELLOW}1. Archivos Críticos:{Colors.RESET}")
    files_to_check = [
        ('manage.py', 'manage.py (Gestor de Django)'),
        ('requirements.txt', 'requirements.txt (Dependencias)'),
        ('supermercado/settings.py', 'settings.py (Configuración)'),
        ('supermercado/urls.py', 'urls.py (Rutas)'),
        ('core/models.py', 'models.py (Modelos de BD)'),
        ('core/views.py', 'views.py (Vistas)'),
        ('core/management/commands/import_excel.py', 'Script de importación Excel'),
        ('core/templates/index.html', 'Página principal'),
        ('core/templates/quienes_somos.html', 'Página Quiénes Somos'),
        ('core/templates/category.html', 'Template de categorías'),
        ('core/static/css/main.css', 'Estilos CSS'),
    ]
    
    for file_path, description in files_to_check:
        full_path = base_path / file_path
        if not check_file(full_path, description):
            issues += 1
    
    # 2. Verificar directorios
    print(f"\n{Colors.YELLOW}2. Directorios Necesarios:{Colors.RESET}")
    dirs_to_check = [
        ('media', 'Carpeta media (para imágenes)'),
        ('media/productos', 'Carpeta de imágenes de productos'),
        ('core/static/img/hero', 'Carpeta de imágenes hero'),
        ('core/templates', 'Carpeta de templates'),
        ('core/static/css', 'Carpeta de estilos'),
        ('core/static/js', 'Carpeta de JavaScript'),
    ]
    
    for dir_path, description in dirs_to_check:
        full_path = base_path / dir_path
        if not check_directory(full_path, description):
            issues += 1
    
    # 3. Verificar dependencias Python
    print(f"\n{Colors.YELLOW}3. Dependencias Python:{Colors.RESET}")
    modules_to_check = [
        ('django', 'django'),
        ('PIL', 'Pillow'),
        ('openpyxl', 'openpyxl'),
        ('xlrd', 'xlrd'),
    ]
    
    for module, pip_name in modules_to_check:
        if not check_import(module, pip_name):
            issues += 1
    
    # 4. Verificar archivos de documentación
    print(f"\n{Colors.YELLOW}4. Documentación:{Colors.RESET}")
    docs_to_check = [
        ('GUIA_RAPIDA.md', 'Guía rápida de uso'),
        ('INSTRUCCIONES_IMPORTAR_EXCEL.md', 'Instrucciones importación Excel'),
        ('CAMBIOS_REALIZADOS_2026_02_04.md', 'Resumen de cambios'),
    ]
    
    for file_path, description in docs_to_check:
        full_path = base_path / file_path
        check_file(full_path, description)
    
    # 5. Resumen y recomendaciones
    print(f"\n{Colors.BLUE}{'='*60}{Colors.RESET}")
    if issues == 0:
        print(f"{Colors.GREEN}✓ TODO ESTÁ BIEN CONFIGURADO{Colors.RESET}")
        print(f"\n{Colors.YELLOW}Próximos pasos:{Colors.RESET}")
        print(f"1. Coloca imágenes en: {base_path}/media/productos/")
        print(f"2. Ejecuta: python manage.py import_excel data/Export.xls")
        print(f"3. Inicia: python manage.py runserver 127.0.0.1:8000")
        print(f"4. Abre: http://127.0.0.1:8000")
    else:
        print(f"{Colors.RED}⚠ Se encontraron {issues} problema(s){Colors.RESET}")
        print(f"\n{Colors.YELLOW}Acciones recomendadas:{Colors.RESET}")
        print(f"1. Instala dependencias: pip install -r requirements.txt")
        print(f"2. Crea directorios faltantes")
        print(f"3. Verifica que todos los archivos estén en su lugar")
    
    print(f"{Colors.BLUE}{'='*60}{Colors.RESET}\n")
    
    return 0 if issues == 0 else 1

if __name__ == '__main__':
    sys.exit(main())
