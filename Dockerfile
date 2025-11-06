# ---------- Stage 1: Build Laravel ----------
FROM composer:2.7 AS build

WORKDIR /app

# انسخ ملفات Composer
COPY composer.json composer.lock ./

# تثبيت الحزم بدون تشغيل أي سكربتات Laravel
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress --no-scripts

# انسخ باقي المشروع بعد تثبيت المكتبات
COPY . .

# ---------- Stage 2: إعداد PHP & Apache ----------
FROM php:8.2-apache

# تثبيت الإضافات اللازمة لـ Laravel
RUN docker-php-ext-install pdo pdo_mysql

# انسخ التطبيق من مرحلة البناء
COPY --from=build /app /var/www/html

# تعيين الصلاحيات للمجلدات المطلوبة
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# تفعيل mod_rewrite حتى تعمل روابط Laravel
RUN a2enmod rewrite
RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

# نسخ ملف .env إذا كان موجودًا (اختياري)
# COPY .env /var/www/html/.env

WORKDIR /var/www/html

# إنشاء APP_KEY إذا لم يكن موجودًا
RUN php artisan key:generate --ansi || true

# Laravel يعمل على المنفذ 8080
ENV PORT=8080
EXPOSE 8080

CMD ["apache2-foreground"]
