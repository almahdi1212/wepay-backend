# ---------- Stage 1: Build Laravel ----------
FROM composer:2.7 AS build

WORKDIR /app

# انسخ ملفات Composer
COPY composer.json composer.lock ./

# تثبيت الحزم بدون تشغيل سكربتات Laravel (منع أخطاء discover)
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress --no-scripts

# انسخ باقي المشروع
COPY . .

# ---------- Stage 2: إعداد PHP & Apache ----------
FROM php:8.2-apache

# تثبيت الإضافات المطلوبة لـ Laravel
RUN docker-php-ext-install pdo pdo_mysql

# نسخ التطبيق من مرحلة البناء
COPY --from=build /app /var/www/html

# تعيين الصلاحيات للمجلدات المطلوبة
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# تفعيل mod_rewrite للروابط
RUN a2enmod rewrite
RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html

# إنشاء APP_KEY في حال لم يكن موجودًا
RUN php artisan key:generate --ansi || true

# 🔹 عند بدء التشغيل، شغّل Apache بعد تنفيذ migrate تلقائيًا
CMD php artisan migrate --force && apache2-foreground

# Laravel يعمل على المنفذ 8080
ENV PORT=8080
EXPOSE 8080
