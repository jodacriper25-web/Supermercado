import os
from django.conf import settings
from reportlab.lib.pagesizes import A4
from reportlab.pdfgen import canvas
from .models import Invoice, Order
from django.core.mail import EmailMessage


def generate_invoice_pdf(order: Order, invoice_number: str):
    invoices_dir = os.path.join(settings.BASE_DIR, '..', '..', 'invoices')
    os.makedirs(invoices_dir, exist_ok=True)
    filename = f"{invoice_number}.pdf"
    path = os.path.join(invoices_dir, filename)

    c = canvas.Canvas(path, pagesize=A4)
    width, height = A4
    c.setFont('Helvetica-Bold', 16)
    c.drawString(40, height - 40, f'Factura: {invoice_number}')
    c.setFont('Helvetica', 12)
    c.drawString(40, height - 70, f'Cliente: {order.user.get_full_name() or order.user.email}')
    c.drawString(40, height - 90, f'Fecha: {order.created_at.strftime("%Y-%m-%d %H:%M")}')

    y = height - 130
    c.drawString(40, y, 'Producto')
    c.drawString(300, y, 'Cantidad')
    c.drawString(380, y, 'Precio')
    c.drawString(460, y, 'Total')
    y -= 20
    for item in order.items.all():
        c.drawString(40, y, item.product.name[:40])
        c.drawString(300, y, str(item.quantity))
        c.drawString(380, y, f"{item.price:.2f}")
        c.drawString(460, y, f"{(item.price * item.quantity):.2f}")
        y -= 18
        if y < 80:
            c.showPage()
            y = height - 80

    c.setFont('Helvetica-Bold', 12)
    c.drawString(380, y - 10, 'Total:')
    c.drawString(460, y - 10, f"{order.total:.2f}")
    c.save()

    # Save path in Invoice model
    inv = Invoice.objects.filter(order=order).first()
    if inv:
        inv.file_path = path
        inv.save()
    else:
        Invoice.objects.create(order=order, invoice_number=invoice_number, file_path=path)

    return path


def send_invoice_email(to_email: str, subject: str, html_body: str, attachment_path: str = None):
    email = EmailMessage(subject=subject, body=html_body, from_email=settings.DEFAULT_FROM_EMAIL, to=[to_email])
    email.content_subtype = 'html'
    if attachment_path and os.path.exists(attachment_path):
        email.attach_file(attachment_path)
    return email.send(fail_silently=False)
