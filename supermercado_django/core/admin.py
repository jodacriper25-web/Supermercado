from django.contrib import admin
from django.contrib.auth.admin import UserAdmin as BaseUserAdmin
from .models import User, Category, Product, Coupon, Order, OrderItem, Invoice, AuditLog


@admin.register(User)
class UserAdmin(BaseUserAdmin):
    fieldsets = (
        (None, {'fields': ('email', 'password')}),
        ('Personal info', {'fields': ('first_name','last_name')}),
        ('Permissions', {'fields': ('is_active','is_staff','is_superuser','role','groups','user_permissions')}),
        ('Important dates', {'fields': ('last_login','date_joined')}),
    )
    add_fieldsets = (
        (None, {'classes':('wide',),'fields':('email','password1','password2')}),
    )
    list_display = ('email','role','is_staff')
    search_fields = ('email',)
    ordering = ('email',)


@admin.register(Category)
class CategoryAdmin(admin.ModelAdmin):
    list_display = ('name','slug')
    prepopulated_fields = {'slug':('name',)}


@admin.register(Product)
class ProductAdmin(admin.ModelAdmin):
    list_display = ('name','category','price','stock','featured')
    list_filter = ('category','featured')


@admin.register(Coupon)
class CouponAdmin(admin.ModelAdmin):
    list_display = ('code','type','value','active','expires_at')


class OrderItemInline(admin.TabularInline):
    model = OrderItem
    extra = 0


@admin.register(Order)
class OrderAdmin(admin.ModelAdmin):
    list_display = ('id','user','total','status','created_at')
    inlines = (OrderItemInline,)


@admin.register(Invoice)
class InvoiceAdmin(admin.ModelAdmin):
    list_display = ('invoice_number','order','created_at')


@admin.register(AuditLog)
class AuditLogAdmin(admin.ModelAdmin):
    list_display = ('created_at','user','action')
