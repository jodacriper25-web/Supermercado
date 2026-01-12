from rest_framework import generics, status
from rest_framework.response import Response
from rest_framework.views import APIView
from django.db import transaction
from django.utils import timezone
from django.contrib.auth import get_user_model
from django.contrib.auth import login
from django.contrib.auth.hashers import make_password, check_password
from django.shortcuts import get_object_or_404
from django.db.models import Q

from .models import Product, Coupon, Order, OrderItem
from .serializers import ProductSerializer, OrderSerializer, CouponSerializer, UserSerializer
from django.shortcuts import render


class ProductList(generics.ListAPIView):
    serializer_class = ProductSerializer

    def get_queryset(self):
        qs = Product.objects.select_related('category').all().order_by('-id')
        q = self.request.query_params.get('q')
        category_id = self.request.query_params.get('category_id')
        if q:
            qs = qs.filter(Q(name__icontains=q) | Q(description__icontains=q))
        if category_id:
            qs = qs.filter(category_id=int(category_id))
        return qs


class ProductDetail(generics.RetrieveAPIView):
    queryset = Product.objects.select_related('category').all()
    serializer_class = ProductSerializer


class Promotions(generics.ListAPIView):
    serializer_class = ProductSerializer

    def get_queryset(self):
        limit = int(self.request.query_params.get('limit', 5))
        return Product.objects.filter(featured=True).select_related('category').order_by('-id')[:limit]


class RegisterView(APIView):
    def post(self, request):
        data = request.data
        email = data.get('email')
        password = data.get('password')
        name = data.get('name','')
        if not email or not password:
            return Response({'error':'Missing fields'}, status=status.HTTP_400_BAD_REQUEST)
        User = get_user_model()
        if User.objects.filter(email=email).exists():
            return Response({'error':'User exists'}, status=status.HTTP_400_BAD_REQUEST)
        user = User.objects.create(email=email, username=email.split('@')[0], password=make_password(password), first_name=name)
        return Response({'success':True,'id':user.id})


class LoginView(APIView):
    def post(self, request):
        data = request.data
        email = data.get('email')
        password = data.get('password')
        if not email or not password:
            return Response({'success':False}, status=status.HTTP_400_BAD_REQUEST)
        User = get_user_model()
        try:
            user = User.objects.get(email=email)
        except User.DoesNotExist:
            return Response({'success':False})
        if check_password(password, user.password):
            login(request, user)
            serializer = UserSerializer(user)
            return Response({'success':True,'user':serializer.data})
        return Response({'success':False})


class CreateOrderView(APIView):
    def post(self, request):
        if not request.user.is_authenticated:
            return Response({'error':'Not authenticated'}, status=status.HTTP_403_FORBIDDEN)
        data = request.data
        items = data.get('items', [])
        coupon_code = data.get('coupon_code')
        if not items:
            return Response({'error':'Invalid payload'}, status=status.HTTP_400_BAD_REQUEST)

        try:
            with transaction.atomic():
                total = 0
                # Lock products
                product_ids = [int(i['product_id']) for i in items]
                products = {p.id: p for p in Product.objects.select_for_update().filter(id__in=product_ids)}
                for it in items:
                    pid = int(it['product_id'])
                    qty = int(it.get('quantity',1))
                    p = products.get(pid)
                    if not p:
                        raise ValueError('Producto no encontrado: %s' % pid)
                    if p.stock < qty:
                        raise ValueError('Stock insuficiente para el producto %s' % pid)
                    total += float(p.price) * qty

                applied_coupon = None
                discount = 0.0
                if coupon_code:
                    try:
                        c = Coupon.objects.get(code=coupon_code, active=True)
                        if c.expires_at and c.expires_at < timezone.now():
                            raise ValueError('Cupón expirado')
                        if c.type == 'percent':
                            discount = (total * float(c.value)) / 100.0
                        else:
                            discount = float(c.value)
                        discount = min(discount, total)
                        total = round(total - discount, 2)
                        applied_coupon = c.code
                    except Coupon.DoesNotExist:
                        raise ValueError('Cupón inválido')

                order = Order.objects.create(user=request.user, total=total, coupon_code=applied_coupon, status='pending')
                for it in items:
                    pid = int(it['product_id'])
                    qty = int(it.get('quantity',1))
                    p = products.get(pid)
                    OrderItem.objects.create(order=order, product=p, quantity=qty, price=p.price)
                    p.stock = p.stock - qty
                    p.save()

                # Generate invoice number and Invoice record
                from .utils import get_next_invoice_number
                inv_num = get_next_invoice_number()
                from .models import Invoice
                Invoice.objects.create(order=order, invoice_number=inv_num)
                # generate PDF invoice
                from .invoice_utils import generate_invoice_pdf, send_invoice_email
                try:
                    pdf_path = generate_invoice_pdf(order, inv_num)
                    # try to send email if user has email
                    if order.user.email:
                        body = f"<p>Gracias por su pedido #{order.id}. Adjuntamos la factura {inv_num}.</p>"
                        try:
                            send_invoice_email(order.user.email, f'Factura {inv_num}', body, pdf_path)
                        except Exception:
                            pass
                except Exception:
                    pdf_path = None

                serializer = OrderSerializer(order)
                return Response({'success':True,'order_id':order.id,'invoice':inv_num,'order':serializer.data})
        except ValueError as e:
            return Response({'success':False,'error':str(e)})
        except Exception as e:
            return Response({'success':False,'error':str(e)})


