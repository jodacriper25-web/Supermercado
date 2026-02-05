"""
Django management command for automatic database backups
"""
from django.core.management.base import BaseCommand
from django.conf import settings
import shutil
import os
from datetime import datetime
import logging

logger = logging.getLogger('core')


class Command(BaseCommand):
    help = 'Backup automatico de la base de datos SQLite'

    def add_arguments(self, parser):
        parser.add_argument(
            '--keep',
            type=int,
            default=7,
            help='Numero de backups anteriores a mantener (default: 7)'
        )

    def handle(self, *args, **options):
        backup_dir = os.path.join(settings.BASE_DIR, 'backups')
        
        # Crear carpeta backups si no existe
        if not os.path.exists(backup_dir):
            os.makedirs(backup_dir)
            self.stdout.write(self.style.SUCCESS(f'Carpeta creada: {backup_dir}'))

        # Generar nombre con timestamp
        timestamp = datetime.now().strftime('%Y%m%d_%H%M%S')
        db_file = os.path.join(settings.BASE_DIR, 'db.sqlite3')
        
        # Validar que la BD existe
        if not os.path.exists(db_file):
            self.stdout.write(self.style.ERROR(f'Error: Base de datos no encontrada en {db_file}'))
            return

        backup_file = os.path.join(backup_dir, f'db_backup_{timestamp}.sqlite3')

        try:
            # Copiar archivo
            shutil.copy2(db_file, backup_file)
            
            # Obtener tamaño del backup
            backup_size = os.path.getsize(backup_file) / (1024 * 1024)  # MB
            
            self.stdout.write(self.style.SUCCESS(f'✓ Backup creado exitosamente'))
            self.stdout.write(f'  Archivo: {backup_file}')
            self.stdout.write(f'  Tamaño: {backup_size:.2f} MB')

            # Mantener solo los ultimos N backups
            keep = options['keep']
            backups = sorted([f for f in os.listdir(backup_dir) if f.startswith('db_backup_')])
            
            if len(backups) > keep:
                old_backups = backups[:-keep]
                self.stdout.write(f'\nLimpiando backups antiguos (manteniendo los ultimos {keep})...')
                
                for old_backup in old_backups:
                    old_path = os.path.join(backup_dir, old_backup)
                    os.remove(old_path)
                    self.stdout.write(f'  Eliminado: {old_backup}')

            # Log the backup in security log
            logger.warning(f'Database backup created: {backup_file} ({backup_size:.2f} MB)')

        except Exception as e:
            self.stdout.write(self.style.ERROR(f'Error durante el backup: {str(e)}'))
            logger.error(f'Backup failed: {str(e)}')
