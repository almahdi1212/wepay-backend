# ---------- Stage 1: Build Laravel ----------
FROM composer:2.7 AS build

WORKDIR /app
COPY composer.json composer.lock ./

# تثبيت الاعتمادات بدون تشغيل سكربتات Laravel
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress --no-scripts

# نسخ باقي ملفات المشروع
COPY . .

# ---------- Stage 2: PHP + Apache ----------
FROM php:8.2-apache

# تثبيت الامتدادات المطلوبة لـ Laravel
RUN docker-php-ext-install pdo pdo_mysql

# نسخ التطبيق من مرحلة البناء
COPY --from=build /app /var/www/html

# إنشاء قاعدة البيانات (في حال لم تكن موجودة)
RUN mkdir -p /var/www/html/database && touch /var/www/html/database/database.sqlite

# إعداد الصلاحيات
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# تفعيل mod_rewrite
RUN a2enmod rewrite

# ✅ إعداد VirtualHost لتمرير كل الطلبات إلى Laravel index.php
RUN echo '<VirtualHost *:80>\n\
    DocumentRoot /var/www/html/public\n\
    <Directory /var/www/html/public>\n\
        AllowOverride All\n\
        Options Indexes FollowSymLinks\n\
        Require all granted\n\
        FallbackResource /index.php\n\
    </Directory>\n\
    ErrorLog /var/log/apache2/error.log\n\
    CustomLog /var/log/apache2/access.log combined\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html

# تنظيف الكاش وإنشاء المفتاح
RUN php artisan config:clear || true
RUN php artisan route:clear || true
RUN php artisan cache:clear || true
RUN php artisan key:generate --ansi || true

# ✅ طباعة قائمة المسارات أثناء النشر (لتتبع نجاح التحميل)
RUN echo "=== ROUTE LIST START ===" && php artisan route:list && echo "=== ROUTE LIST END ==="

# تنفيذ migrate عند التشغيل، ثم تشغيل Apache
CMD php artisan migrate --force && apache2-foreground

# Laravel يعمل على المنفذ 8080
ENV PORT=8080
EXPOSE 8080
