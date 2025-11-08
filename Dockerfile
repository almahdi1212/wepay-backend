# ---------- Stage 1: Build Laravel ----------
FROM composer:2.7 AS build

WORKDIR /app
COPY composer.json composer.lock ./

# تثبيت الاعتمادات بدون سكربتات Laravel
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress --no-scripts

# نسخ باقي ملفات المشروع
COPY . .

# ---------- Stage 2: PHP + Apache ----------
FROM php:8.2-apache

# ✅ تثبيت المكتبات والامتدادات المطلوبة (بما فيها PostgreSQL)
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql

# تفعيل mod_rewrite
RUN a2enmod rewrite

# ✅ إعداد VirtualHost الصحيح
RUN echo '<VirtualHost *:80>\n\
    DocumentRoot /var/www/html/public\n\
    <Directory /var/www/html/public>\n\
        AllowOverride All\n\
        Options Indexes FollowSymLinks\n\
        Require all granted\n\
    </Directory>\n\
    ErrorLog /var/log/apache2/error.log\n\
    CustomLog /var/log/apache2/access.log combined\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf && \
    echo "<Directory /var/www/html/public>\nAllowOverride All\nRequire all granted\n</Directory>" >> /etc/apache2/apache2.conf

# نسخ التطبيق من مرحلة البناء
COPY --from=build /app /var/www/html

# إعداد الصلاحيات
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

WORKDIR /var/www/html

# تنظيف الكاش وإنشاء المفتاح
RUN php artisan config:clear || true
RUN php artisan route:clear || true
RUN php artisan cache:clear || true
RUN php artisan key:generate --ansi || true

# ✅ نقطة فحص التشغيل
RUN printf "\nRoute::get('/health', function () { return response()->json(['status' => 'ok']); });\n" >> routes/web.php

# ✅ عرض المسارات أثناء النشر
RUN echo "=== ROUTE LIST START ===" && php artisan route:list && echo "=== ROUTE LIST END ==="

# إعادة إنشاء قاعدة البيانات بالكامل قبل تشغيل السيرفر
CMD php artisan migrate:fresh --force && php artisan db:seed --force && apache2-foreground


ENV PORT=8080
EXPOSE 8080
