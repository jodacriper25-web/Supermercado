# Setup script for Supermercado Django (Windows PowerShell)
Set-StrictMode -Version Latest
Write-Host "Creating virtualenv .venv and installing dependencies..."
python -m venv .venv
& .\.venv\Scripts\python -m pip install --upgrade pip
& .\.venv\Scripts\pip install -r requirements.txt

if (!(Test-Path .env)) {
  Copy-Item .env.example .env
  Write-Host "Copied .env.example to .env — edit .env if needed."
}

Write-Host "Running migrations..."
& .\.venv\Scripts\python manage.py migrate

Write-Host "Create a superuser (interactive). Use Ctrl+C to skip."
& .\.venv\Scripts\python manage.py createsuperuser

Write-Host "Setup complete. Start the dev server with:`"& .\\.venv\\Scripts\\activate; python manage.py runserver"`"
