# ---------- Stage 1: Build Laravel ----------
FROM composer:2.7 AS build

WORKDIR /app

# انسخ ملفات composer أولاً لتفعيل التخزين المؤقت للبناء
COPY composer.json composer.lock ./

# تثبيت الحزم بدون سكربتات تلقائية لتجنب أخطاء package discovery
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress --no-scripts

# نسخ باقي ملفات المشروع
COPY . .

# ---------- Stage 2: PHP + Apache ----------
FROM php:8.2-apache

# تثبيت الامتدادات المطلوبة للـ Laravel
RUN docker-php-ext-install pdo pdo_mysql

# نسخ التطبيق من مرحلة البناء
COPY --from=build /app /var/www/html

# إنشاء ملف قاعدة البيانات (SQLite) إن لم يكن موجودًا
RUN mkdir -p /var/www/html/database && touch /var/www/html/database/database.sqlite

# تعيين صلاحيات المجلدات
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# ✅ تفعيل mod_rewrite وتوجيه الجذر إلى مجلد public
RUN a2enmod rewrite
RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

# ✅ ضمان أن Apache يسمح بقراءة .htaccess داخل مجلد public
RUN echo "<Directory /var/www/html/public>\n\
    AllowOverride All\n\
    Options Indexes FollowSymLinks\n\
    Require all granted\n\
</Directory>\n" >> /etc/apache2/apache2.conf

# ✅ جعل Apache يستخدم index.php كملف افتراضي ويمرر أي طلب غير موجود إلى Laravel
RUN echo 'DirectoryIndex index.php' >> /etc/apache2/apache2.conf
RUN echo 'FallbackResource /index.php' >> /etc/apache2/apache2.conf

WORKDIR /var/www/html

# ✅ تنظيف الكاش وضمان مفتاح التطبيق
RUN php artisan config:clear || true
RUN php artisan route:clear || true
RUN php artisan cache:clear || true
RUN php artisan key:generate --ansi || true

# ✅ عند التشغيل: تنفيذ الترحيلات ثم بدء Apache
CMD php artisan migrate --force && apache2-foreground

ENV PORT=8080
EXPOSE 8080
