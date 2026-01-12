from django.utils import timezone
from .models import Invoice

def get_next_invoice_number():
    year = timezone.now().year
    like = f"INV-{year}-%"
    last = Invoice.objects.filter(invoice_number__startswith=f'INV-{year}-').order_by('-id').first()
    if not last:
        return f'INV-{year}-0001'
    parts = last.invoice_number.split('-')
    try:
        seq = int(parts[-1]) + 1
    except Exception:
        seq = 1
    return f'INV-{year}-{seq:04d}'
