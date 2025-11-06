# ---------- Stage 1: Build Composer dependencies ----------
FROM composer:2 AS vendor

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress

# ---------- Stage 2: Set up PHP & Laravel ----------
FROM php:8.2-apache

# Install required PHP extensions
RUN docker-php-ext-install pdo pdo_mysql

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Copy Laravel project files
WORKDIR /var/www/html
COPY . .

# Copy vendor dependencies from first stage
COPY --from=vendor /app/vendor ./vendor

# Set Laravel permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage /var/www/html/bootstrap/cache

# Apache configuration for Laravel
RUN echo '<Directory /var/www/html/public>\n\
    AllowOverride All\n\
</Directory>' > /etc/apache2/conf-available/laravel.conf \
    && a2enconf laravel

EXPOSE 8080
CMD ["apache2-foreground"]
# 🧱 المرحلة الأولى: بناء تطبيق Laravel
FROM composer:2.7 AS build

WORKDIR /app

# انسخ ملفات composer فقط أولاً لتسريع عملية build
COPY composer.json composer.lock ./

# تثبيت مكتبات Laravel بدون dev
RUN composer install --no-dev --optimize-autoloader

# انسخ باقي ملفات المشروع
COPY . .

# 🧱 المرحلة الثانية: إعداد Apache + PHP
FROM php:8.2-apache

# تثبيت الإضافات المطلوبة للـ Laravel
RUN docker-php-ext-install pdo pdo_mysql

# نسخ المشروع من مرحلة البناء
COPY --from=build /app /var/www/html

# إعطاء التصاريح للمجلدات الضرورية
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# إعداد الـ Document Root
WORKDIR /var/www/html

# ضبط Apache على المسار الصحيح للـ public
RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

# تفعيل mod_rewrite (مطلوب للـ Laravel routes)
RUN a2enmod rewrite

# متغير البيئة الخاص بـ Laravel
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
ENV PORT 8080

EXPOSE 8080

CMD ["apache2-foreground"]
