from django.urls import path
from . import views

urlpatterns = [
    path('products/', views.ProductList.as_view(), name='api-products'),
    path('products/<int:pk>/', views.ProductDetail.as_view(), name='api-product'),
    path('promotions/', views.Promotions.as_view(), name='api-promotions'),
    path('auth/register/', views.RegisterView.as_view(), name='api-register'),
    path('auth/login/', views.LoginView.as_view(), name='api-login'),
    path('orders/create/', views.CreateOrderView.as_view(), name='api-create-order'),
    path('orders/<int:pk>/', views.OrderDetailView.as_view(), name='api-order'),
    path('orders/user/', views.UserOrdersView.as_view(), name='api-user-orders'),
    path('orders/<int:pk>/invoice/', views.OrderInvoiceView.as_view(), name='api-order-invoice'),
    path('orders/<int:pk>/send_invoice/', views.SendInvoiceView.as_view(), name='api-send-invoice'),
    path('coupons/validate/', views.CouponValidateView.as_view(), name='api-coupon-validate'),
]
