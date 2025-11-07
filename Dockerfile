# ---------- Stage 1: Build Laravel ----------
FROM composer:2.7 AS build

WORKDIR /app

# انسخ ملفات Composer
COPY composer.json composer.lock ./

# تثبيت الحزم بدون تشغيل سكربتات Laravel
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress --no-scripts

# انسخ باقي المشروع
COPY . .

# ---------- Stage 2: إعداد PHP & Apache ----------
FROM php:8.2-apache

# تثبيت الإضافات المطلوبة لـ Laravel
RUN docker-php-ext-install pdo pdo_mysql

# انسخ المشروع من مرحلة البناء
COPY --from=build /app /var/www/html

# إنشاء قاعدة البيانات إذا لم تكن موجودة
RUN mkdir -p /var/www/html/database && touch /var/www/html/database/database.sqlite

# تعيين الصلاحيات الصحيحة
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# 🔹 إعداد Apache لإعادة التوجيه إلى Laravel
RUN a2enmod rewrite
RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf
RUN echo "<Directory /var/www/html/public>\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>" >> /etc/apache2/apache2.conf

WORKDIR /var/www/html

# توليد مفتاح التطبيق إن لم يكن موجودًا
RUN php artisan key:generate --ansi || true

# تنفيذ الترحيلات ثم تشغيل السيرفر
CMD php artisan migrate --force && apache2-foreground

EXPOSE 8080
ENV PORT=8080