class OrderDetailView(generics.RetrieveAPIView):
    queryset = Order.objects.prefetch_related('items__product').all()
    serializer_class = OrderSerializer


class UserOrdersView(generics.ListAPIView):
    serializer_class = OrderSerializer

    def get_queryset(self):
        if not self.request.user.is_authenticated:
            return Order.objects.none()
        return Order.objects.filter(user=self.request.user).prefetch_related('items__product').order_by('-created_at')


class CouponValidateView(APIView):
    def get(self, request):
        code = request.query_params.get('code')
        total = float(request.query_params.get('total', '0') or 0)
        if not code:
            return Response({'valid':False,'error':'Missing code'})
        try:
            c = Coupon.objects.get(code=code, active=True)
            if c.expires_at and c.expires_at < timezone.now():
                return Response({'valid':False,'error':'Coupon expired'})
            if c.type == 'percent':
                discount = (total * float(c.value)) / 100.0
            else:
                discount = float(c.value)
            discount = min(discount, total)
            return Response({'valid':True,'discount':round(discount,2),'new_total':round(total-discount,2),'coupon':CouponSerializer(c).data})
        except Coupon.DoesNotExist:
            return Response({'valid':False,'error':'Invalid coupon'})


def HomeView(request):
    return render(request, 'index.html')


class OrderInvoiceView(APIView):
    def get(self, request, pk):
        order = get_object_or_404(Order, pk=pk)
        # only owner or staff
        if not (request.user.is_staff or (request.user.is_authenticated and request.user == order.user)):
            return Response({'error':'Forbidden'}, status=403)
        inv = getattr(order, 'invoice', None)
        if not inv or not inv.file_path:
            return Response({'error':'No invoice'}, status=404)
        from django.http import FileResponse
        try:
            return FileResponse(open(inv.file_path, 'rb'), as_attachment=True, filename=f'{inv.invoice_number}.pdf')
        except Exception:
            return Response({'error':'File not found'}, status=404)


class SendInvoiceView(APIView):
    def post(self, request, pk):
        order = get_object_or_404(Order, pk=pk)
        if not request.user.is_staff:
            return Response({'error':'Forbidden'}, status=403)
        inv = getattr(order, 'invoice', None)
        if not inv:
            return Response({'error':'No invoice record'}, status=404)
        from .invoice_utils import send_invoice_email
        if not order.user.email:
            return Response({'error':'No recipient email'}, status=400)
        try:
            body = f"<p>Adjuntamos la factura {inv.invoice_number} para el pedido #{order.id}.</p>"
            send_invoice_email(order.user.email, f'Factura {inv.invoice_number}', body, inv.file_path)
            return Response({'success':True})
        except Exception as e:
            return Response({'success':False,'error':str(e)})
